<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


route::get('home', 'HomeController@index');

Route::get('/', ['as' => 'backend.index',  'uses' => 'Backend\IndexController@index']);
Route::get('/', ['as' => 'frontend.index', 'uses' => 'Frontend\IndexController@index']);



// Authentication Routes...
Route::get('/login',                            ['as' => 'frontend.show_login_form',        'uses' => 'Frontend\Auth\LoginController@showLoginForm']);
Route::post('login',                            ['as' => 'frontend.login',                  'uses' => 'Frontend\Auth\LoginController@login']);
Route::post('logout',                           ['as' => 'frontend.logout',                 'uses' => 'Frontend\Auth\LoginController@logout']);
Route::get('register',                          ['as' => 'frontend.show_register_form',     'uses' => 'Frontend\Auth\RegisterController@showRegistrationForm']);
Route::post('register',                         ['as' => 'frontend.register',               'uses' => 'Frontend\Auth\RegisterController@register']);
Route::get('password/reset',                    ['as' => 'password.request',                'uses' => 'Frontend\Auth\ForgotPasswordController@showLinkRequestForm']);
Route::post('password/email',                   ['as' => 'password.email',                  'uses' => 'Frontend\Auth\ForgotPasswordController@sendResetLinkEmail']);
Route::get('password/reset/{token}',            ['as' => 'password.reset',                  'uses' => 'Frontend\Auth\ResetPasswordController@showResetForm']);
Route::post('password/reset',                   ['as' => 'password.update',                 'uses' => 'Frontend\Auth\ResetPasswordController@reset']);
Route::get('email/verify',                      ['as' => 'verification.notice',             'uses' => 'Frontend\Auth\VerificationController@show']);
Route::get('/email/verify/{id}/{hash}',         ['as' => 'verification.verify',             'uses' => 'Frontend\Auth\VerificationController@verify']);
Route::post('email/resend',                     ['as' => 'verification.resend',             'uses' => 'Frontend\Auth\VerificationController@resend']);


Route::group(['middleware' => 'verified'], function() { //access for verified users only

    Route::get('/dashboard', 'Frontend\UsersController@index')->name('frontend.dashboard');

    Route::any('user/notifications/get', 'Frontend\NotificationsController@getNotifications');
    Route::any('user/notifications/read', 'Frontend\NotificationsController@markAsRead');
    Route::any('user/notifications/read/{id}', 'Frontend\NotificationsController@markAsReadAndRedirect');

    //edit users info
    Route::get('/edit-info', 'Frontend\UsersController@edit_info')->name('users.edit.info');
    Route::post('/edit-info', 'Frontend\UsersController@update_info')->name('users.update.info');
    Route::post('/edit-password', 'Frontend\UsersController@update_password')->name('users.update.password');

  
    //create posts
    Route::get('/create-post', 'Frontend\UsersController@create_post')->name('users.post.create');
    Route::post('/create-post', 'Frontend\UsersController@store_post')->name('users.post.store');

    //update posts
    Route::get('/edit-post/{post_id}', 'Frontend\UsersController@edit_post')->name('users.post.edit');
    Route::put('/edit-post/{post_id}', 'Frontend\UsersController@update_post')->name('users.post.update');

    //delete posts
    Route::delete('/delete-post/{post_id}', 'Frontend\UsersController@destroy_post')->name('users.post.destroy');

    //delete post images
    Route::post('/delete-post-media/{media_id}', 'Frontend\UsersController@destroy_post_media')->name('users.posts.media.destroy');

    //show comments
    Route::get('comments', 'Frontend\UsersController@show_comments')->name('users.comments');
    
    //edit comments
    Route::get('/edit-comment/{comment_id}', 'Frontend\UsersController@edit_comment')->name('users.comment.edit');
    Route::put('/edit-comment/{comment_id}', 'Frontend\UsersController@update_comment')->name('users.comment.update');

    //delete comments
    Route::delete('/delete-comment/{comment_id}', 'Frontend\UsersController@destroy_comment')->name('users.comment.destroy');
  
});







Route::group(['prefix' =>'admin'], function() {
    // Authentication Routes...
    Route::get('/login',                            ['as' => 'admin.show_login_form',        'uses' => 'backend\Auth\LoginController@showLoginForm']);
    Route::post('login',                            ['as' => 'admin.login',                  'uses' => 'backend\Auth\LoginController@login']);
    Route::post('logout',                           ['as' => 'admin.logout',                 'uses' => 'backend\Auth\LoginController@logout']);
    Route::get('password/reset',                    ['as' => 'admin.password.request',                'uses' => 'backend\Auth\ForgotPasswordController@showLinkRequestForm']);
    Route::post('password/email',                   ['as' => 'admin.password.email',                  'uses' => 'backend\Auth\ForgotPasswordController@sendResetLinkEmail']);
    Route::get('password/reset/{token}',            ['as' => 'admin.password.reset',                  'uses' => 'backend\Auth\ResetPasswordController@showResetForm']);
    Route::post('password/reset',                   ['as' => 'admin.password.update',                 'uses' => 'backend\Auth\ResetPasswordController@reset']);
    Route::get('email/verify',                      ['as' => 'admin.verification.notice',             'uses' => 'backend\Auth\VerificationController@show']);
    Route::get('/email/verify/{id}/{hash}',         ['as' => 'admin.verification.verify',             'uses' => 'backend\Auth\VerificationController@verify']);
    Route::post('email/resend',                     ['as' => 'admin.verification.resend',             'uses' => 'backend\Auth\VerificationController@resend']);
});


Route::get('/contact-us', 'Frontend\IndexController@contact')->name('frontend.contact');
Route::post('/contact-us', 'Frontend\IndexController@do_contact')->name('frontend.do.contact');

Route::get('category/{category_slug}', 'Frontend\IndexController@category')->name('frontend.category.posts');
Route::get('archive/{date}', 'Frontend\IndexController@archive')->name('frontend.archive.posts');
Route::get('author/{username}', 'Frontend\IndexController@author')->name('frontend.author.posts');

Route::get('/search', 'Frontend\IndexController@search')->name('frontend.search');
Route::get('//autocomplete-search', 'Frontend\IndexController@autocompleteSearch');

Route::get('/{post}', 'Frontend\IndexController@post_show')->name('post.show');
Route::post('/{post}', 'Frontend\IndexController@store_comment')->name('add.comment');




