<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name','slug'];

    public function articles()
    {
        return $this->belongsToMany(\App\Models\Article::class, 'article_category');
    }
}
