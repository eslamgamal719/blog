<?php

namespace App\Http\Livewire\Backend;

use App\Models\Comment;
use App\Models\Post;
use Livewire\Component;

class LastPostsComments extends Component
{
    public function render()
    {
        $posts = Post::wherePostType('post')->withCount('comments')->whereStatus(1)->orderBy('id', 'desc')->take(5)->get();

        $comments = Comment::orderBy('id', 'desc')->take(5)->get();

        return view('livewire.backend.last-posts-comments', [
            'posts'    => $posts,
            'comments' => $comments,
        ]);
    }
}
