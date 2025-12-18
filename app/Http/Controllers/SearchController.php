<?php
// app/Http/Controllers/SearchController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route as RouteFacade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Pagination\LengthAwarePaginator;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        // Normalize query: trim + collapse spaces
        $raw = (string) $request->query('q', '');
        $q   = Str::squish(trim($raw));

        if ($q === '') {
            return view('search', [
                'q'          => '',
                'results'    => collect(),
                'suggestion' => null,
            ]);
        }

        $results = collect();

        // ---- DB MODELS (auto-detect existing columns) ---------------------------------
        $results = $results->concat(
            $this->searchModel(
                \App\Models\Article::class,
                ['title','name','excerpt','body','description','content'],
                function ($a) {
                    $url = RouteFacade::has('articles.show')
                        ? route('articles.show', $a->slug ?? $a->id)
                        : url('/blogs/' . ($a->slug ?? $a->id));

                    return [
                        'type'         => 'blog',
                        'title'        => $a->title ?? $a->name ?? 'Untitled',
                        'url'          => $url,
                        'excerpt'      => $a->excerpt
                            ?? Str::limit(strip_tags($a->body ?? $a->description ?? ''), 240),
                        'image'        => $a->thumb_url ?? $a->image ?? null,
                        'category'     => optional($a->category)->name ?? $a->category ?? null,
                        'published_at' => $a->effective_publish_at
                            ?? $a->published_at
                            ?? $a->created_at,
                        'score'        => 90,
                    ];
                },
                $q
            )
        );

        $results = $results->concat(
            $this->searchModel(
                \App\Models\Promo::class,
                ['title','name','excerpt','body','description','content'],
                function ($p) {
                    $url = RouteFacade::has('promos.show')
                        ? route('promos.show', $p->slug ?? $p->id)
                        : url('/promos/' . ($p->slug ?? $p->id));

                    return [
                        'type'         => 'promo',
                        'title'        => $p->title ?? $p->name ?? 'Promo',
                        'url'          => $url,
                        'excerpt'      => $p->excerpt
                            ?? Str::limit(strip_tags($p->body ?? $p->description ?? ''), 240),
                        'image'        => $p->thumb_url ?? $p->image ?? null,
                        'category'     => $p->category ?? null,
                        'published_at' => $p->effective_publish_at
                            ?? $p->published_at
                            ?? $p->created_at,
                        'score'        => 80,
                    ];
                },
                $q
            )
        );

        $results = $results->concat(
            $this->searchModel(
                \App\Models\Room::class,
                ['title','name','excerpt','description','body','content'],
                function ($r) {
                    $url = RouteFacade::has('rooms.show')
                        ? route('rooms.show', $r->slug ?? $r->id)
                        : url('/accommodations/' . ($r->slug ?? $r->id));

                    return [
                        'type'         => 'room',
                        'title'        => $r->title ?? $r->name ?? 'Room',
                        'url'          => $url,
                        'excerpt'      => Str::limit(
                            strip_tags($r->excerpt ?? $r->description ?? $r->body ?? ''),
                            240
                        ),
                        'image'        => $r->thumb_url ?? $r->image ?? null,
                        'category'     => $r->category ?? 'Accommodations',
                        'published_at' => $r->updated_at ?? $r->created_at,
                        'score'        => 70,
                    ];
                },
                $q
            )
        );

        $results = $results->concat(
            $this->searchModel(
                \App\Models\Page::class,
                ['title','name','excerpt','body','content','description'],
                function ($pg) {
                    $url = RouteFacade::has('pages.show')
                        ? route('pages.show', $pg->slug ?? $pg->id)
                        : url('/' . ($pg->slug ?? ('page/' . $pg->id)));

                    return [
                        'type'         => 'page',
                        'title'        => $pg->title ?? $pg->name ?? 'Page',
                        'url'          => $url,
                        'excerpt'      => Str::limit(
                            strip_tags($pg->excerpt ?? $pg->body ?? $pg->content ?? ''),
                            240
                        ),
                        'image'        => $pg->thumb_url ?? $pg->image ?? null,
                        'category'     => $pg->category ?? null,
                        'published_at' => $pg->updated_at ?? $pg->created_at,
                        'score'        => 60,
                    ];
                },
                $q
            )
        );

        // ---- STATIC BLADE PAGES (About, Amenities, FAQs, Dining, Meetings, etc.) ------
        $results = $results->concat($this->staticPages($q));

        // ---- SORT + PAGINATE ----------------------------------------------------------
        $results = $results
            ->sortByDesc(function ($r) use ($q) {
                $score = (int) ($r['score'] ?? 0);
                $title = Str::lower($r['title'] ?? '');

                if ($title && Str::contains($title, Str::lower($q))) {
                    $score += 15;
                }

                $ts = strtotime((string) ($r['published_at'] ?? now()));

                return $score * 10_000_000 + $ts;
            })
            ->values();

        $perPage   = 12;
        $page      = LengthAwarePaginator::resolveCurrentPage();
        $slice     = $results->slice(($page - 1) * $perPage, $perPage)->values();
        $paginator = new LengthAwarePaginator(
            $slice,
            $results->count(),
            $perPage,
            $page,
            ['path' => route('search'), 'query' => ['q' => $q]]
        );

        return view('search', [
            'q'          => $q,
            'results'    => $paginator,
            'suggestion' => null,
        ]);
    }

    /**
     * Token-based LIKE search that only uses columns that exist on the table.
     * Tokens split on spaces AND hyphens, so "check-out" → "check", "out".
     */
    protected function searchModel(string $class, array $candidateColumns, \Closure $map, string $q)
    {
        if (!class_exists($class) || $q === '') {
            return collect();
        }

        $model = new $class;
        $table = method_exists($model, 'getTable') ? $model->getTable() : null;

        if (!$table || !Schema::hasTable($table)) {
            return collect();
        }

        $columns = Schema::getColumnListing($table);
        $usable  = array_values(array_intersect($candidateColumns, $columns));
        if (empty($usable)) {
            return collect();
        }

        // Split on spaces AND hyphens
        $tokens = array_filter(preg_split('/[\s\-]+/u', $q));
        if (empty($tokens)) {
            $tokens = [$q];
        }

        $query = $class::query();

        $query->where(function ($outer) use ($usable, $tokens) {
            foreach ($tokens as $token) {
                $outer->where(function ($inner) use ($usable, $token) {
                    foreach ($usable as $i => $col) {
                        $method = $i === 0 ? 'where' : 'orWhere';
                        $inner->{$method}($col, 'like', '%' . $token . '%');
                    }
                });
            }
        });

        $orderColumn = in_array('updated_at', $columns)
            ? 'updated_at'
            : (in_array('published_at', $columns) ? 'published_at' : $usable[0]);

        return $query
            ->orderByDesc($orderColumn)
            ->limit(50)
            ->get()
            ->map($map);
    }

    /**
     * Manual index for static Blade routes so they appear in results.
     * Includes FAQ-style keywords with "check-out" variations and
     * venue / outlet names like “Parasol” and “Stratos”.
     */
    protected function staticPages(string $q)
    {
        $items = [];

        $pages = [
            [
                'title'    => 'Home',
                'route'    => 'home',
                'keywords' => 'home welcome astoria current boracay station 3 beachfront hotel resort',
            ],
            [
                'title'    => 'About',
                'route'    => 'about',
                'keywords' => 'about astoria current story resort profile boracay station 3',
            ],
            [
                'title'    => 'Amenities',
                'route'    => 'amenities',
                'keywords' => 'amenities pools spa gym facilities cabanas daybeds beach loungers activities',
            ],
            [
                'title'    => 'Explore',
                'route'    => 'explore',
                'keywords' => 'explore island tour activities nearby attractions boracay white beach',
            ],
            [
                'title'    => 'Travel Guide',
                'route'    => 'travel',
                'keywords' => 'how to get here travel guide transport directions airport jetty boat',
            ],
            [
                'title'    => 'CSR',
                'route'    => 'csr',
                'keywords' => 'csr corporate social responsibility sustainability eco friendly initiatives',
            ],

            // FAQs — keep check-in / check-out variations
            [
                'title'    => 'FAQs',
                'route'    => 'faqs',
                'keywords' => 'faq frequently asked questions help support booking policy check in check-in check out check-out checkout early check-in late check-out cancellation payment',
            ],

            [
                'title'    => 'Blogs',
                'route'    => 'blogs',
                'keywords' => 'news blog articles updates stories tips',
            ],
            [
                'title'    => 'Promos',
                'route'    => 'promos',
                'keywords' => 'promos offers deals discounts packages',
            ],
            [
                'title'    => 'Accommodations',
                'route'    => 'accommodations',
                'keywords' => 'rooms suites accommodations deluxe superior premier standard',
            ],
            [
                'title'    => 'The Nest',
                'route'    => 'nest',
                'keywords' => 'the nest room category nest rooms',
            ],
            [
                'title'    => 'The Alcove',
                'route'    => 'alcove',
                'keywords' => 'the alcove room category alcove rooms',
            ],
            [
                'title'    => 'Villas',
                'route'    => 'villa',
                'keywords' => 'villa villas private villa suite',
            ],
            [
                'title'    => 'Premier',
                'route'    => 'premier',
                'keywords' => 'premier room category premier room suite',
            ],

            // Dining – explicitly includes Parasol / Aqua Breeze
            [
                'title'    => 'Dining',
                'url'      => url('/food-beverages'),
                'keywords' => 'dining restaurant food beverage f&b buffet breakfast lunch dinner poolside bar cafe grill parasol parasol bar parasol restaurant parasol deck aqua breeze cafe aqua breeze',
            ],

            [
                'title'    => 'Waterpark',
                'url'      => url('/waterpark'),
                'keywords' => 'waterpark slides wave pool kids family fun',
            ],

            // Meetings & Events – explicitly includes Stratos / Parasol as venues
            [
                'title'    => 'Meetings & Events',
                'url'      => url('/meetings-events'),
                'keywords' => 'meetings events venues corporate social function ballroom conference wedding reception stratos stratos ballroom stratos function room parasol deck parasol venue parasol bar',
            ],

            [
                'title'    => 'Contact Us',
                'route'    => 'contact',
                'keywords' => 'contact address phone email location map inquiry',
            ],
        ];

        $needle = Str::lower(Str::squish($q));

        foreach ($pages as $p) {
            $url = isset($p['route']) && RouteFacade::has($p['route'])
                ? route($p['route'])
                : ($p['url'] ?? null);

            if (!$url) {
                continue;
            }

            $hay = Str::lower(
                Str::squish(($p['title'] ?? '') . ' ' . ($p['keywords'] ?? ''))
            );

            $matched = false;

            // Direct phrase match
            if ($needle !== '' && Str::contains($hay, $needle)) {
                $matched = true;
            } else {
                // Split query on spaces AND hyphens for partial matches
                $tokens = array_filter(preg_split('/[\s\-]+/u', $needle));
                foreach ($tokens as $tk) {
                    if ($tk !== '' && Str::contains($hay, $tk)) {
                        $matched = true;
                        break;
                    }
                }
            }

            if (!$matched) {
                continue;
            }

            $items[] = [
                'type'         => 'page',
                'title'        => $p['title'],
                'url'          => $url,
                'excerpt'      => Str::title($p['keywords']),
                'image'        => null,
                'category'     => 'Page',
                'published_at' => now(),
                'score'        => 65,
            ];
        }

        return collect($items);
    }
}
