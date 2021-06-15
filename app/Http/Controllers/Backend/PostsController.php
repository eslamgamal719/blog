<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use App\Models\PostMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Validator;
use Stevebauman\Purify\Facades\Purify;

class PostsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $posts = Post::with(['category', 'user', 'comments'])->wherePostType('post')->orderBy('id', 'desc')->paginate(10);
        return view('backend.posts.index', compact('posts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = Category::orderBy('id', 'desc')->pluck('name', 'id');
        return view('backend.posts.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title'          => 'required',
            'description'    => 'required|min:10',
            'status'         => 'required',
            'category_id'    => 'required',
            'comment_able'   => 'required',
            'images.*'       => 'nullable|mimes:jpg,jpeg,png,gif|max:20000' //20000 = 2MB size
        ]);

        if($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data['title']          = $request->title;
        $data['description']    = Purify::clean($request->description);
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

        if($request->status == 1) {
            Cache::forget('recent_posts');
        }

        return redirect()->route('admin.posts.index')->with([
            'message'    => 'Post Created Successfully',
            'alert-type' => 'success'
        ]);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $categories = Category::orderBy('id', 'desc')->pluck('name', 'id');
        $post = Post::with('media')->whereId($id)->wherePostType('post')->first();
        return view('backend.posts.edit', compact('categories', 'post'));
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
        $validator = Validator::make($request->all(), [
            'title'          => 'required',
            'description'    => 'required|min:10',
            'status'         => 'required',
            'category_id'    => 'required',
            'comment_able'   => 'required',
            'images.*'       => 'nullable|mimes:jpg,jpeg,png,gif|max:20000' //20000 = 2MB size
        ]);

        if($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $post = Post::whereId($id)->wherePostType('post')->first();

        if($post) {
            $data['title']          = $request->title;
            $data['slug']           = null;
            $data['description']    = Purify::clean($request->description);
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

            if($request->status == 1) {
                Cache::forget('recent_posts');
            }

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
        //
    }


    public function removeImage($media_id) {

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
