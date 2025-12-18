<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use App\Models\Article;
use App\Models\Promo;
use App\Models\ArticleCategory;
use App\Models\Popup; // ✅ Pop-ups

class DashboardController extends Controller
{
    /**
     * Admin dashboard landing page (single-page admin).
     */
    public function index(Request $request)
    {
        // ===== Guards & handy flags =====
        $articleModel      = new Article();
        $articleTable      = $articleModel->getTable();
        $hasArticleTable   = Schema::hasTable($articleTable);
        $hasArticleDeleted = $hasArticleTable && Schema::hasColumn($articleTable, 'deleted_at');

        $promoModel        = new Promo();
        $promoTable        = $promoModel->getTable();
        $hasPromoTable     = Schema::hasTable($promoTable);
        $hasPromoDeleted   = $hasPromoTable && Schema::hasColumn($promoTable, 'deleted_at');

        $categoryModel     = new ArticleCategory();
        $categoryTable     = $categoryModel->getTable();
        $hasCategoryTable  = Schema::hasTable($categoryTable);

        // ✅ Pop-ups table guard
        $popupModel        = new Popup();
        $popupTable        = $popupModel->getTable();
        $hasPopupTable     = Schema::hasTable($popupTable);

        $nowUtc = now('UTC');

        // ===== Live articles (server-side filters + pagination) =====
        $articles = $hasArticleTable
            ? Article::query()
                ->with(['category:id,name,slug', 'creator:id,name'])
                // text search
                ->when($request->filled('blog_q'), function ($q) use ($request) {
                    $term = '%'.trim((string)$request->query('blog_q')).'%';
                    $q->where(function ($qq) use ($term) {
                        $qq->where('title',   'like', $term)
                           ->orWhere('slug',   'like', $term)
                           ->orWhere('excerpt','like', $term)
                           ->orWhere('body',   'like', $term)
                           ->orWhere('tags',   'like', $term)
                           ->orWhereHas('category', fn ($c) => $c->where('name','like',$term));
                    });
                })
                // category filter (slug) — "__none" means null
                ->when($request->filled('blog_category'), function ($q) use ($request) {
                    $slug = (string)$request->query('blog_category');
                    if ($slug === '__none') {
                        $q->whereNull('article_category_id');
                    } elseif ($slug !== '') {
                        $q->whereHas('category', fn ($c) => $c->where('slug', $slug));
                    }
                })
                // creator filter (value is the display name now)
                ->when($request->filled('blog_creator'), function ($q) use ($request) {
                    $name = (string)$request->query('blog_creator');
                    $q->where(function ($qq) use ($name) {
                        $qq->where('created_by_name', $name)
                           ->orWhereHas('creator', fn ($c) => $c->where('name', $name));
                    });
                })
                // status filter using effective times
                ->when($request->filled('blog_status'), function ($q) use ($request, $nowUtc) {
                    $status = (string)$request->query('blog_status');
                    $q->when($status === 'expired', fn ($qq) =>
                            $qq->whereNotNull('expires_at')->where('expires_at', '<=', $nowUtc)
                      )->when($status === 'scheduled', fn ($qq) =>
                            $qq->where(function ($w) use ($nowUtc) {
                                $w->where('scheduled_publish_date', '>', $nowUtc)
                                  ->orWhere('published_at', '>', $nowUtc);
                            })
                      )->when($status === 'published', fn ($qq) =>
                            $qq->where(function ($w) use ($nowUtc) {
                                $w->where(function ($z) use ($nowUtc) {
                                      $z->whereNotNull('published_at')->where('published_at', '<=', $nowUtc);
                                  })->orWhere(function ($z) use ($nowUtc) {
                                      $z->whereNotNull('scheduled_publish_date')->where('scheduled_publish_date', '<=', $nowUtc);
                                  });
                            })->where(function ($w) use ($nowUtc) {
                                $w->whereNull('expires_at')->orWhere('expires_at', '>', $nowUtc);
                            })
                      )->when($status === 'draft', fn ($qq) =>
                            $qq->where(function ($w) {
                                $w->where('status', 'draft')
                                  ->orWhere(function ($z) {
                                      $z->whereNull('published_at')->whereNull('scheduled_publish_date');
                                  });
                            })
                      );
                })
                // sort
                ->when(($request->query('blog_sort') ?? 'latest') === 'oldest',
                       fn ($q) => $q->orderBy('created_at', 'asc'),
                       fn ($q) => $q->orderBy('created_at', 'desc'))
                ->paginate(15, ['*'], 'articles_page')
                ->withQueryString()
            : collect();

        // ===== Trashed articles (paginated with page clamp) =====
        $trashedArticles = collect();
        if ($hasArticleTable && $hasArticleDeleted) {
            $trashPage = max(1, (int) $request->query('trash_page', 1));

            $trashedArticles = Article::onlyTrashed()
                ->with(['category:id,name,slug', 'creator:id,name'])
                ->latest('deleted_at')
                ->paginate(10, ['*'], 'trash_page', $trashPage)
                ->withQueryString();

            if ($trashedArticles->lastPage() > 0 && $trashedArticles->currentPage() > $trashedArticles->lastPage()) {
                $trashedArticles = Article::onlyTrashed()
                    ->with(['category:id,name,slug', 'creator:id,name'])
                    ->latest('deleted_at')
                    ->paginate(10, ['*'], 'trash_page', $trashedArticles->lastPage())
                    ->appends($request->except('trash_page'));
            }
        }

        // ===== Promos (server-side filters + pagination) =====
        $promos = $hasPromoTable
            ? Promo::query()
                ->with(['creator:id,name'])
                // text search
                ->when($request->filled('promo_q'), function ($q) use ($request) {
                    $term = '%'.trim((string)$request->query('promo_q')).'%';
                    $q->where(function ($qq) use ($term) {
                        $qq->where('title','like',$term)
                           ->orWhere('slug','like',$term)
                           ->orWhere('body','like',$term);
                    });
                })
                // category (all|regular|premium or your property enums)
                ->when($request->filled('promo_category'), fn ($q) =>
                    $q->where('category', (string)$request->query('promo_category'))
                )
                // creator (display name)
                ->when($request->filled('promo_creator'), function ($q) use ($request) {
                    $name = (string)$request->query('promo_creator');
                    $q->where(function ($qq) use ($name) {
                        $qq->where('created_by_name', $name)
                           ->orWhereHas('creator', fn ($c) => $c->where('name', $name));
                    });
                })
                // status via times
                ->when($request->filled('promo_status'), function ($q) use ($request, $nowUtc) {
                    $status = (string)$request->query('promo_status');
                    $q->when($status === 'expired', fn ($qq) =>
                            $qq->whereNotNull('expires_at')->where('expires_at', '<=', $nowUtc)
                      )->when($status === 'scheduled', fn ($qq) =>
                            $qq->where(function ($w) use ($nowUtc) {
                                $w->where('scheduled_publish_date', '>', $nowUtc)
                                  ->orWhere('published_at', '>', $nowUtc);
                            })
                      )->when($status === 'published', fn ($qq) =>
                            $qq->where(function ($w) use ($nowUtc) {
                                $w->where(function ($z) use ($nowUtc) {
                                      $z->whereNotNull('published_at')->where('published_at', '<=', $nowUtc);
                                  })->orWhere(function ($z) use ($nowUtc) {
                                      $z->whereNotNull('scheduled_publish_date')->where('scheduled_publish_date', '<=', $nowUtc);
                                  });
                            })->where(function ($w) use ($nowUtc) {
                                $w->whereNull('expires_at')->orWhere('expires_at', '>', $nowUtc);
                            })
                      )->when($status === 'draft', fn ($qq) =>
                            $qq->where(function ($w) {
                                $w->where('status', 'draft')
                                  ->orWhere(function ($z) {
                                      $z->whereNull('published_at')->whereNull('scheduled_publish_date');
                                  });
                            })
                      );
                })
                // sort
                ->when(($request->query('promo_sort') ?? 'latest') === 'oldest',
                       fn ($q) => $q->orderBy('created_at', 'asc'),
                       fn ($q) => $q->orderBy('created_at', 'desc'))
                ->paginate(15, ['*'], 'promos_page')
                ->withQueryString()
            : collect();

        // ===== Trashed promos (paginated with page clamp) =====
        $trashedPromos = collect();
        if ($hasPromoTable && $hasPromoDeleted) {
            $promosTrashPage = max(1, (int) $request->query('promos_trash_page', 1));

            $trashedPromos = Promo::onlyTrashed()
                ->with(['creator:id,name'])
                ->latest('deleted_at')
                ->paginate(10, ['*'], 'promos_trash_page', $promosTrashPage)
                ->withQueryString();

            if ($trashedPromos->lastPage() > 0 && $trashedPromos->currentPage() > $trashedPromos->lastPage()) {
                $trashedPromos = Promo::onlyTrashed()
                    ->with(['creator:id,name'])
                    ->latest('deleted_at')
                    ->paginate(10, ['*'], 'promos_trash_page', $trashedPromos->lastPage())
                    ->appends($request->except('promos_trash_page'));
            }
        }

        // ✅ Pop-ups (simple listing for dashboard table)
        $popups = $hasPopupTable
            ? Popup::query()
                // quick text search on title/description/image/click class
                ->when($request->filled('popup_q'), function ($q) use ($request) {
                    $term = '%'.trim((string)$request->query('popup_q')).'%';
                    $q->where(function ($qq) use ($term) {
                        $qq->where('title',        'like', $term)
                           ->orWhere('description', 'like', $term)
                           ->orWhere('image_path',  'like', $term)
                           ->orWhere('click_class', 'like', $term);
                    });
                })
                // filter by active / inactive
                ->when($request->filled('popup_status'), function ($q) use ($request) {
                    $status = (string) $request->query('popup_status');
                    if ($status === 'active') {
                        $q->where('is_active', true)->where('is_draft', false);
                    } elseif ($status === 'inactive') {
                        $q->where(function ($w) {
                            $w->where('is_active', false)->where('is_draft', false);
                        });
                    } elseif ($status === 'draft') {
                        $q->where('is_draft', true);
                    }
                })
                ->orderBy('created_at', 'desc')
                ->paginate(15, ['*'], 'popups_page')
                ->withQueryString()
            : collect();

        // ===== Stats =====
        $blogsCount      = $hasArticleTable ? Article::where('status', 'published')->count() : 0;
        $blogsTrashCount = ($hasArticleTable && $hasArticleDeleted) ? Article::onlyTrashed()->count() : 0;

        $careersCount = 0;
        if (class_exists(\App\Models\Career::class)) {
            $careerModel = new \App\Models\Career();
            if (Schema::hasTable($careerModel->getTable())) {
                $careersCount = \App\Models\Career::where('status', 'published')->count();
            }
        }

        $promosCount      = $hasPromoTable ? Promo::where('status', 'published')->count() : 0;
        $promosTrashCount = ($hasPromoTable && $hasPromoDeleted) ? Promo::onlyTrashed()->count() : 0;

        // ✅ Active pop-ups (live: active & not draft)
        $popupsActiveCount = $hasPopupTable
            ? Popup::where('is_active', true)->where('is_draft', false)->count()
            : 0;

        $stats = [
            'blogs'          => $blogsCount,
            'careers'        => $careersCount,
            'promos'         => $promosCount,
            'blogs_trash'    => $blogsTrashCount,
            'promos_trash'   => $promosTrashCount,
            'popups_active'  => $popupsActiveCount,
        ];

        // ✅ Aliases for your cards
        $trashedBlogsCount  = $blogsTrashCount;
        $trashedPromosCount = $promosTrashCount;

        // ===== Categories for blog forms =====
        $categories = $hasCategoryTable
            ? ArticleCategory::orderBy('name')->get(['id', 'name', 'slug'])
            : collect();

        // ===== Page-level items for "Recent Items (this page)" =====
        $blogItems = $articles instanceof \Illuminate\Contracts\Pagination\Paginator
            ? collect($articles->items())
            : collect($articles);

        $promoItems = $promos instanceof \Illuminate\Contracts\Pagination\Paginator
            ? collect($promos->items())
            : collect($promos);

        $popupItems = $popups instanceof \Illuminate\Contracts\Pagination\Paginator
            ? collect($popups->items())
            : collect($popups);

        // ✅ "New This Week (page)" = blogs on this page created this calendar week (Asia/Manila)
        $nowPh       = now('Asia/Manila');
        $weekStartPh = $nowPh->copy()->startOfWeek(); // Monday 00:00 by default

        $newBlogsThisWeekOnPage = $blogItems->filter(function ($article) use ($weekStartPh) {
            if (empty($article->created_at)) {
                return false;
            }
            $created = $article->created_at->copy()->timezone('Asia/Manila');
            return $created->greaterThanOrEqualTo($weekStartPh);
        })->count();

        // ===== Image gallery for thumbnail picker (blogs & promos) =====
        $gallery = $this->gatherGallery();

        return view('admin.dashboard', compact(
            'articles',
            'promos',
            'popups',              // pop-up paginator for tables
            'stats',
            'categories',
            'blogItems',
            'promoItems',
            'popupItems',
            'trashedBlogsCount',
            'trashedPromosCount',
            'newBlogsThisWeekOnPage',
            'popupsActiveCount'
        ))->with([
            // article trash aliases
            'trashedArticles' => $trashedArticles,
            'trashed'         => $trashedArticles,
            'articlesTrash'   => $trashedArticles,
            'trashArticles'   => $trashedArticles,

            // promo trash aliases (for your promos_trashtable.blade.php)
            'trashedPromos'   => $trashedPromos,
            'promosTrash'     => $trashedPromos,
            'trashPromos'     => $trashedPromos,

            'gallery'         => $gallery,
        ]);
    }

