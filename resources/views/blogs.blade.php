{{-- resources/views/blogs.blade.php --}}
@extends('layouts.app')

@section('title', 'Blogs | Astoria Bohol')

@section('content')
@php
  // Ensure categories are always available for the filter (fallback if controller didn’t pass)
  $categories = ($categories ?? null) ?: \App\Models\ArticleCategory::query()
      ->orderBy('name')
      ->get(['id','name','slug']);

  // Use active category from query or route param
  $activeCategory = request('category') ?: (request()->route('category') ?? '');

  // Ensure tags is a collection (optional feature)
  $tags = ($tags ?? collect());
@endphp

<main x-data="blogIndex()" class="bg-white">
{{-- =========================
   BLOG HERO — Big height, black overlay, left-aligned
   + Entrance animation with delays on all text
   + Desktop-only: H1 spans full content width (no max width clamp)
========================= --}}
@php
  $bg = $bg ?? asset('images/blog-header.webp'); // fallback image
@endphp

<section id="blog-hero" class="relative isolate w-full  lg:min-h-[950px]">
  <!-- Background image -->
  <picture class="absolute inset-0 -z-10">
    <source srcset="{{ $bg }}" media="(min-width: 1024px)">
    <img src="{{ $bg }}" alt="Astoria Bohol" class="h-full w-full object-cover">
  </picture>

  <!-- Black overlays -->
  <div class="absolute inset-0 -z-10 bg-black/25"></div>
  <div class="absolute inset-0 -z-10 bg-gradient-to-t from-black/10 via-black/5 to-black/25"></div>

  <!-- Animations -->
  <style>
    @keyframes heroFadeUp { from { opacity: 0; transform: translateY(14px) } to { opacity: 1; transform: none } }
    #blog-hero .fx {
      opacity: 0; transform: translateY(14px);
      animation: heroFadeUp .68s cubic-bezier(.22,1,.36,1) forwards;
      animation-delay: var(--d, 0ms);
      will-change: transform, opacity;
    }
    @media (prefers-reduced-motion: reduce) {
      #blog-hero .fx { animation: none !important; opacity: 1 !important; transform: none !important; }
    }
  </style>

  <!-- Content -->
  <div class="mx-auto max-w-7xl px-6">
    <div class="min-h-[50vh] sm:min-h-[60vh] lg:min-h-[92vh] pt-28 sm:pt-32 pb-12 flex items-center">
      <div class="w-full text-left">
        <!-- Desktop-only: H1 is full width (no clamp); other texts remain comfortably readable -->
        <h1 class="fx text-white text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-semibold leading-tight tracking-tight w-full lg:max-w-none"
            style="--d:120ms">
          Astoria Bohol Blogs
        </h1>

        <div class="mt-4 flex items-center gap-4 max-w-3xl">
          <h2 class="fx text-white text-2xl sm:text-3xl md:text-4xl font-semibold tracking-tight" style="--d:220ms">
            Stories &amp; Updates
          </h2>
          <span class="fx sm:inline-block h-1 w-16 rounded-full bg-[#E6E7E8]" style="--d:260ms"></span>
        </div>

        <p class="fx mt-5 text-white/90 text-base sm:text-lg md:text-xl max-w-2xl" style="--d:320ms">
          News, tips, and inspiration curated for our guests.
        </p>
      </div>
    </div>
  </div>
