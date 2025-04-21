<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Nicolaslopezj\Searchable\SearchableTrait;

class Tag extends Model
{
    use HasFactory, Sluggable, SearchableTrait;


    protected $guarded = [];

    protected $searchable = [
        'columns' => [
            'tags.name' => 10,
            'tags.slug' => 10,
        ]
    ];

    public function sluggable(): array
    {
        return [
            "slug" => [
                'source' => 'name',
                ]
            ];
    }


    public function posts() {
        return $this->belongsToMany(Post::class, 'posts_tags');
    }


}
