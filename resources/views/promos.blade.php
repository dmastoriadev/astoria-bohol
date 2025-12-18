@extends('layouts.app')
@section('title', 'Promos | Astoria Bohol')

@section('content')
@php
  use Illuminate\Support\Str;
  use Illuminate\Support\Carbon;

  $tz = 'Asia/Manila';

  // Normalize $promos into a Collection
  $promosInput = $promos ?? null;
  if ($promosInput instanceof \Illuminate\Contracts\Pagination\Paginator ||
      $promosInput instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator) {
    $raw = $promosInput->getCollection();
  } else {
    $raw = collect($promosInput ?? []);
  }

  // Helper: convert to Asia/Manila (assume DB stored in UTC)
  $toTz = function ($val) use ($tz) {
    if ($val instanceof \Carbon\CarbonInterface) return $val->copy()->timezone($tz);
    if (!blank($val)) {
      try { return Carbon::parse($val, 'UTC')->timezone($tz); } catch (\Throwable $e) {}
      try { return Carbon::parse($val, $tz); } catch (\Throwable $e) {}
    }
    return null;
  };

  $now = Carbon::now($tz);

  // Build view-model (prefers scheduled_publish_date for display)
  $items = $raw->map(function ($p) use ($toTz, $now) {
    $title   = $p['title'] ?? ($p->title ?? 'Untitled Promo');
    $slug    = $p['slug'] ?? ($p->slug ?? Str::slug($title));
    $excerpt = $p['excerpt'] ?? ($p->excerpt ?? '');
    $imgRaw  = $p['featured_image'] ?? ($p->featured_image ?? null);

    $img = $imgRaw
      ? (Str::startsWith($imgRaw, ['http://','https://']) ? $imgRaw : \Illuminate\Support\Facades\Storage::url($imgRaw))
      : asset('images/placeholder/post-wide.jpg');

    // Validity window
    $start = isset($p['starts_at']) || isset($p->starts_at) ? $toTz($p['starts_at'] ?? $p->starts_at) : null;
    $end   = isset($p['ends_at'])   || isset($p->ends_at)   ? $toTz($p['ends_at']   ?? $p->ends_at)   : null;
    $end   = $end ?: (isset($p['expires_at']) || isset($p->expires_at) ? $toTz($p['expires_at'] ?? $p->expires_at) : null);

    // Published dates
    $published = isset($p['published_at']) || isset($p->published_at) ? $toTz($p['published_at'] ?? $p->published_at) : null;
    $created   = isset($p['created_at'])   || isset($p->created_at)   ? $toTz($p['created_at']   ?? $p->created_at)   : null;

    // Prefer scheduled publish for display
    $scheduled = isset($p['scheduled_publish_date']) || isset($p->scheduled_publish_date)
      ? $toTz($p['scheduled_publish_date'] ?? $p->scheduled_publish_date)
      : ($start ?: null);

    $effectivePublished = $scheduled ?: ($published ?: $created);

    // Sorting base
    $sortBase = $effectivePublished ?: ($start ?: ($end ?: $now));
    $sortTs   = $sortBase ? $sortBase->getTimestamp() : 0;

    $status = 'active';
    if ($start && $now->lt($start)) $status = 'upcoming';
    if ($end   && $now->gt($end))   $status = 'expired';

    $link = \Illuminate\Support\Facades\Route::has('promos.show')
      ? route('promos.show', ['promo' => $slug])
      : url('/promos#'.$slug);

    return (object)[
      'title'               => $title,
      'slug'                => $slug,
      'excerpt'             => $excerpt,
      'img'                 => $img,
      'start'               => $start,
      'end'                 => $end,
      'status'              => $status,
      'link'                => $link,
      'sort_ts'             => $sortTs,
      'effective_published' => $effectivePublished,
    ];
  });

  // Default sort newest -> oldest
  $items = $items->sortByDesc('sort_ts')->values();
  $itemsCount = $items->count();
@endphp

