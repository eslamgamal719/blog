<?php

namespace App\Http\Controllers\Frontend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class NotificationsController extends Controller
{
    
    public function getNotifications() {
        return [
            'read' => auth()->user()->readNotifications,
            'unread' => auth()->user()->unreadNotifications,
            'userType' => auth()->user()->roles->first()->name,
        ];
    }    


    public function markAsRead(Request $request) {
        return auth()->user()->notifications->where('id', $request->id)->markAsRead();
    }


    public function markAsReadAndRedirect($id) {
        $notification = auth()->user()->notifications->where('id', $id)->first();

        $notification->markAsRead();

        if(auth()->user()->roles->first()->name == 'user') {

            if($notification->type == 'App\Notifications\NewCommentForPostOwnerNotify') {
                return redirect()->route('users.comment.edit', $notification->data['comment_id']);
            }
        }else {
            return redirect()->back();
        }
        
    }


}