</section>




  <!-- ========== FILTERS ========== -->
  <section class="sticky top-0 pt-10 z-20 bg-white/85 backdrop-blur border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-6 py-4">
      <form method="GET" action="{{ url()->current() }}" class="grid gap-3 md:grid-cols-12 md:items-center">
        {{-- SEARCH --}}
        <div class="md:col-span-6">
          <label for="q" class="sr-only">Search</label>
          <div class="relative">
            <input
              id="q"
              name="q"
              type="search"
              value="{{ request('q') }}"
              placeholder="Search articles, e.g. “Boracay dining”…"
              class="w-full rounded-xl border-gray-200 focus:border-[#CF4520] focus:ring-[#CF4520] px-4 py-2.5 pr-10"
            >
            <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 p-2 rounded-lg hover:bg-[#F05323]/5" aria-label="Search">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 opacity-70" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="m21 21-4.35-4.35M11 19a8 8 0 1 1 0-16 8 8 0 0 1 0 16Z"/>
              </svg>
            </button>
          </div>
        </div>

        {{-- CATEGORY --}}
        <div class="md:col-span-3">
          <label for="category" class="sr-only">Category</label>
          <select id="category" name="category"
                  onchange="this.form.requestSubmit()"
                  class="w-full rounded-xl border-gray-200 focus:border-[#CF4520] focus:ring-[#CF4520] px-3 py-2.5">
            <option value="">All categories</option>
            @foreach($categories as $cat)
              <option value="{{ $cat->slug }}" @selected($activeCategory === $cat->slug)>{{ $cat->name }}</option>
            @endforeach
          </select>
        </div>

        {{-- SORT --}}
        <div class="md:col-span-2">
          <label for="sort" class="sr-only">Sort</label>
          @php $sort = request('sort', 'latest'); @endphp
          <select id="sort" name="sort"
                  onchange="this.form.requestSubmit()"
                  class="w-full rounded-xl border-gray-200 focus:border-[#CF4520] focus:ring-[#CF4520] px-3 py-2.5">
            <option value="latest"  @selected($sort==='latest')>Latest</option>
            <option value="oldest"  @selected($sort==='oldest')>Oldest</option>
            <option value="popular" @selected($sort==='popular')>Most Popular</option>
          </select>
        </div>

        {{-- ACTIONS --}}
        <div class="md:col-span-1 flex md:justify-end gap-2">
          <button type="submit"
                  class="inline-flex items-center justify-center rounded-xl bg-[#CF4520] text-white px-4 py-2.5 font-semibold transition shadow-sm hover:shadow-md hover:bg-[#3F2021]">
            Apply
          </button>
          @if(request()->hasAny(['q','category','tag','sort']))
            <a href="{{ url()->current() }}"
               class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-4 py-2.5 font-semibold hover:bg-[#F05323]/5">
              Reset
            </a>
          @endif
        </div>

        {{-- TAGS (optional) --}}
        @if($tags->count())
          <div class="md:col-span-12">
            <div class="flex flex-wrap gap-2 pt-1">
              @foreach($tags as $tag)
                @php
                  $slugged = \Illuminate\Support\Str::slug($tag);
                  $q = array_merge(request()->query(), ['tag' => $slugged, 'page' => 1]);
                  $active = request('tag') === $slugged;
                @endphp
                <a href="{{ url()->current() . '?' . http_build_query($q) }}"
                   class="px-3 py-1.5 rounded-full text-sm border transition
                          {{ $active
                              ? 'bg-[#CF4520]/10 text-[#CF4520] border-[#CF4520]/30'
                              : 'text-gray-700 border-gray-200 hover:border-[#CF4520]/30 hover:text-[#CF4520]' }}">
                  #{{ $tag }}
                </a>
              @endforeach
            </div>
          </div>
        @endif
      </form>
    </div>
  </section>

  <!-- ========== GRID ========== -->
  <section class="py-10 md:py-14">
    <div class="max-w-7xl mx-auto px-6">

      {{-- Empty State --}}
      @if(($articles ?? collect())->count() === 0)
        <div class="rounded-2xl border border-gray-200 bg-white p-10 text-center">
          <h3 class="text-lg font-semibold">No articles found</h3>
          <p class="text-gray-600 mt-1">Try adjusting your filters or search term.</p>
          <a href="{{ url()->current() }}" class="mt-4 inline-flex items-center rounded-xl bg-[#CF4520] px-4 py-2.5 text-white font-semibold hover:shadow-md">
            Clear Filters
          </a>
        </div>
      @endif

      {{-- Cards --}}
      <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($articles as $i => $article)
          @php
            /** @var \App\Models\Article $article */
            $title   = $article->title ?? 'Untitled';
            $slug    = $article->slug ?? null;
            $excerpt = $article->excerpt ?? '';

            $rawImage = $article->featured_image ?? null;
            $img = $rawImage
                    ? (\Illuminate\Support\Str::startsWith($rawImage, ['http://','https://'])
                          ? $rawImage
                          : \Illuminate\Support\Facades\Storage::url($rawImage))
                    : asset('images/placeholder/post.jpg');

            $category = optional($article->category);
            $tagCsv   = $article->tags ?? '';
            $tagItems = collect(array_filter(array_map('trim', explode(',', $tagCsv))));

            // Effective publish time (UTC -> PH)
            $eff  = $article->effective_publish_at ?? $article->published_at ?? $article->created_at;
            $pubDT = $eff ? \Illuminate\Support\Carbon::parse($eff)->timezone('Asia/Manila') : null;

            if ($slug && $pubDT) {
              if (\Illuminate\Support\Facades\Route::has('articles.show')) {
                $href = route('articles.show', [
                  'year'  => $pubDT->format('Y'),
                  'month' => $pubDT->format('m'),
                  'day'   => $pubDT->format('d'),
                  'slug'  => $slug,
                ]);
              } else {
                $href = url(sprintf('/%s/%s/%s/%s', $pubDT->format('Y'), $pubDT->format('m'), $pubDT->format('d'), $slug));
              }
            } else {
              $href = '#';
            }
          @endphp

          <article
            x-data
            x-intersect.once="$el.classList.add('translate-y-0','opacity-100')"
            class="group relative bg-white border border-gray-200 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition
                   opacity-100 translate-y-2 will-change-transform">

            <a href="{{ $href }}" class="absolute inset-0 z-10" aria-label="Read more: {{ $title }}"></a>

            <div class="relative aspect-[16/10] overflow-hidden">
              <img
                src="{{ $img }}"
                alt="{{ $title }}"
                loading="lazy"
                class="w-full h-full object-cover transition-transform duration-700
                       group-hover:scale-[1.04] group-active:scale-[1.02]"
              />
              <div class="absolute inset-0 bg-gradient-to-t from-black/25 via-transparent to-transparent pointer-events-none"></div>
              <div class="absolute inset-0 ring-1 ring-inset ring-black/5 pointer-events-none"></div>

              {{-- Category badge --}}
              @if($category->exists)
                @php
                  $catSlug = $category->slug ?? '';
                  $q = array_merge(request()->query(), ['category' => $catSlug, 'page' => 1]);
                @endphp
                <a href="{{ url()->current() . '?' . http_build_query($q) }}"
                   class="absolute top-3 left-3 text-[11px] font-semibold px-2.5 py-1 rounded-full
                          bg-white/90 backdrop-blur border border-white shadow
                          hover:bg-[#F05323] hover:text-white transition"
                   aria-label="Filter by {{ $category->name }}">
                  {{ $category->name }}
                </a>
              @endif
            </div>

            <div class="p-5 flex flex-col gap-3">
              <h3 class="text-[17px] md:text-lg font-semibold leading-snug line-clamp-2">
                {{ $title }}
              </h3>

              <p class="text-sm font-medium text-gray-700 line-clamp-3">
                {!! \Illuminate\Support\Str::of($excerpt)->limit(180) !!}
              </p>

              <div class="mt-1 flex flex-wrap gap-2">
                @foreach($tagItems as $t)
                  @php
                    $slugged = \Illuminate\Support\Str::slug($t);
                    $q = array_merge(request()->query(), ['tag' => $slugged, 'page' => 1]);
                  @endphp
                  <a href="{{ url()->current() . '?' . http_build_query($q) }}"
                     class="text-xs px-2 py-1 rounded-full border transition
                            bg-gray-50 text-gray-700 border-gray-200
                            hover:border-[#CF4520]/40 hover:text-[#CF4520]">
                    #{{ $t }}
                  </a>
                @endforeach
              </div>

              <div class="mt-2 flex items-center justify-between text-xs text-gray-500">
                <div class="flex items-center gap-2">
                  @if($pubDT)
                    <time datetime="{{ $pubDT->toDateString() }}">
                      {{ $pubDT->format('M j, Y') }}
                    </time>
                  @endif
                  <span aria-hidden="true">•</span>
                  <span class="opacity-80">Read more</span>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 opacity-60 group-hover:translate-x-0.5 transition" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7"/>
                </svg>
              </div>
            </div>
          </article>
        @endforeach
      </div>

      {{-- Pagination --}}
      @if(method_exists($articles, 'links'))
        <div class="mt-10">
          {{ $articles->onEachSide(1)->links() }}
        </div>
      @endif
    </div>
  </section>
</main>

{{-- Alpine Component (placeholder) --}}
<script>
  document.addEventListener('alpine:init', () => {
    Alpine.data('blogIndex', () => ({}))
  })
</script>



{{-- ===== Full-width decorative vector ===== --}}
<section id="footer-vector" class="relative w-full overflow-hidden pt-5" aria-hidden="true">
  <img
    src="{{ asset('images/footer-vector.webp') }}"
    alt=""
    class="block w-full h-auto select-none pointer-events-none"
    loading="lazy"
    decoding="async"
  >
</section>
@endsection