<style>
  [x-cloak]{display:none!important}
  .masonry{ column-gap: 1.5rem; }
  .masonry-item{ break-inside: avoid; -webkit-column-break-inside: avoid; margin-bottom: 1.5rem; }
</style>

<main x-data="promosUI()" x-init="init()" class="bg-white">

{{-- =========================
   PROMOS HERO — mirrors BLOG hero
   Big height, black overlay, left-aligned
   Entrance animations with delays
   Desktop: H1 spans full width (no clamp)
========================= --}}
@php
  $bg = $bg ?? asset('images/promos-header.webp'); // fallback background image
@endphp

<section id="promo-hero" class="relative isolate w-full lg:min-h-[950px]">
  <!-- Background image -->
  <picture class="absolute inset-0 -z-10">
    <source srcset="{{ $bg }}" media="(min-width: 1024px)">
    <img src="{{ $bg }}" alt="Promos" class="h-full w-full object-cover">
  </picture>

  <!-- Black overlays -->
  <div class="absolute inset-0 -z-10 bg-black/25"></div>
  <div class="absolute inset-0 -z-10 bg-gradient-to-t from-black/10 via-black/5 to-black/25"></div>

  <!-- Animations -->
  <style>
    @keyframes heroFadeUp { from { opacity: 0; transform: translateY(14px) } to { opacity: 1; transform: none } }
    #promo-hero .fx {
      opacity: 0; transform: translateY(14px);
      animation: heroFadeUp .68s cubic-bezier(.22,1,.36,1) forwards;
      animation-delay: var(--d, 0ms);
      will-change: transform, opacity;
    }
    @media (prefers-reduced-motion: reduce) {
      #promo-hero .fx { animation: none !important; opacity: 1 !important; transform: none !important; }
    }
  </style>

  <!-- Content -->
  <div class="mx-auto max-w-7xl px-6">
    <div class="min-h-[50vh] sm:min_h-[86vh] lg:min-h-[92vh] pt-28 sm:pt-32 pb-12 flex items-center">
      <div class="w-full text-left">
        <h1 class="fx text-white text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-semibold leading-tight tracking-tight w-full lg:max-w-none"
            style="--d:120ms">
          SPECIAL OFFERS
        </h1>

        <div class="mt-4 flex items-center gap-4 max-w-3xl">
          <h2 class="fx text-white text-2xl sm:text-3xl md:text-4xl font-semibold tracking-tight" style="--d:220ms">
            EXCLUSIVE DINING AND VACATION EXPERIENCES


  </h2>
        </div>

        <p class="fx mt-5 text-white/90 text-base font-medium sm:text-lg md:text-xl max-w-2xl" style="--d:320ms">
        The best offerings to make the most of your vacation at Astoria Bohol!


        </p>
      </div>
    </div>
  </div>
</section>




