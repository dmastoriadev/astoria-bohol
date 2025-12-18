<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ArticleCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function store(Request $request)
{
    $data = $request->validate([
        'name' => ['required','string','max:120', Rule::unique('article_categories','name')],
    ]);

    // If you keep a slug column for internal use, set it silently (not unique)
    \App\Models\ArticleCategory::create([
        'name' => trim($data['name']),
        'slug' => Str::slug($data['name']), // optional, not shown to users
    ]);

    return back()->with('success', 'Category created.');
}

public function destroy(\App\Models\ArticleCategory $category)
{
    // detach from articles (set to null)
    \App\Models\Article::where('article_category_id', $category->id)
        ->update(['article_category_id' => null]);

    $name = $category->name;
    $category->delete();

    return back()->with('success', "Category “{$name}” deleted.");
}
}
