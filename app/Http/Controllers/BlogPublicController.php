<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class BlogPublicController extends Controller
{
    // List published articles (filters match your Blade)
    public function index(Request $request)
    {
        $q       = (string) $request->query('q', '');
        $catSlug = (string) $request->query('category', '');
        $sort    = (string) $request->query('sort', 'latest');
        $tagSlug = (string) $request->query('tag', '');

        $query = Article::query()
            ->live() // ✅ status='published' AND schedule/expiry window is currently visible
            ->with('category:id,name,slug');

        // Filters
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('title', 'like', "%{$q}%")
                  ->orWhere('excerpt', 'like', "%{$q}%")
                  ->orWhere('body', 'like', "%{$q}%");
            });
        }

        if ($catSlug !== '') {
            $query->whereHas('category', fn ($c) => $c->where('slug', $catSlug));
        }

        if ($tagSlug !== '') {
            // tags are stored as CSV; simple LIKE works if you slug when building links
            $query->where('tags', 'like', "%{$tagSlug}%");
        }

        // Sorting — use effective time: scheduled > published > created
        if ($sort === 'popular') {
            if (Schema::hasColumn('articles', 'views')) {
                $query->orderByDesc('views')
                      ->orderByEffectivePublished('desc');
            } else {
                $query->orderByEffectivePublished('desc');
            }
        } elseif ($sort === 'oldest') {
            $query->orderByEffectivePublished('asc');
        } else { // 'latest' (default)
            $query->orderByEffectivePublished('desc'); // ✅ ensures Aug 24 shows at/near top
        }

        $articles   = $query->paginate(9)->withQueryString();
        $categories = ArticleCategory::orderBy('name')->get(['id','name','slug']);
        $tags       = collect(); // provide if you later want a tag strip

        return view('blogs', compact('articles', 'categories', 'tags'));
    }


// Show a single published article by date + slug: /YYYY/MM/DD/slug
public function show(string $year, string $month, string $day, string $slug)
{
    // Only show things that are live right now
    $article = Article::live()
        ->where('slug', $slug)
        ->firstOrFail();

    // Canonical date comes from the effective publish moment
    $base = $article->effective_publish_at ?? $article->published_at ?? $article->created_at;

    // Convert to Asia/Manila for canonical date in URL
    $manila = Carbon::parse($base)->timezone('Asia/Manila');
    $yy = $manila->format('Y'); // 4-digit, e.g. "2025"
    $mm = $manila->format('m');
    $dd = $manila->format('d');

    // If URL date doesn't match canonical date, redirect permanently
    if ($year !== $yy || $month !== $mm || $day !== $dd) {
        return redirect()->route('articles.show', [
            'year'  => $yy,
            'month' => $mm,
            'day'   => $dd,
            'slug'  => $article->slug,
        ], 301);
    }

    return view('articles.show', compact('article'));
}


}
