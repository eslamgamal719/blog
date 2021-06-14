<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;


class AdminController extends Controller
{

    public function __construct() {
        if(!Auth::check()) {
            return redirect()->route('admin.show_login_form');
        }
    }


    public function index() {
        
        if(Auth::check()) {
             return view('backend.index');
        }else {
            return redirect()->route('admin.show_login_form');
        }
    }
}
