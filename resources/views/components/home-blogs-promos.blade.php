{{-- resources/views/components/home-blogs-promos.blade.php --}}
@props([
  'blogs'  => null,   // Collection of Article models
  'promos' => null,   // Collection of Promo models or arrays

  'blogsCount'  => 3,
  'promosCount' => 3,

  'title' => 'Latest Updates',
  'lead'  => 'Read the latest from our blog and browse current offers.',
])

@php
  use Illuminate\Support\Str;
  use Illuminate\Support\Carbon;

  $tz   = 'Asia/Manila';
  $now  = Carbon::now($tz);

  /* ===================== ALT helpers ===================== */
  $publicDisk = \Illuminate\Support\Facades\Storage::disk('public');
  $localDisk  = \Illuminate\Support\Facades\Storage::disk('local');

  $toPublicPath = function (?string $src): ?string {
    if (!$src) return null;
    $path = parse_url($src, PHP_URL_PATH) ?: $src;
    $path = ltrim($path, '/');
    $path = preg_replace('#^storage/#', '', $path);
    $path = preg_replace('#^public/#',  '', $path);
    return $path ?: null;
  };

  $altMap = (function() use ($localDisk) {
    $file = 'media/meta.json';
    if (! $localDisk->exists($file)) return [];
    $raw = json_decode($localDisk->get($file), true);
    return is_array($raw) ? $raw : [];
  })();

  $altFromMap = function (?string $src) use ($altMap, $toPublicPath): ?string {
    $pub = $toPublicPath((string) $src);
    if (!$pub) return null;
    $a = $altMap[$pub]['alt'] ?? null;
    return is_string($a) && $a !== '' ? $a : null;
  };

  $altFromSidecar = function (?string $src) use ($publicDisk, $toPublicPath): ?string {
    $pub = $toPublicPath((string) $src);
    if (!$pub) return null;
    $metaPath = $pub . '.meta.json';
    if (! $publicDisk->exists($metaPath)) return null;
    try {
      $raw = $publicDisk->get($metaPath);
      $arr = json_decode($raw, true, flags: JSON_THROW_ON_ERROR);
      return is_array($arr) && !empty($arr['alt']) ? (string) $arr['alt'] : null;
    } catch (\Throwable $e) { return null; }
  };

  $altFor = function (?string $src, ?string $fallback=null) use ($altFromMap, $altFromSidecar) {
    return $altFromMap($src) ?? $altFromSidecar($src) ?? ($fallback ?: null);
  };

  /* ===================== Promo masonry: thumbnail-driven aspect ratio ===================== */
  $gcd = function(int $a, int $b): int {
    $a = abs($a); $b = abs($b);
    if ($a === 0) return $b ?: 1;
    while ($b !== 0) { $t = $b; $b = $a % $b; $a = $t; }
    return $a ?: 1;
  };

  $aspectFor = function (?string $raw) use ($publicDisk, $toPublicPath, $gcd) {
    $fallback = '16 / 9';
    if (!$raw) return $fallback;
    if (Str::startsWith($raw, ['http://','https://'])) return $fallback;

    $pub = $toPublicPath($raw);
    if (!$pub) return $fallback;

    try {
      if (! $publicDisk->exists($pub)) return $fallback;

      $abs  = $publicDisk->path($pub);
      $info = @getimagesize($abs);
      if (!$info || empty($info[0]) || empty($info[1])) return $fallback;

      $w = (int) $info[0];
      $h = (int) $info[1];
      if ($w <= 0 || $h <= 0) return $fallback;

      $ratio = $w / $h;
      if ($ratio > 2.4) return '21 / 9';
      if ($ratio < 0.75) return '3 / 4';

      $d  = $gcd($w, $h);
      $rw = max(1, (int) round($w / $d));
      $rh = max(1, (int) round($h / $d));

      return $rw . ' / ' . $rh;
    } catch (\Throwable $e) {
      return $fallback;
    }
  };

  /* ===================== URL helpers ===================== */
  $articleUrl = function($a) use ($tz) {
    $base = $a->effective_publish_at
      ?? $a->scheduled_publish_date
      ?? $a->published_at
      ?? $a->created_at;

    $d = $base instanceof Carbon ? $base->copy() : Carbon::parse($base);
    return url($d->timezone($tz)->format('Y/m/d').'/'.$a->slug);
  };

  $promoGet = function ($p, $key, $fallback=null) {
    if (is_array($p))  return $p[$key] ?? $fallback;
    if (is_object($p)) return $p->{$key} ?? $fallback;
    return $fallback;
  };

  $promoUrl = function($p) use ($promoGet) {
    $slug = $promoGet($p, 'slug');
    if (\Illuminate\Support\Facades\Route::has('promos.show') && $slug) {
      return route('promos.show', ['promo' => $slug]);
    }
    return $slug ? url('/promos/'.$slug) : url('/promos');
  };

  /* ===================== Fallback data ===================== */
  if ($blogs === null && class_exists(\App\Models\Article::class)) {
    try {
      $q = \App\Models\Article::query();
      if (method_exists(\App\Models\Article::class, 'published')) $q = $q->published();
      $blogs = $q->latest('published_at')->take((int)$blogsCount)->get();
    } catch (\Throwable $e) { $blogs = collect(); }
  }
  $blogs = ($blogs ?: collect())->take((int)$blogsCount);

  if ($promos === null && class_exists(\App\Models\Promo::class)) {
    try {
      $q = \App\Models\Promo::query();
      if (method_exists(\App\Models\Promo::class, 'published')) {
        $q = $q->published();
      } else {
        $q = $q->whereNotNull('published_at');
      }
      $promos = $q->latest('published_at')->take((int)$promosCount)->get();
    } catch (\Throwable $e) { $promos = collect(); }
  }
  $promos = ($promos ?: collect())->take((int)$promosCount)->values();

  // ✅ True masonry distribution (row-wise) so tablet=2 cols / desktop=3 cols never “drops” a card.
  $promoCols2 = [collect(), collect()];
  $promoCols3 = [collect(), collect(), collect()];
  foreach ($promos as $idx => $p) {
    $promoCols2[$idx % 2]->push($p);
    $promoCols3[$idx % 3]->push($p);
  }

  $blogsIndexUrl  = \Illuminate\Support\Facades\Route::has('blogs') ? route('blogs') : url('/blogs');
  $promosIndexUrl = \Illuminate\Support\Facades\Route::has('promos.index') ? route('promos.index') : url('/promos');

  // Reusable promo card renderer (promo styles kept; mobile adds “tap to reveal overlay” without changing desktop hover)
  $renderPromoCard = function($p) use ($promoGet, $promoUrl, $altFor, $aspectFor) {
    $pTitle = (string) $promoGet($p, 'title', 'Untitled Promo');
    $imgRaw = $promoGet($p, 'featured_image');

    $img = $imgRaw
      ? (Str::startsWith($imgRaw, ['http://','https://']) ? $imgRaw : \Illuminate\Support\Facades\Storage::url($imgRaw))
      : asset('images/placeholder/post-wide.jpg');

    $imgAlt = $altFor($imgRaw, $pTitle) ?: $pTitle;

    $pAR  = $aspectFor($imgRaw);
    $link = $promoUrl($p);

    $safeTitle = e($pTitle);
    $safeLink  = e($link);
    $safeImg   = e($img);
    $safeAlt   = e($imgAlt);

    return <<<HTML
<a href="{$safeLink}"
   aria-label="{$safeTitle}"
   x-data="{ open:false, touch:(window.matchMedia && window.matchMedia('(hover: none)').matches) }"
   @click="if(touch && !open){ \$event.preventDefault(); open = true }"
   @click.outside="open = false"
   @keydown.escape.window="open = false"
   class="group relative block w-full overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm hover:shadow-md transition focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-gray-900/40">
  <div class="relative overflow-hidden" style="aspect-ratio: {$pAR};">
    <img src="{$safeImg}" alt="{$safeAlt}" loading="lazy" decoding="async"
         class="h-full w-full object-cover transform-gpu card-zoom">

    <div
      class="absolute inset-0 pointer-events-none opacity-0 translate-y-2 transition-all duration-300
             group-hover:opacity-100 group-hover:translate-y-0
             group-focus-visible:opacity-100 group-focus-visible:translate-y-0
             group-active:opacity-100 group-active:translate-y-0"
      :class="open ? 'opacity-100 translate-y-0' : ''"
    >
      <div class="absolute inset-0 bg-gradient-to-b from-black/10 via-black/15 to-black/80"></div>

      <div class="absolute inset-x-0 bottom-0 p-5 text-white">
        <h4 class="text-xl md:text-2xl font-semibold leading-snug line-clamp-2">
          {$safeTitle}
        </h4>

        <div class="mt-4 inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-semibold text-gray-900">
          View promo
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M13 5l7 7-7 7v-4H4v-6h9V5z"/></svg>
        </div>

        <div class="mt-2 text-xs text-white/80 md:hidden" x-show="touch && open" x-cloak>
          Tap again to open
        </div>
      </div>
    </div>
  </div>

  <span class="sr-only">Open promo: {$safeTitle}</span>
</a>
HTML;
  };
