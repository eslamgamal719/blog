<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use Nicolaslopezj\Searchable\SearchableTrait;


class Category extends Model
{
    use Sluggable, SearchableTrait;

    protected $guarded = [];

    protected $searchable = [
        'columns' => [
            'categories.name'   => 10,
            'categories.id'     => 10
        ]
    ];


    public function sluggable(): array {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }


    public function posts() {
        return $this->hasMany(Post::class, 'category_id');
    }

    public function status() {
        return $this->status == 1 ? 'Active' : 'Inactive';
    }
}