    /**
     * FRAGMENT: Blogs table (server-side filter + pagination, returns HTML).
     */
    public function blogsFragment(Request $request)
    {
        $nowUtc = now('UTC');

        $articles = Article::query()
            ->with(['category:id,name,slug', 'creator:id,name'])
            // search
            ->when($request->filled('blog_q'), function ($q) use ($request) {
                $term = '%'.trim((string)$request->query('blog_q')).'%';
                $q->where(function ($qq) use ($term) {
                    $qq->where('title',   'like', $term)
                       ->orWhere('slug',   'like', $term)
                       ->orWhere('excerpt','like', $term)
                       ->orWhere('body',   'like', $term)
                       ->orWhere('tags',   'like', $term)
                       ->orWhereHas('category', fn ($c) => $c->where('name','like',$term));
                });
            })
            // category
            ->when($request->filled('blog_category'), function ($q) use ($request) {
                $slug = (string)$request->query('blog_category');
                if ($slug === '__none') {
                    $q->whereNull('article_category_id');
                } elseif ($slug !== '') {
                    $q->whereHas('category', fn ($c) => $c->where('slug', $slug));
                }
            })
            // creator (name)
            ->when($request->filled('blog_creator'), function ($q) use ($request) {
                $name = (string)$request->query('blog_creator');
                $q->where(function ($qq) use ($name) {
                    $qq->where('created_by_name', $name)
                       ->orWhereHas('creator', fn ($c) => $c->where('name', $name));
                });
            })
            // status
            ->when($request->filled('blog_status'), function ($q) use ($request, $nowUtc) {
                $status = (string)$request->query('blog_status');
                $q->when($status === 'expired', fn ($qq) =>
                        $qq->whereNotNull('expires_at')->where('expires_at', '<=', $nowUtc)
                  )->when($status === 'scheduled', fn ($qq) =>
                        $qq->where(function ($w) use ($nowUtc) {
                            $w->where('scheduled_publish_date', '>', $nowUtc)
                              ->orWhere('published_at', '>', $nowUtc);
                        })
                  )->when($status === 'published', fn ($qq) =>
                        $qq->where(function ($w) use ($nowUtc) {
                            $w->where(function ($z) use ($nowUtc) {
                                  $z->whereNotNull('published_at')->where('published_at', '<=', $nowUtc);
                              })->orWhere(function ($z) use ($nowUtc) {
                                  $z->whereNotNull('scheduled_publish_date')->where('scheduled_publish_date', '<=', $nowUtc);
                              });
                        })->where(function ($w) use ($nowUtc) {
                            $w->whereNull('expires_at')->orWhere('expires_at', '>', $nowUtc);
                        })
                  )->when($status === 'draft', fn ($qq) =>
                        $qq->where(function ($w) {
                            $w->where('status', 'draft')
                              ->orWhere(function ($z) {
                                  $z->whereNull('published_at')->whereNull('scheduled_publish_date');
                              });
                        })
                  );
            })
            // sort
            ->when(($request->query('blog_sort') ?? 'latest') === 'oldest',
                   fn ($q) => $q->orderBy('created_at', 'asc'),
                   fn ($q) => $q->orderBy('created_at', 'desc'))
            ->paginate(15)
            ->withQueryString();

        $articles->withPath(route('admin.dashboard.fragment.blogs'));

        return view('admin.dashboard.partials.blogs_table', compact('articles'))->render();
    }

