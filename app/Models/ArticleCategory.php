<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArticleCategory extends Model
{
    // Either delete this line entirely (Eloquent will default to "article_categories"),
    // or keep it and set the correct plural table name explicitly:
    protected $table = 'article_categories';

    protected $fillable = ['name', 'slug'];

    public function articles()
    {
        return $this->hasMany(Article::class, 'article_category_id');
    }
}