{{-- ========== FILTERS (match blogs design, now search + sort only) ========== --}}
<section class="sticky top-0 pt-10 z-20 bg-white/85 backdrop-blur border-b border-gray-100">
  <div class="max-w-7xl mx-auto px-6 py-4">
    <form class="grid gap-3 md:grid-cols-12 md:items-center" @submit.prevent="apply()">
      {{-- SEARCH --}}
      <div class="md:col-span-7">
        <label for="q" class="sr-only">Search</label>
        <div class="relative">
          <input
            id="q"
            x-model="q"
            type="search"
            placeholder="Search offers, e.g. “discount”…"
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

      {{-- SORT --}}
      <div class="md:col-span-3">
        <label for="sort" class="sr-only">Sort</label>
        <select id="sort" x-model="sort"
                @change="apply()"
                class="w-full rounded-xl border-gray-200 focus:border-[#CF4520] focus:ring-[#CF4520] px-3 py-2.5">
          <option value="new">Latest</option>
          <option value="old">Oldest</option>
        </select>
      </div>

      {{-- ACTIONS --}}
      <div class="md:col-span-2 flex md:justify-end gap-2">
        <button type="submit"
                class="inline-flex items-center justify-center rounded-xl bg-[#CF4520] text-white px-4 py-2.5 font-semibold transition shadow-sm hover:shadow-md hover:bg-[#3F2021]">
          Apply
        </button>
        <button type="button" @click="resetAll()"
                class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-4 py-2.5 font-semibold hover:bg-[#F05323]/5">
          Reset
        </button>
      </div>
    </form>
  </div>
</section>

{{-- ========== GRID ========== --}}
<section class="py-10 md:py-14">
  <div class="max-w-7xl mx-auto px-6">

    {{-- Cards (masonry) --}}
    <div id="promoGridOpt1" class="mt-8 masonry columns-1 sm:columns-2 lg:columns-3" x-show="view==='1'">
      @forelse($items as $p)
        @php
          $startLbl = $p->start ? $p->start->isoFormat('MMM D, YYYY') : null;
          $endLbl   = $p->end   ? $p->end->isoFormat('MMM D, YYYY')   : null;
          $rangeLbl = $startLbl && $endLbl ? "{$startLbl} – {$endLbl}" : ($endLbl ? "Until {$endLbl}" : ($startLbl ? 'From '.$startLbl : ''));
          $pubLbl   = $p->effective_published ? $p->effective_published->isoFormat('MMM D, YYYY') : null;
        @endphp
        <article
          id="{{ $p->slug }}"
          class="masonry-item opacity-100 translate-y-0 transition"
          data-title="{{ Str::lower($p->title) }}"
          data-ts="{{ $p->sort_ts }}"
        >
          <div class="group relative bg-white border border-gray-200 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition">
            <a href="{{ $p->link }}" class="absolute inset-0 z-10" aria-label="View: {{ $p->title }}"></a>

            <div class="relative">
              <img src="{{ $p->img }}" alt="{{ $p->title }}"
                   onerror="this.src='{{ asset('images/placeholder/post-wide.jpg') }}'"
                   class="w-full h-auto object-contain">
              <div class="absolute inset-0 bg-gradient-to-t from-black/25 via-transparent to-transparent pointer-events-none"></div>
              <div class="absolute inset-0 ring-1 ring-inset ring-black/5 pointer-events-none"></div>
              {{-- Category capsule removed --}}
            </div>

            <div class="px-4 py-3">
              <div class="flex items-center justify-between font-medium text-[12px] text-gray-600">
                <span class="inline-flex items-center gap-1">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" class="opacity-70">
                    <path d="M7 2v3M17 2v3M4 11h16M6 6h12a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2Z"
                          stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                  </svg>
                  <span class="font-medium">Published:</span> <time>{{ $pubLbl ?? '—' }}</time>
                </span>

                @if($rangeLbl)
                  <span class="px-2 py-0.5 rounded-full border bg-gray-50 text-gray-700">
                    {{ $rangeLbl }}
                  </span>
                @endif
              </div>

              <h3 class="mt-2 text-[17px] md:text-lg font-extrabold leading-snug">
                {{ $p->title }}
              </h3>

              @if(!blank($p->excerpt))
                <p class="mt-1 text-sm text-gray-600">
                  {!! \Illuminate\Support\Str::of($p->excerpt)->limit(180) !!}
                </p>
              @endif

              <div class="mt-3">
                <a href="{{ $p->link }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-[#CF4520] px-3.5 py-2 text-sm font-semibold text-white hover:bg-[#3F2021] transition">
                  Learn More
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M13.172 11H4a1 1 0 1 0 0 2h9.172l-3.586 3.586a1 1 0 1 0 1.414 1.414l5.657-5.657a1 1 0 0 0 0-1.414l-5.657-5.657a1 1 0 1 0-1.414 1.414L13.172 11z"/>
                  </svg>
                </a>
              </div>
            </div>
          </div>
        </article>
      @empty
        {{-- Single server-side empty state --}}
        <div class="w-full">
          <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-gray-300 bg-gray-50 py-16">
            <div class="text-4xl">🗂️</div>
            <h3 class="mt-3 text-lg font-semibold text-gray-900">No promotions yet</h3>
            <p class="mt-1 text-sm text-gray-600">New offers will appear here once published.</p>
          </div>
        </div>
      @endforelse
    </div>

    {{-- Client-side empty state (only when there are server items but filters hide all) --}}
    @if($itemsCount > 0)
      <div x-show="clientEmpty" x-cloak class="mt-8">
        <div class="rounded-2xl border border-gray-200 bg-white p-10 text-center">
          <h3 class="text-lg font-semibold">No offers found</h3>
          <p class="text-gray-600 mt-1">Try adjusting your keywords.</p>
          <button type="button"
                  @click="resetAll()"
                  class="mt-4 inline-flex items-center rounded-xl bg-[#CF4520] px-4 py-2.5 text-white font-semibold hover:shadow-md">
            Reset Filters
          </button>
        </div>
      </div>
    @endif

    {{-- Pagination if a paginator was passed --}}
    @php
      $isPaginator = isset($promos) && (
        $promos instanceof \Illuminate\Contracts\Pagination\Paginator ||
        $promos instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator
      );
    @endphp
    @if($isPaginator)
      <div class="mt-10">
        {{ $promos->withQueryString()->links() }}
      </div>
    @endif
  </div>
</section>

{{-- ========== CONTACT FORM (after promo listings) ========== --}}
<section id="promo-contact" class="bg-gray-50 py-12 md:py-16">
  <div class="max-w-4xl mx-auto px-6">
    <div class="text-center mb-6 md:mb-8">
      <h2 class="text-2xl md:text-3xl font-extrabold tracking-tight text-[#25282a]">
        Have questions about our promos?
      </h2>
      <p class="mt-2 text-sm md:text-base text-gray-700">
        Send us an inquiry and our Reservations team will reach out to you with more details about our current offers.
      </p>
    </div>

    <div class="rounded-2xl bg-white shadow ring-1 ring-gray-200 p-4 md:p-6">
      <div id="promo-contact-form" class="min-h-[320px]"></div>
      <noscript>
        <p class="mt-4 text-sm text-gray-600">
          JavaScript is required to load this form. Please contact us directly through our channels.
        </p>
      </noscript>
    </div>
  </div>
</section>

</main>

{{-- HubSpot contact form embed --}}
<script charset="utf-8" type="text/javascript" src="//js-na2.hsforms.net/forms/embed/v2.js"></script>
<script>
  hbspt.forms.create({
    portalId: "21911373",
    formId: "eed685a3-d8e9-49dd-a476-bee132f94a9d",
    region: "na2",
    target: "#promo-contact-form"
  });
</script>



{{-- Alpine helpers (client-side filter/sort, now search + sort only) --}}
<script>
  document.addEventListener('alpine:init', () => {
    Alpine.data('promosUI', () => ({
      q: '',
      sort: 'new',
      view: '1',
      clientEmpty: false, // track client-side "no results"
      init(){ this.apply(); },
      resetAll(){
        this.q=''; this.sort='new';
        this.apply();
      },
      apply(){
        const grid = document.getElementById('promoGridOpt1');
        if (!grid) return;

        // Sort by timestamp
        const nodes = Array.from(grid.children).filter(el => el.matches('article[data-ts]'));
        nodes.sort((a, b) => {
          const ta = parseInt(a.dataset.ts || '0', 10);
          const tb = parseInt(b.dataset.ts || '0', 10);
          return this.sort === 'new' ? (tb - ta) : (ta - tb);
        });
        nodes.forEach(n => grid.appendChild(n));

        // Filter + compute visible count (by title only)
        const q = this.q.trim().toLowerCase();
        const cards = grid.querySelectorAll('article[data-title]');
        let visibleCount = 0;
        cards.forEach(card => {
          const title = (card.dataset.title || '');
          const matchesQ = !q || title.includes(q);
          const show = matchesQ;
          card.style.display = show ? '' : 'none';
          if (show) visibleCount++;
        });

        this.clientEmpty = (visibleCount === 0);
      },
    }));
  });
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
