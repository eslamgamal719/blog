<?php

namespace App\Http\Controllers\Frontend;

use App\Models\Post;
use App\Models\User;
use App\Models\Comment;
use App\Models\Contact;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\Tag;
use App\Notifications\NewCommentForAdminNotify;
use Stevebauman\Purify\Facades\Purify;
use Illuminate\Support\Facades\Validator;
use App\Notifications\NewCommentForPostOwnerNotify;


class IndexController extends Controller
{
    public function index() {
        
        $posts = Post::with(['user', 'media', 'tags'])
            ->whereHas('category', function($q) {
                $q->whereStatus(1);
            })->whereHas('user', function($query) {
                $query->whereStatus(1);
            })
            ->post()
            ->active()
            ->orderBy('id', 'desc')
            ->paginate(5);

        return view('frontend.index', compact('posts'));
    }


    public function search(Request $request) {
        $keyword = isset($request->keyword) && $request->keyword != '' ? $request->keyword : null;

        $posts = Post::with(['user', 'media', 'tags'])
            ->whereHas('category', function($q) {
                $q->whereStatus(1);
            })->whereHas('user', function($query) {
                $query->whereStatus(1); 
            });

            if($keyword != null) {
                $posts = $posts->search($keyword, null, true);
            }

           $posts = $posts->post()
            ->active()
            ->orderBy('id', 'desc')
            ->paginate(5);

        return view('frontend.index', compact('posts'));
    }

    public function autocompleteSearch(Request $request)
    {
          $search = $request->get('search');
          $filterResult = Post::where('title', 'LIKE', '%'. $search. '%')->get();
          return response()->json($filterResult);
    }


    public function post_show($slug) {
        $post = Post::with(['category', 'media', 'user', 'tags',
            'approved_comments' => function($qu) {
              $qu->orderBy('id', 'desc');
            }
        ]);

        $post = $post->whereHas('category', function($query) {
            $query->whereStatus(1);
        })->whereHas('user', function ($q) {
                $q->whereStatus(1);
         });

        $post = $post->whereSlug($slug);
        $post = $post->active()->first();

        if($post) {
           $blade = $post->post_type == 'post' ? 'post' : 'page';

            return view('frontend.' . $blade, compact('post'));
        }else {
            return redirect()->route('frontend.index');
        }
    }



    public function store_comment(Request $request, $slug) { 
        $validation = Validator::make($request->all(), [
            'name' =>  'required',
            'email'=>  'required|email',
            'url' => 'nullable|url',
            'comment' => 'required|min:10',
        ]);
        if($validation->fails()) {
            return redirect()->back()->withErrors($validation)->withInput();
        }

        $post = Post::whereSlug($slug)->wherePostType('post')->whereStatus(1)->first();

        if($post) {
            $userId = Auth::check() ? Auth::id() : null;
            $data['name']  =$request->name;
            $data['email']  =$request->email;
            $data['url']  =$request->url;
            $data['ip_address']  = $request->ip();
            $data['comment']  = Purify::clean($request->comment);
            $data['post_id']  = $post->id;
            $data['user_id']  = $userId;

            //Comment::create($data);
             $comment = $post->comments()->create($data);     //instead of using comment model too

            if(auth()->guest() || auth()->id() != $post->user_id) {
                 $post->user->notify(new NewCommentForPostOwnerNotify($comment));
            }

            User::whereHas('roles', function($query) {
                $query->whereIn('name', ['admin', 'editor']);
            })->each(function($admin, $key) use ($comment) {
                $admin->notify(new NewCommentForAdminNotify($comment));
            });

            return redirect()->back()->with([
                'message' => 'Comment Added Successfully',
                'alert-type' => 'success'
            ]);
        }
            return redirect()->back()->with([
                'message' => 'Something Was Wrong',
                'alert-type' => 'danger'
            ]);

    }


    public function  contact() {

        return view('frontend.contact');
    }

    public function do_contact(Request $request) {
        $validation = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'mobile' => 'nullable|numeric',
            'title' => 'required|min:5',
            'message' => 'required|min:10'
        ]);

        if($validation->fails()) {
            return redirect()->back()->withErrors($validation)->withInput();
        }

            $data['name' ]    = $request->name;
            $data['email' ]   = $request->email;
            $data['mobile']   = $request->mobile;
            $data['title' ]   = $request->title;
            $data['message']  = $request->message;

            Contact::create($data);

        return redirect()->back()->with([
            'message' => 'Message Sent Successfully',
            'alert-type' => 'success'
        ]);
    }



    public function category($slug) {
        $category_id = Category::whereSlug($slug)->orWhere('id', $slug)->whereStatus(1)->first()->id;

        if($category_id) {
            $posts = Post::with(['user', 'media', 'tags'])
            ->whereCategoryId($category_id)   
            ->post()
            ->active()
            ->orderBy('id', 'desc')
            ->paginate(5);

            return view('frontend.index', compact('posts'));
        }

        return redirect()->route('frontend.index');
    }


    public function tag($slug) {
        $tag_id = Tag::whereSlug($slug)->orWhere('id', $slug)->first()->id;

        if($tag_id) {
            $posts = Post::with(['user', 'media', 'tags'])
            ->whereHas('tags', function($query) use ($slug) {
                $query->where('slug', $slug);
            })
            ->post()
            ->active()
            ->orderBy('id', 'desc')
            ->paginate(5);

            return view('frontend.index', compact('posts'));
        }

        return redirect()->route('frontend.index');
    }


    public function archive($date) {  // date is like 06-2020

        $exploded_date = explode('-', $date);
        $month = $exploded_date[0];
        $year = $exploded_date[1];

        $posts = Post::with(['user', 'media', 'tags'])
        ->whereMonth('created_at', $month)
        ->whereYear('created_at', $year)
        ->post()
        ->active()
        ->orderBy('id', 'desc')
        ->paginate(5);

        return view('frontend.index', compact('posts'));
    }

    
    public function author($username) {
        $user_id = User::whereUsername($username)->whereStatus(1)->first()->id;

        if($user_id) {
            $posts = Post::with(['user', 'media'])
            ->whereUserId($user_id)   
            ->post()
            ->active()
            ->orderBy('id', 'desc')
            ->paginate(5);

            return view('frontend.index', compact('posts'));
        }

        return redirect()->route('frontend.index');
    }



}
