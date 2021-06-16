<?php

namespace App\Http\Controllers\Frontend;

use App\Models\Post;
use App\Models\Comment;
use App\Models\Category;
use App\Models\PostMedia;
use GuzzleHttp\Middleware;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Cache;
use Stevebauman\Purify\Facades\Purify;
use Illuminate\Support\Facades\Validator;


class UsersController extends Controller
{

    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
    }


    public function index() {

        $posts = auth()->user()->posts()->with(['media', 'category', 'user'])
            ->withCount('comments')
            ->orderBy('id', 'desc')->paginate(10);

        
        return view('frontend.users.dashboard', compact('posts'));
    }


    public function edit_info() {
        return view('frontend.users.edit_info');
    }


    public function update_info(Request $request) {

        $validator = Validator::make($request->all(), [
            'name'           => 'required',
            'email'          => 'required|email',
            'mobile'         => 'required|numeric',
            'receive_email'  => 'required',
            'bio'            => 'nullable|min:10',
            'user_image'     => 'nullable|image|max:20000,mimes:jpg,jpeg,png',
        ]);

        if($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data['name']           = $request->name;
        $data['email']          = $request->email;
        $data['mobile']         = $request->mobile;
        $data['receive_email']  = $request->receive_email;
        $data['bio']            = $request->bio;

        $image = $request->file('user_image');

        if($image) {
            if(auth()->user()->user_image != '') {
                if(File::exists("assets/users/" . auth()->user()->user_image)) {
                    unlink("assets/users/" . auth()->user()->user_image);
                }
                $fileName = Str::slug(auth()->user()->username) . '.' . $image->getClientOriginalExtension();
                $path = public_path('assets/users/' . $fileName);
    
                Image::make($image->getRealPath())->resize(300, 300, function($constraint) {
                    $constraint->aspectRatio();
                })->save($path, 100);
                
                $data['user_image'] = $fileName;

            } else {

                $fileName = Str::slug(auth()->user()->username) . '.' . $image->getClientOriginalExtension();
                $path = public_path('assets/users/' . $fileName);
    
                Image::make($image->getRealPath())->resize(300, 300, function($constraint) {
                    $constraint->aspectRatio();
                })->save($path, 100);
            }

              $data['user_image'] = $fileName;

        }

        $update = auth()->user()->update($data);

        if($update) {
            return redirect()->back()->with([
                'message'    => 'Information Updated Successfully',
                'alert-type' => 'success'
            ]);
        }else {
            return redirect()->back()->with([
                'message'     => 'Something Was Wrong',
                'alert-type'  => 'danger'
            ]);
        }


    }

    public function update_password(Request $request) {

        $validator = Validator::make($request->all(), [
            'current_password'  => 'required',
            'password'          => 'required|confirmed',
        ]);

        if($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user = auth()->user();

        if(Hash::check($request->current_password, $user->password)) {
            $update = $user->update([
                'password' => bcrypt($request->password)
            ]);

            if($update) {
                return redirect()->back()->with([
                    'message'    => 'Password Updated Successfully',
                    'alert-type' => 'success'
                ]);
            }else { 
                return redirect()->back()->with([
                    'message'     => 'Something Was Wrong',
                    'alert-type'  => 'danger'
                ]);
            }
        }else {
            return redirect()->back()->with([
                'message'     => 'Something Was Wrong',
                'alert-type'  => 'danger'
            ]);
        }

    }



    public function create_post() {

        $categories = Category::whereStatus(1)->pluck('name', 'id');
        return view('frontend.users.create_post', compact('categories'));
    }
    

    public function store_post(Request $request) {
    
        $validator = Validator::make($request->all(), [
            'title'          => 'required',
            'description'    => 'required|min:10',
            'status'         => 'required',
            'category_id'    => 'required',
            'comment_able'   => 'required',
        ]);

        if($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data['title']          = $request->title;
        $data['description']    = Purify::clean($request->description);
        $data['status']         = $request->status;
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

        return redirect()->back()->with([
            'message'    => 'Post Created Successfully',
            'alert-type' => 'success'
        ]);
    } 
       
    
    public function edit_post($post_id) {
        
        $post = Post::whereSlug($post_id)->orWhere('id', $post_id)->whereUserId(auth()->id())->first();

        if($post) {
            $categories = Category::whereStatus(1)->pluck('name', 'id');
            return view('frontend.users.edit_post', compact('post', 'categories'));
        }

        return redirect()->route('frontend.index');
    }


    public function update_post(Request $request, $post_id) {

        $validator = Validator::make($request->all(), [
            'title'          => 'required',
            'description'    => 'required|min:10',
            'status'         => 'required',
            'category_id'    => 'required',
            'comment_able'   => 'required',
        ]);

        if($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $post = Post::whereSlug($post_id)->orWhere('id', $post_id)->whereUserId(auth()->id())->first();

        if($post) {
            $data['title']          = $request->title;
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

            return redirect()->back()->with([
                'message'     => 'Post Updated Successfully',
                'alert-type'  => 'success'
            ]);
        }

        return redirect()->back()->with([
            'message'     => 'Something Was Wrong',
            'alert-type'  => 'danger'
        ]);
    }


    public function destroy_post_media($media_id) {

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


    public function destroy_post($post_id) {

        $post = Post::whereSlug($post_id)->orWhere('id', $post_id)->whereUserId(auth()->id())->first();

        if($post) {
            if($post->media->count() > 0) {
                foreach($post->media as $media) {
                    if(File::exists("assets/posts/" . $media->file_name)) {
                        unlink("assets/posts/" . $media->file_name);
                    }
                }
            }
            $post->delete();

            return redirect()->back()->with([
                'message'     => 'Post Deleted Successfully',
                'alert-type'  => 'success'
            ]);
        }
        return redirect()->back()->with([
            'message'     => 'Something Was Wrong',
            'alert-type'  => 'danger'
        ]);
    }
    

    public function show_comments(Request $request) {

        $comments = Comment::query();

        if(isset($request->post_id) && $request->post_id != '') {
            $comments = $comments->where('post_id', $request->post_id);
        }else {
            $posts_id = auth()->user()->posts()->pluck('id')->toArray();
            $comments = $comments->whereIn('post_id', $posts_id);
        }

        $comments = $comments->orderBy('id', 'desc');
        $comments = $comments->paginate(10);

        return view('frontend.users.comments', compact('comments'));
    }


    public function edit_comment($comment_id) {

        $comment = Comment::whereId($comment_id)->whereHas('post', function($q) {
            $q->where('posts.user_id', auth()->id());
        })->first();

        if($comment) {
            return view('frontend.users.edit_comment', compact('comment'));
        }

        return redirect()->back()->with([
            'message'     => 'Something Was Wrong',
            'alert-type'  => 'danger'
        ]);
    }



    public function update_comment(Request $request, $comment_id) {
        
        $validator = Validator::make($request->all(), [
            'name'      => 'required',
            'email'     => 'required|email',
            'url'       => 'nullable|url',
            'status'    => 'required',
            'comment'   => 'required',
        ]);

        if($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $comment = Comment::whereId($comment_id)->whereHas('post', function($q) {
            $q->where('posts.user_id', auth()->id());
        })->first();

        if($comment) {
            $data['name']     = $request->name;
            $data['email']    = $request->email;
            $data['url']      = $request->url != '' ? $request->url : null;
            $data['status']   = $request->status;
            $data['comment']  = Purify::clean($request->comment);

            $comment->update($data);

            if($request->status == 1) {
                Cache::forget('recent_comments');
            }

            return redirect()->back()->with([
                'message'     => 'Comment Updated Successfully',
                'alert-type'  => 'success'
            ]);
        }
        return redirect()->back()->with([
            'message'     => 'Something Was Wrong',
            'alert-type'  => 'danger'
        ]);
    }

    
    public function destroy_comment($comment_id) {

        $comment = Comment::whereId($comment_id)->whereHas('post', function($q) {
            $q->where('posts.user_id', auth()->id());
        })->first();

        if($comment) {
            
            $comment->delete();
      
            Cache::forget('recent_comments');
            
            return redirect()->back()->with([
                'message'     => 'Comment Deleted Successfully',
                'alert-type'  => 'success'
            ]);
        }
        return redirect()->back()->with([
            'message'     => 'Something Was Wrong',
            'alert-type'  => 'danger'
        ]);
    }


    
}
