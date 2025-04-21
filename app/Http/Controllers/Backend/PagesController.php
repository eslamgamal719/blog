<?php

namespace App\Http\Controllers\Backend;

use App\Models\Page;
use App\Models\Category;
use App\Models\PostMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Intervention\Image\Facades\Image;
use Stevebauman\Purify\Facades\Purify;
use Illuminate\Support\Facades\Validator;


class PagesController extends Controller
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
        if(!auth()->user()->ability('admin', 'manage_pages,show_pages')) {
            return redirect('admin/index');
        }

        $keyword = (isset(request()->keyword) && request()->keyword != '') ? request()->keyword : null;
        $categoryId = (isset(request()->category_id) && request()->category_id != '') ? request()->category_id : null;
        $status = (isset(request()->status) && request()->status != '') ? request()->status : null;
        $sort_by = (isset(request()->sort_by) && request()->sort_by != '') ? request()->sort_by : 'id';
        $order_by = (isset(request()->order_by) && request()->order_by != '') ? request()->order_by : 'desc';
        $limit_by = (isset(request()->limit_by) && request()->limit_by != '') ? request()->limit_by : '10';

        $categories = Category::orderBy('id', 'desc')->pluck('name', 'id');

        $pages = Page::wherePostType('page');

        if($keyword != null) {
            $pages = $pages->search($keyword);
        }

        if($categoryId != null) {
            $pages = $pages->whereCategoryId($categoryId);
        }

        if($status != null) {
            $pages = $pages->whereStatus($status);
        }

        $pages = $pages->orderBy($sort_by, $order_by);

        $pages = $pages->paginate($limit_by);

        return view('backend.pages.index', compact('pages', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if(!auth()->user()->ability('admin', 'create_pages')) {
            return redirect('admin/index');
        }

        $categories = Category::orderBy('id', 'desc')->pluck('name', 'id');
        return view('backend.pages.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(!auth()->user()->ability('admin', 'create_pages')) {
            return redirect('admin/index');
        }

        $validator = Validator::make($request->all(), [
            'title'          => 'required',
            'description'    => 'required|min:10',
            'status'         => 'required',
            'category_id'    => 'required',
            'images.*'       => 'nullable|mimes:jpg,jpeg,png,gif|max:20000' //20000 = 2MB size
        ]);

        if($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data['title']          = $request->title;
        $data['description']    = $request->description;
        $data['status']         = $request->status;
        $data['post_type']      = 'page';
        $data['comment_able']   = 0;
        $data['category_id']    = $request->category_id;

        $page = auth()->user()->posts()->create($data);

        if($request->images && $request->images > 0) {

            $i = 1;
            foreach($request->images as $file) {

                $fileName = $page->slug . '-' . time() . '-' . $i . '.' . $file->getClientOriginalExtension();
                $fileSize = $file->getSize();
                $fileType = $file->getMimeType();
                $path = public_path('assets/posts/' . $fileName);

                Image::make($file->getRealPath())->resize(800, null, function($constraint) {
                    $constraint->aspectRatio();
                })->save($path, 100);

                $page->media()->create([
                    'file_name' => $fileName,
                    'file_size' => $fileSize,
                    'file_type' => $fileType,
                ]);
                $i++;
            }
        }

        return redirect()->route('admin.pages.index')->with([
            'message'    => 'Page Created Successfully',
            'alert-type' => 'success'
        ]);
    }


    public function show($id)
    {
        if(!auth()->user()->ability('admin', 'display_pages')) {
            return redirect('admin/index');
        }

        $page = Page::with('media')->whereId($id)->wherePostType('page')->first();
        return view('backend.pages.show', compact('page'));
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if(!auth()->user()->ability('admin', 'update_pages')) {
            return redirect('admin/index');
        }

        $categories = Category::orderBy('id', 'desc')->pluck('name', 'id');
        $page = Page::with('media')->whereId($id)->wherePostType('page')->first();
        return view('backend.pages.edit', compact('categories', 'page'));
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
        if(!auth()->user()->ability('admin', 'update_pages')) {
            return redirect('admin/index');
        }

        $validator = Validator::make($request->all(), [
            'title'          => 'required',
            'description'    => 'required|min:10',
            'status'         => 'required',
            'category_id'    => 'required',
            'images.*'       => 'nullable|mimes:jpg,jpeg,png,gif|max:20000' //20000 = 2MB size
        ]);

        if($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $page = Page::whereId($id)->wherePostType('page')->first();

        if($page) {
            $data['title']          = $request->title;
            $data['slug']           = null;
            $data['description']    = $request->description;
            $data['status']         = $request->status;
            $data['category_id']    = $request->category_id;

            $page->update($data);

            if($request->images && $request->images > 0) {

                $i = 1;
                foreach($request->images as $file) {
                    $fileName = $page->slug . '-' . time() . '-' . $i . '.' . $file->getClientOriginalExtension();
                    $fileSize = $file->getSize();
                    $fileType = $file->getMimeType();
                    $path     = public_path('assets/posts/' . $fileName);

                    Image::make($file->getRealPath())->resize(800, null, function($constraint) {
                        $constraint->aspectRatio();
                    })->save($path, 100);

                    $page->media()->create([
                        'file_name' => $fileName,
                        'file_size' => $fileSize,
                        'file_type' => $fileType,
                    ]);

                    $i++;
                }
            }

            return redirect()->route('admin.pages.index')->with([
                'message'     => 'Page Updated Successfully',
                'alert-type'  => 'success'
            ]);
        }

        return redirect()->route('admin.pages.index')->with([
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
        if(!auth()->user()->ability('admin', 'delete_pages')) {
            return redirect('admin/index');
        }

        $page = Page::whereId($id)->wherePostType('page')->first();

        if($page) {
            if($page->media->count() > 0) {
                foreach($page->media as $media) {
                    if(File::exists('assets/posts/' . $media->file_name)) {
                        unlink('assets/posts/' . $media->file_name);
                    }
                }
            }
            $page->delete();

            return redirect()->route('admin.pages.index')->with([
                'message'     => 'Page Deleted Successfully',
                'alert-type'  => 'success'
            ]);
        }

        return redirect()->route('admin.pages.index')->with([
            'message'     => 'Something Was Wrong',
            'alert-type'  => 'danger'
        ]);
    }


    public function removeImage($media_id)
    {
        if(!auth()->user()->ability('admin', 'delete_pages')) {
            return redirect('admin/index');
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
