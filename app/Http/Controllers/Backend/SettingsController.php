<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Setting;

class SettingsController extends Controller
{

    public function __construct()
    {
        if(auth()->check()) {
            $this->middleware('auth');
        }else {
            return view('backend.auth.login');
        }
    }


    
    public function index()
    {
        if(!auth()->user()->ability('admin', 'manage_settings,show_settings')) {
            return redirect('admin/index');
        }

        $section = (isset(request()->section) && request()->section != '') ? request()->section : 'general';
        $settings_sections = Setting::select('section')->distinct()->pluck('section');
        $settings = Setting::whereSection($section)->get();

        return view('backend.settings.index', compact('section', 'settings_sections', 'settings'));
    }


    
    public function update(Request $request, $id)
    {
        //
    }


    
    public function destroy($id)
    {
        //
    }
}
