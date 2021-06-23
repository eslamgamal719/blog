<?php

namespace App\Http\Controllers\Backend;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Support\Facades\Cache;
use Intervention\Image\Facades\Image;
use Stevebauman\Purify\Facades\Purify;
use Illuminate\Support\Facades\Validator;


class PostTagsController extends Controller
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
        if(!auth()->user()->ability('admin', 'manage_post_tags,show_post_tags')) {
            return redirect();
        }

        $keyword = (isset(request()->keyword) && request()->keyword != '') ? request()->keyword : null;
        $sort_by = (isset(request()->sort_by) && request()->sort_by != '') ? request()->sort_by : 'id';
        $order_by = (isset(request()->order_by) && request()->order_by != '') ? request()->order_by : 'desc';
        $limit_by = (isset(request()->limit_by) && request()->limit_by != '') ? request()->limit_by : '10';
        
        $tags = Tag::withCount('posts');

        if($keyword != null) {
            $tags = $tags->search($keyword);
        }

        $tags = $tags->orderBy($sort_by, $order_by);

        $tags = $tags->paginate($limit_by);

        return view('backend.post_tags.index', compact('tags'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if(!auth()->user()->ability('admin', 'create_post_tags')) {
            return redirect();
        }

        return view('backend.post_tags.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(!auth()->user()->ability('admin', 'create_post_tags')) {
            return redirect();
        }

        $validator = Validator::make($request->all(), [
            'name'     => 'required',
        ]);

        if($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data['name'] = $request->name;

        Tag::create($data);

        Cache::forget('global_tags');

        return redirect()->route('admin.post_tags.index')->with([
            'message'    => 'Tag Created Successfully',
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
        if(!auth()->user()->ability('admin', 'update_post_tags')) {
            return redirect();
        }

        $tag = Tag::whereId($id)->first();
        return view('backend.post_tags.edit', compact('tag'));
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
        if(!auth()->user()->ability('admin', 'update_post_tags')) {
            return redirect();
        }

        $validator = Validator::make($request->all(), [
            'name'     => 'required',
        ]);

        if($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $tag = Tag::whereId($id)->first();

        if($tag) {
            $data['name']    = $request->name;
            $data['slug']    = null;
    
            $tag->update($data);

            Cache::forget('global_tags');
            
            return redirect()->route('admin.post_tags.index')->with([
                'message'     => 'Tag Updated Successfully',
                'alert-type'  => 'success'
            ]);
        }

        return redirect()->route('admin.post_tags.index')->with([
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
        if(!auth()->user()->ability('admin', 'delete_post_tags')) {
            return redirect();
        }

        $tag = Tag::whereId($id)->first();
   
        $tag->delete();

        Cache::forget('global_tags');

        return redirect()->route('admin.post_tags.index')->with([
            'message'     => 'Tag Deleted Successfully',
            'alert-type'  => 'success'
        ]);
    
        return redirect()->route('admin.post_tags.index')->with([
            'message'     => 'Something Was Wrong',
            'alert-type'  => 'danger'
        ]);
    }


}