    /**
     * FRAGMENT: Promos table (server-side filter + pagination, returns HTML).
     */
    public function promosFragment(Request $request)
    {
        $nowUtc = now('UTC');

        $promos = Promo::query()
            ->with(['creator:id,name'])
            // search
            ->when($request->filled('promo_q'), function ($q) use ($request) {
                $term = '%'.trim((string)$request->query('promo_q')).'%';
                $q->where(function ($qq) use ($term) {
                    $qq->where('title','like',$term)
                       ->orWhere('slug','like',$term)
                       ->orWhere('body','like',$term);
                });
            })
            // category
            ->when($request->filled('promo_category'), fn ($q) =>
                $q->where('category', (string) $request->query('promo_category'))
            )
            // creator
            ->when($request->filled('promo_creator'), function ($q) use ($request) {
                $name = (string)$request->query('promo_creator');
                $q->where(function ($qq) use ($name) {
                    $qq->where('created_by_name', $name)
                       ->orWhereHas('creator', fn ($c) => $c->where('name', $name));
                });
            })
            // status
            ->when($request->filled('promo_status'), function ($q) use ($request, $nowUtc) {
                $status = (string)$request->query('promo_status');
                $q->when($status === 'expired', fn ($qq) =>
                        $qq->whereNotNull('expires_at')->where('expires_at', '<=', $nowUtc)
                  )->when($status === 'scheduled', fn ($qq) =>
                        $qq->where(function ($w) use ($nowUtc) {
                            $w->where('scheduled_publish_date', '>', $nowUtc)
                              ->orWhere('published_at', '>', $nowUtc);
                        })
                  )->when($status === 'published', fn ($qq) =>
                        $qq->where(function ($w) use ($nowUtc) {
                            $w->where(function ($z) use ($nowUtc) {
                                  $z->whereNotNull('published_at')->where('published_at', '<=', $nowUtc);
                              })->orWhere(function ($z) use ($nowUtc) {
                                  $z->whereNotNull('scheduled_publish_date')->where('scheduled_publish_date', '<=', $nowUtc);
                              });
                        })->where(function ($w) use ($nowUtc) {
                            $w->whereNull('expires_at')->orWhere('expires_at', '>', $nowUtc);
                        })
                  )->when($status === 'draft', fn ($qq) =>
                        $qq->where(function ($w) {
                            $w->where('status', 'draft')
                              ->orWhere(function ($z) {
                                  $z->whereNull('published_at')->whereNull('scheduled_publish_date');
                              });
                        })
                  );
            })
            // sort
            ->when(($request->query('promo_sort') ?? 'latest') === 'oldest',
                   fn ($q) => $q->orderBy('created_at', 'asc'),
                   fn ($q) => $q->orderBy('created_at', 'desc'))
            ->paginate(15)
            ->withQueryString();

        $promos->withPath(route('admin.dashboard.fragment.promos'));

        return view('admin.dashboard.partials.promos_table', compact('promos'))->render();
    }

