{{-- resources/views/search.blade.php --}}
@extends('layouts.app')

@section('title', 'Astoria Current Search' . (trim(request('q','')) ? ' — ' . e(request('q')) : ''))

@push('head')
  <style>
    /* Tight highlight — no extra space around the match */
    mark.search-hit{
      background:#ffebdb; color:#25282a;
      padding:0; border-radius:.15rem;
      -webkit-box-decoration-break: clone; box-decoration-break: clone;
    }

    /* Type dots */
    .type-dot{ width:.65rem; height:.65rem; border-radius:9999px; display:inline-block; }
    .dot-blog { background:#305063; }
    .dot-promo{ background:#105702; }
    .dot-room { background:#e95427; }
    .dot-page { background:#25282a; }

    /* Cards */
    .result-card{
      border:1px solid rgba(0,0,0,.08);
      border-radius:1rem; background:#fff;
      box-shadow:0 1px 2px rgba(0,0,0,.04);
      transition: box-shadow .2s ease, border-color .2s ease, transform .06s ease;
    }
    .result-card:hover{ box-shadow:0 8px 24px -8px rgba(0,0,0,.15); border-color:rgba(0,0,0,.12); }
    .result-card:active{ transform: translateY(1px); }

    /* Chips */
    .chip{
      display:inline-flex; align-items:center; gap:.4rem;
      font-weight:700; font-size:.68rem; letter-spacing:.02em;
      padding:.35rem .55rem; border-radius:.625rem;
      border:1px solid rgba(0,0,0,.08); background:#fafafa; color:#374151;
    }
    .chip-ghost{ background:#fff; }

    /* Widget tiles */
    .stat-tile, .widget{
      border:1px solid rgba(0,0,0,.08);
      border-radius:.85rem; background:#fff;
      padding:1rem; box-shadow:0 1px 2px rgba(0,0,0,.04);
      transition: box-shadow .2s ease, border-color .2s ease;
    }
    .stat-tile:hover, .widget:hover{ box-shadow:0 8px 24px -10px rgba(0,0,0,.12); border-color:rgba(0,0,0,.12); }

    .sr-only{ position:absolute; width:1px; height:1px; padding:0; margin:-1px; overflow:hidden; clip:rect(0,0,0,0); border:0; }
  </style>
@endpush

@section('content')
@php
  use Illuminate\Support\Str;
  use Illuminate\Support\Facades\Route;

  $q = trim($q ?? request('q',''));
  $results = $results ?? collect();

  $isPaginator = $results instanceof \Illuminate\Contracts\Pagination\Paginator;
  $coll = $isPaginator ? $results->getCollection() : collect($results);

  // Normalize items
  $items = $coll->map(function($r){
      $arr = is_array($r) ? $r : (method_exists($r,'toSearchArray') ? $r->toSearchArray() : (array)$r);
      $arr['type']    = $arr['type']    ?? ($arr['model_type'] ?? 'page');
      $arr['title']   = $arr['title']   ?? ($arr['name'] ?? 'Untitled');
      $arr['url']     = $arr['url']     ?? ($arr['link'] ?? '#');
      $arr['excerpt'] = $arr['excerpt'] ?? ($arr['summary'] ?? ($arr['body'] ?? ''));
      $arr['category']= $arr['category']?? ($arr['tag'] ?? null);
      return $arr;
  });

  // Labels + counts
  $labels = [
    'all'   => 'All',
    'blog'  => 'Blogs',
    'promo' => 'Promos',
    'room'  => 'Rooms',
    'page'  => 'Pages',
  ];
  $counts = [
    'all'   => $items->count(),
    'blog'  => $items->where('type','blog')->count(),
    'promo' => $items->where('type','promo')->count(),
    'room'  => $items->where('type','room')->count(),
    'page'  => $items->where('type','page')->count(),
  ];

  // Highlighter (keeps text continuous; no spacing added)
  $terms = array_filter(preg_split('/\s+/u', $q));
  $highlight = function($text) use ($terms){
    $safe = e(Str::limit(strip_tags($text ?? ''), 300));
    if(!$terms){ return $safe; }
    $pattern = '/(' . implode('|', array_map(fn($t)=>preg_quote($t,'/'), $terms)) . ')/iu';
    return preg_replace($pattern, '<mark class="search-hit">$1</mark>', $safe);
  };

  // Search action
  $searchAction = Route::has('search') ? route('search') : url('/search');
  $suggestion = $suggestion ?? null;

  // Helper for named route with fallback path
  $linkTo = function(string $name, string $fallbackPath){
      return Route::has($name) ? route($name) : url($fallbackPath);
  };

  // Sitelinks (right column widgets) – aligned to Astoria Current nav
   $sitelinks = [
    'Top links' => [
      ['Home',        $linkTo('home','/')],
      ['Contact Us',  $linkTo('contact','/contact-us')],
      ['Promos',      $linkTo('promos','/promos')],
      ['Blogs',       $linkTo('blogs','/blogs')],
    ],
    'Accommodations' => [
      ['Accommodations',   $linkTo('accommodations','/accommodations')],
      ['Deluxe Room',      $linkTo('deluxe','/accommodations/deluxe-room')],
      ['Superior Deluxe',  $linkTo('superiordeluxe','/accommodations/superior-deluxe')],
      ['Superior Room',    $linkTo('superior','/accommodations/superior')],
      ['Standard Room',    $linkTo('standard','/accommodations/standard')],
      ['Premier',          $linkTo('premier','/accommodations/premier')],
    ],

  ];
@endphp

<section class="bg-white text-[#25282a]">
  <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 pt-24 md:pt-28 pb-24">
    <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight">Search</h1>

    {{-- Search bar --}}
    <form action="{{ $searchAction }}" method="GET" class="mt-4 relative mb-6">
      <label class="sr-only" for="q">Search the site</label>
      <input
        id="q" name="q" type="search" value="{{ $q }}"
        placeholder="Search rooms, promos, blogs…"
        class="w-full rounded-2xl border border-gray-300/80 bg-white px-5 py-3 pr-16 text-base shadow-sm
               focus:outline-none focus:ring-2 focus:ring-[#04b2e2] focus:border-[#04b2e2]" />
      <button type="submit"
              class="absolute right-2 top-1/2 -translate-y-1/2 px-3 py-2 rounded-xl
                     bg-[#04b2e2] text-white font-semibold hover:brightness-110">
        Search
      </button>
    </form>

    {{-- Two-column layout: 75% results / 25% sidebar --}}
    <div class="grid grid-cols-12 gap-8 mb-12">
      {{-- LEFT: Results + filters --}}
      <div class="col-span-12 lg:col-span-9">
        {{-- Meta row --}}
        <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
          @if($q !== '')
            <p class="text-sm md:text-base">
              Showing <span class="font-bold">{{ $counts['all'] }}</span> result{{ $counts['all']===1?'':'s' }}
              for “<span class="font-semibold">{{ e($q) }}</span>”.
            </p>
          @endif

          @if($suggestion && Str::lower($suggestion) !== Str::lower($q))
            <a href="{{ $searchAction }}?q={{ urlencode($suggestion) }}"
               class="text-sm md:text-base underline decoration-[#04b2e2]/50 underline-offset-4 hover:decoration-[#04b2e2]">
              Did you mean: <span class="font-semibold">{{ e($suggestion) }}</span>?
            </a>
          @endif
        </div>

        {{-- Stats widget --}}
        <div class="mt-5 grid grid-cols-2 sm:grid-cols-4 gap-3">
          <div class="stat-tile flex items-center justify-between">
            <div class="flex items-center gap-2"><span class="type-dot dot-blog"></span><span class="text-sm font-semibold">Blogs</span></div>
            <span class="text-xl font-extrabold">{{ $counts['blog'] }}</span>
          </div>
          <div class="stat-tile flex items-center justify-between">
            <div class="flex items-center gap-2"><span class="type-dot dot-promo"></span><span class="text-sm font-semibold">Promos</span></div>
            <span class="text-xl font-extrabold">{{ $counts['promo'] }}</span>
          </div>
          <div class="stat-tile flex items-center justify-between">
            <div class="flex items-center gap-2"><span class="type-dot dot-room"></span><span class="text-sm font-semibold">Rooms</span></div>
            <span class="text-xl font-extrabold">{{ $counts['room'] }}</span>
          </div>
          <div class="stat-tile flex items-center justify-between">
            <div class="flex items-center gap-2"><span class="type-dot dot-page"></span><span class="text-sm font-semibold">Pages</span></div>
            <span class="text-xl font-extrabold">{{ $counts['page'] }}</span>
          </div>
        </div>

        {{-- Filters + results --}}
        <div
          x-data="{
            active: 'all',
            setTab(t){ this.active = t; $nextTick(()=>document.getElementById('results-top')?.scrollIntoView({behavior:'smooth'})); }
          }"
          class="mt-6"
        >
          {{-- Filter chips --}}
          <div class="flex flex-wrap gap-2">
            @foreach($labels as $key => $label)
              @php $count = $counts[$key] ?? 0; @endphp
              <button
                type="button"
                @click="setTab('{{ $key }}')"
                :class="active === '{{ $key }}'
                  ? 'bg-[#04b2e2] text-white'
                  : 'bg-white text-[#25282a] border border-gray-300 hover:bg-gray-50'"
                class="px-3 py-1.5 rounded-full text-sm font-semibold transition"
                {{ $count === 0 ? 'disabled' : '' }}
                :disabled="{{ $count === 0 ? 'true' : 'false' }}"
                aria-pressed="false"
              >
                {{ $label }}
                <span class="ml-1 inline-flex items-center justify-center text-xs font-bold px-1.5 py-0.5 rounded-full
                             bg-black/5 text-[#25282a] align-middle">{{ $count }}</span>
              </button>
            @endforeach
          </div>

          {{-- Tips --}}
          <details class="mt-4 rounded-xl border border-dashed border-gray-300 p-4 bg-white/60">
            <summary class="cursor-pointer text-sm font-bold">Search tips</summary>
            <div class="mt-2 text-sm text-gray-700 leading-relaxed">
              Try specific room names (e.g., <em>Deluxe Room</em>, <em>Superior Room</em>), topics (e.g., <em>F&amp;B</em> or <em>MICE</em>),
              or sections like <em>Amenities</em> or <em>Meetings &amp; Events</em>.
              You can also search property pages such as <em>F&amp;B</em>, <em>Amenities</em>, or <em>Contact Us</em>.
            </div>
          </details>

          {{-- Results list (no thumbnails; tight highlights) --}}
          <div id="results-top" class="mt-6 grid grid-cols-1 gap-4">
            @forelse($items as $it)
              @php
                $type = Str::lower((string)($it['type'] ?? 'page'));
                $title = $it['title'] ?? 'Untitled';
                $url   = $it['url']   ?? '#';
                $excerpt = $it['excerpt'] ?? '';
                $dotClass = match($type){
                  'blog'  => 'dot-blog',
                  'promo' => 'dot-promo',
                  'room'  => 'dot-room',
                  default => 'dot-page',
                };
                $typeLabel = match($type){
                  'blog'  => 'Blog',
                  'promo' => 'Promo',
                  'room'  => 'Room',
                  default => 'Page',
                };
              @endphp

              <article
                x-show="active === 'all' || active === '{{ $type }}'"
                x-cloak
                class="result-card p-4 md:p-5"
              >
                <header class="flex items-start justify-between gap-3">
                  <div class="min-w-0">
                    <div class="flex items-center gap-2">
                      <span class="type-dot {{ $dotClass }}"></span>
                      <span class="text-[11px] font-extrabold uppercase tracking-wider opacity-70">{{ $typeLabel }}</span>
                    </div>
                    <h3 class="mt-1 text-lg md:text-xl font-extrabold leading-snug break-words">
                      <a href="{{ $url }}" class="hover:underline decoration-[#04b2e2]/40 underline-offset-4">
                        {!! $highlight($title) !!}
                      </a>
                    </h3>
                  </div>

                  <div class="shrink-0 flex items-center gap-2">
                    @if(!empty($it['category']))
                      <span class="chip" title="Category">{{ e($it['category']) }}</span>
                    @endif
                  </div>
                </header>

                @if(!empty($excerpt))
                  <p class="mt-3 text-sm md:text-[15px] text-gray-700 leading-relaxed">
                    {!! $highlight($excerpt) !!}
                  </p>
                @endif

                <footer class="mt-4 flex items-center justify-between gap-3">
                  <a href="{{ $url }}"
                     class="inline-flex items-center gap-2 text-sm font-semibold text-[#04b2e2] hover:brightness-110">
                    Read more
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                  </a>
                </footer>
              </article>
            @empty
              {{-- Empty state --}}
              <div class="col-span-full">
                <div class="rounded-2xl border border-dashed border-gray-300 p-10 text-center">
                  <h3 class="text-lg font-bold">No results found</h3>
                  @if($q !== '')
                    <p class="mt-2 text-gray-600">Try different keywords or check your spelling.</p>
                  @else
                    <p class="mt-2 text-gray-600">Start by typing something above.</p>
                  @endif
                  <div class="mt-4 flex flex-wrap justify-center gap-2">
                    @php
                      $popular = [
                        'About Us'     => ['type' => 'link', 'href' => route('about')],
                        'FAQs'   => ['type' => 'link', 'href' => route('faqs')],
                        'Accommodations'         => ['type' => 'link', 'href' => route('accommodations')],
                        'Amenities'         => ['type' => 'link', 'href' => route('amenities')],
                        'F&B'             => ['type' => 'link', 'href' => route('dining')],
                        'MICE'            => ['type' => 'link', 'href' => route('meetings')],
                        'Astoria Current' => [
                          'type' => 'link',
                          // use route('home') since you already have:
                          // Route::view('/', 'home')->name('home');
                          'href' => route('home'),
                          
                        ],
                      ];
                    @endphp

                    @foreach($popular as $label => $meta)
                      @php
                        $href = $meta['type'] === 'link'
                          ? $meta['href']
                          : $searchAction.'?q='.urlencode($label);
                      @endphp

                      <a href="{{ $href }}"
                        class="px-3 py-1.5 rounded-full text-sm font-semibold bg-white border border-gray-300 hover:bg-gray-50">
                        {{ $label }}
                      </a>
                    @endforeach

                  </div>
                </div>
              </div>
            @endforelse
          </div>

          {{-- Pagination --}}
          @if($isPaginator && $results->hasPages())
            <div class="mt-8">
              {{ $results->appends(['q' => $q])->links() }}
            </div>
          @endif
        </div>
      </div>

      {{-- RIGHT: Sitelinks --}}
      <aside class="col-span-12 lg:col-span-3">
        <div class="lg:sticky lg:top-28 space-y-4">
          @foreach($sitelinks as $group => $links)
            <nav class="widget">
              <h3 class="text-sm font-extrabold uppercase tracking-wider mb-3 opacity-70">{{ $group }}</h3>
              <ul class="space-y-1.5">
                @foreach($links as [$label, $href])
                  <li>
                    <a href="{{ $href }}" class="group flex items-center justify-between gap-3 px-2 py-2 rounded-md hover:bg-gray-50">
                      <span class="text-sm font-semibold">{{ $label }}</span>
                      <svg class="w-4 h-4 opacity-60 group-hover:opacity-90 transition" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                      </svg>
                    </a>
                  </li>
                @endforeach
              </ul>
            </nav>
          @endforeach
        </div>
      </aside>
    </div>
  </div>
</section>
@endsection