@endphp

<section class="py-12 md:py-16 bg-white">
  <div class="mx-auto max-w-[1550px] px-4 sm:px-6 lg:px-8">
    {{-- Header --}}
    <header class="flex flex-col gap-5 md:flex-row md:items-end md:justify-between">
      <div class="max-w-3xl">
        <p class="text-sm font-semibold tracking-[.28em] text-gray-500 uppercase">Updates</p>
        <h2 class="mt-2 text-4xl md:text-4xl lg:text-5xl font-semibold text-gray-900 leading-tight">
          {{ $title }}
        </h2>
        <p class="mt-2 text-base md:text-lg text-gray-700 font-medium">
          {{ $lead }}
        </p>
      </div>

      <div class="flex flex-wrap items-center gap-2">
        <a href="{{ $blogsIndexUrl }}"
           class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-900 hover:bg-gray-50">
          View all Blogs
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M13 5l7 7-7 7v-4H4v-6h9V5z"/></svg>
        </a>
        <a href="{{ $promosIndexUrl }}"
           class="inline-flex items-center gap-2 rounded-xl bg-[#CF4520] px-4 py-2.5 text-sm font-semibold text-white hover:bg-black/90">
          View all Promos
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M13 5l7 7-7 7v-4H4v-6h9V5z"/></svg>
        </a>
      </div>
    </header>

    <div class="mt-8 border-b border-gray-200"></div>

    {{-- ===================== BLOGS (fixed-size cards; title+CTA visible by default) ===================== --}}
    <div class="mt-10">
      <div class="flex items-center justify-between">
        <h3 class="text-base xl:text-2xl font-semibold text-gray-900 tracking-wide">Latest Blogs</h3>
        <a href="{{ $blogsIndexUrl }}" class="text-base font-semibold text-gray-900 hover:underline">Browse</a>
      </div>

      <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($blogs as $b)
          @php
            $bImgRaw = $b->featured_image ?? null;
            $bImg    = $bImgRaw
                        ? (Str::startsWith($bImgRaw, ['http://','https://']) ? $bImgRaw : \Illuminate\Support\Facades\Storage::url($bImgRaw))
                        : asset('images/placeholder/post-wide.jpg');
            $bAlt    = $altFor($bImgRaw, $b->image_alt ?? ($b->title ?? 'Blog image')) ?: ($b->title ?? 'Blog image');
            $bLink   = $articleUrl($b);
            $bTitle  = (string)($b->title ?? 'Untitled');
          @endphp

          <a href="{{ $bLink }}"
             aria-label="{{ $bTitle }}"
             class="group relative block overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm hover:shadow-md transition focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-gray-900/40">
            <div class="relative aspect-[16/9] overflow-hidden">
              <img src="{{ $bImg }}" alt="{{ $bAlt }}" loading="lazy" decoding="async"
                   class="h-full w-full object-cover transform-gpu card-zoom">

              <div class="absolute inset-0 pointer-events-none">
                <div class="absolute inset-0 bg-gradient-to-b from-black/0 via-black/10 to-black/65 transition-opacity duration-300
                            group-hover:opacity-100 group-focus-visible:opacity-100"></div>

                <div class="absolute inset-x-0 bottom-0 p-5 text-white">
                  <h4 class="text-xl md:text-2xl font-semibold leading-snug line-clamp-2">
                    {{ $bTitle }}
                  </h4>

                  <div class="mt-4 inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-semibold text-gray-900">
                    Read article
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M13 5l7 7-7 7v-4H4v-6h9V5z"/></svg>
                  </div>
                </div>
              </div>
            </div>

            <span class="sr-only">Open blog: {{ $bTitle }}</span>
          </a>
        @empty
          <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5 text-sm font-semibold text-gray-700 lg:col-span-3">
            No blog posts yet.
          </div>
        @endforelse
      </div>
    </div>

    {{-- ===================== PROMOS (STRICT: mobile=1 col, tablet=2 col, desktop=3 col) ===================== --}}
    <div class="mt-12">
      <div class="flex items-center justify-between">
        <h3 class="text-base xl:text-2xl font-semibold text-gray-900 tracking-wide">Latest Promos</h3>
        <a href="{{ $promosIndexUrl }}" class="text-base font-semibold text-gray-900 hover:underline">Browse</a>
      </div>

      @if($promos->count())
        {{-- ✅ MOBILE: 1 column (variable heights = “masonry” feel) --}}
        <div class="mt-5 grid grid-cols-1 gap-6 md:hidden">
          @foreach($promos as $p)
            {!! $renderPromoCard($p) !!}
          @endforeach
        </div>

        {{-- ✅ TABLET: 2 columns masonry --}}
        <div class="hidden md:grid lg:hidden mt-5 grid-cols-2 gap-6">
          @for($c=0; $c<2; $c++)
            <div class="space-y-6">
              @foreach($promoCols2[$c] as $p)
                {!! $renderPromoCard($p) !!}
              @endforeach
            </div>
          @endfor
        </div>

        {{-- ✅ DESKTOP: 3 columns masonry --}}
        <div class="hidden lg:grid mt-5 grid-cols-3 gap-6">
          @for($c=0; $c<3; $c++)
            <div class="space-y-6">
              @foreach($promoCols3[$c] as $p)
                {!! $renderPromoCard($p) !!}
              @endforeach
            </div>
          @endfor
        </div>
      @else
        <div class="mt-5 rounded-2xl border border-gray-200 bg-gray-50 p-5 text-sm font-semibold text-gray-700">
          No promos yet.
        </div>
      @endif
    </div>
  </div>

  <style>
    /* Hover zoom only for desktop fine pointer; mobile stays stable */
    .card-zoom { transition: transform .35s ease; will-change: transform; }
    @media (hover:hover) and (pointer:fine){
      .group:hover .card-zoom { transform: scale(1.03); }
    }
  </style>
</section>