    /**
     * FRAGMENT: Promos trash table (server-side pagination, returns HTML).
     */
    public function promosTrashFragment(Request $request)
    {
        $trashedPromos = Promo::onlyTrashed()
            ->with(['creator:id,name'])
            ->latest('deleted_at')
            ->paginate(10)
            ->withQueryString();

        // Ensure pagination links target this fragment route
        $trashedPromos->withPath(route('admin.dashboard.fragment.promos-trash'));

        return view('admin.dashboard.partials.promos_trashtable', [
            'trashedPromos' => $trashedPromos,
        ])->render();
    }

    /**
     * Collect recent images from common public storage folders to power a simple picker.
     * Returns an array of ['path' => string, 'url' => string, 'mtime' => int].
     */
    private function gatherGallery(): array
    {
        $disk = Storage::disk('public');

        // You can tweak or extend these folders anytime
        $dirs = ['articles', 'promos', 'tinymce', 'uploads', 'gallery'];

        $out  = [];

        foreach ($dirs as $dir) {
            try {
                foreach ($disk->files($dir) as $p) {
                    if (!preg_match('/\.(jpe?g|png|gif|webp|svg)$/i', $p)) {
                        continue;
                    }
                    $out[] = [
                        'path'  => $p,
                        'url'   => Storage::url($p),
                        'mtime' => $disk->lastModified($p),
                    ];
                }
            } catch (\Throwable $e) {
                // missing directory is fine
            }
        }

        // newest first
        usort($out, fn ($a, $b) => $b['mtime'] <=> $a['mtime']);

        // cap to avoid huge payloads
        return array_slice($out, 0, 200);
    }
}
