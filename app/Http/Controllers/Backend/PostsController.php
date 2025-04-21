<?php

namespace App\Http\Controllers\Backend;

use App\Models\Post;
use App\Models\Category;
use App\Models\PostMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Support\Facades\Cache;
use Intervention\Image\Facades\Image;
use Stevebauman\Purify\Facades\Purify;
use Illuminate\Support\Facades\Validator;


class PostsController extends Controller
{
    public function __construct()
    {
        if(auth()->check()) {
            $this->middleware('auth');
        }else {
            return view('backend.auth.login');
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(!auth()->user()->ability('admin', 'manage_posts,show_posts')) {
            return redirect('admin/index');
        }

        $keyword = (isset(request()->keyword) && request()->keyword != '') ? request()->keyword : null;
        $categoryId = (isset(request()->category_id) && request()->category_id != '') ? request()->category_id : null;
        $tagId = (isset(request()->tag_id) && request()->tag_id != '') ? request()->tag_id : null;
        $status = (isset(request()->status) && request()->status != '') ? request()->status : null;
        $sort_by = (isset(request()->sort_by) && request()->sort_by != '') ? request()->sort_by : 'id';
        $order_by = (isset(request()->order_by) && request()->order_by != '') ? request()->order_by : 'desc';
        $limit_by = (isset(request()->limit_by) && request()->limit_by != '') ? request()->limit_by : '10';

        $categories = Category::orderBy('id', 'desc')->pluck('name', 'id');

        $posts = Post::with(['category', 'user', 'comments'])->wherePostType('post');

        if($keyword != null) {
            $posts = $posts->search($keyword);
        }

        if($categoryId != null) {
            $posts = $posts->whereCategoryId($categoryId);
        }

        if($tagId != null) {
            $posts = $posts->whereHas('tags', function($query) use ($tagId) {
                $query->where('id', $tagId);
            });
        }

        if($status != null) {
            $posts = $posts->whereStatus($status);
        }

        $posts = $posts->orderBy($sort_by, $order_by);

        $posts = $posts->paginate($limit_by);

        return view('backend.posts.index', compact('posts', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if(!auth()->user()->ability('admin', 'create_posts')) {
            return redirect();
        }

        $tags = Tag::pluck('name', 'id');
        $categories = Category::orderBy('id', 'desc')->pluck('name', 'id');
        return view('backend.posts.create', compact('categories', 'tags'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(!auth()->user()->ability('admin', 'create_posts')) {
            return redirect();
        }

        $validator = Validator::make($request->all(), [
            'title'          => 'required',
            'description'    => 'required|min:10',
            'status'         => 'required',
            'category_id'    => 'required',
            'comment_able'   => 'required',
            'tags.*'         => 'array|min:1',
            'tags.0'         => 'required',
            'images.*'       => 'nullable|mimes:jpg,jpeg,png,gif|max:20000' //20000 = 2MB size
        ]);

        if($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        $data['title']          = $request->title;
        $data['description']    = $request->description;
        $data['status']         = $request->status;
        $data['post_type']      = 'post';
        $data['comment_able']   = $request->comment_able;
        $data['category_id']    = $request->category_id;

        $post = auth()->user()->posts()->create($data);

        if($request->images && $request->images > 0) {

            $i = 1;
            foreach($request->images as $file) {

                $fileName = $post->slug . '-' . time() . '-' . $i . '.' . $file->getClientOriginalExtension();
                $fileSize = $file->getSize();
                $fileType = $file->getMimeType();
                $path = public_path('assets/posts/' . $fileName);

                Image::make($file->getRealPath())->resize(800, null, function($constraint) {
                    $constraint->aspectRatio();
                })->save($path, 100);

                $post->media()->create([
                    'file_name' => $fileName,
                    'file_size' => $fileSize,
                    'file_type' => $fileType,
                ]);
                $i++;
            }
        }

        if(count($request->tags) > 0) {
            $new_tags = [];
             foreach($request->tags as $tag) {
                 $tag = Tag::firstOrCreate([
                     'id' => $tag
                 ], [
                     'name' => $tag
                 ]);
                 $new_tags[] = $tag->id;
             }
             $post->tags()->sync($new_tags);
         }

        if($request->status == 1) {
            Cache::forget('recent_posts');
            Cache::forget('global_tags');
        }

        return redirect()->route('admin.posts.index')->with([
            'message'    => 'Post Created Successfully',
            'alert-type' => 'success'
        ]);
    }

    public function show($id)
    {
        if(!auth()->user()->ability('admin', 'display_posts')) {
            return redirect();
        }

        $post = Post::with(['media', 'category', 'user', 'comments'])->whereId($id)->wherePostType('post')->first();
        return view('backend.posts.show', compact('post'));
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if(!auth()->user()->ability('admin', 'update_posts')) {
            return redirect();
        }

        $tags = Tag::pluck('name', 'id');
        $categories = Category::orderBy('id', 'desc')->pluck('name', 'id');
        $post = Post::with('media')->whereId($id)->wherePostType('post')->first();
        return view('backend.posts.edit', compact('categories', 'post', 'tags'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if(!auth()->user()->ability('admin', 'update_posts')) {
            return redirect();
        }

        $validator = Validator::make($request->all(), [
            'title'          => 'required',
            'description'    => 'required|min:10',
            'status'         => 'required',
            'category_id'    => 'required',
            'comment_able'   => 'required',
            'tags.*'         => 'required',
            'images.*'       => 'nullable|mimes:jpg,jpeg,png,gif|max:20000' //20000 = 2MB size
        ]);

        if($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $post = Post::whereId($id)->wherePostType('post')->first();

        if($post) {
            $data['title']          = $request->title;
            $data['slug']           = null;
            $data['description']    = $request->description;
            $data['status']         = $request->status;
            $data['comment_able']   = $request->comment_able;
            $data['category_id']    = $request->category_id;

            $post->update($data);

            if($request->images && $request->images > 0) {

                $i = 1;
                foreach($request->images as $file) {
                    $fileName = $post->slug . '-' . time() . '-' . $i . '.' . $file->getClientOriginalExtension();
                    $fileSize = $file->getSize();
                    $fileType = $file->getMimeType();
                    $path     = public_path('assets/posts/' . $fileName);

                    Image::make($file->getRealPath())->resize(800, null, function($constraint) {
                        $constraint->aspectRatio();
                    })->save($path, 100);

                    $post->media()->create([
                        'file_name' => $fileName,
                        'file_size' => $fileSize,
                        'file_type' => $fileType,
                    ]);

                    $i++;
                }
            }


        if(count($request->tags) > 0) {
            $new_tags = [];
             foreach($request->tags as $tag) {
                 $tag = Tag::firstOrCreate([
                     'id' => $tag
                 ], [
                     'name' => $tag
                 ]);
                 $new_tags[] = $tag->id;
             }
             $post->tags()->sync($new_tags);
         }

                Cache::forget('recent_posts');
                Cache::forget('global_tags');

            return redirect()->route('admin.posts.index')->with([
                'message'     => 'Post Updated Successfully',
                'alert-type'  => 'success'
            ]);
        }

        return redirect()->route('admin.posts.index')->with([
            'message'     => 'Something Was Wrong',
            'alert-type'  => 'danger'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if(!auth()->user()->ability('admin', 'delete_posts')) {
            return redirect();
        }

        $post = Post::whereId($id)->wherePostType('post')->first();

        if($post) {
            if($post->media->count() > 0) {
                foreach($post->media as $media) {
                    if(File::exists('assets/posts/' . $media->file_name)) {
                        unlink('assets/posts/' . $media->file_name);
                    }
                }
            }
            $post->delete();

            return redirect()->route('admin.posts.index')->with([
                'message'     => 'Post Deleted Successfully',
                'alert-type'  => 'success'
            ]);
        }

        return redirect()->route('admin.posts.index')->with([
            'message'     => 'Something Was Wrong',
            'alert-type'  => 'danger'
        ]);
    }


    public function removeImage($media_id)
    {
        if(!auth()->user()->ability('admin', 'delete_posts')) {
            return redirect();
        }

        $media = PostMedia::whereId($media_id)->first();

        if($media) {
            if(File::exists("assets/posts/" . $media->file_name)) {
                unlink("assets/posts/" . $media->file_name);
            }

            $media->delete();
            return true;
        }
        return false;
    }
}
