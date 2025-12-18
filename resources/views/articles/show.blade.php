{{-- resources/views/articles/show.blade.php --}}
@extends('layouts.app')

@section('title', $article->title ?? 'Article')

@section('content')
@php
  use Illuminate\Support\Str;
  use Illuminate\Support\Carbon;

  $tz   = 'Asia/Manila';
  $now  = Carbon::now($tz);
  $disk = \Illuminate\Support\Facades\Storage::disk('public');

  // === Helpers ==============================================================
  // Normalize any src (absolute URL or /storage/...) to a public-disk path.
  $toPublicPath = function (?string $src): ?string {
      if (!$src) return null;
      $path = parse_url($src, PHP_URL_PATH) ?: $src;         // strip query/host if present
      $path = ltrim($path, '/');
      $path = preg_replace('#^storage/#', '', $path);        // /storage/foo -> foo
      $path = preg_replace('#^public/#',  '', $path);        // public/foo   -> foo
      return $path ?: null;
  };

  // Fetch 'alt' from sidecar meta (foo.jpg.meta.json) if it exists.
  $altFromSidecar = function (?string $src) use ($disk, $toPublicPath): ?string {
      $pub = $toPublicPath((string) $src);
      if (!$pub) return null;
      $metaPath = $pub . '.meta.json';
      if (!$disk->exists($metaPath)) return null;
      try {
          $raw = $disk->get($metaPath);
          $arr = json_decode($raw, true, flags: JSON_THROW_ON_ERROR);
          return is_array($arr) && !empty($arr['alt']) ? (string) $arr['alt'] : null;
      } catch (\Throwable $e) {
          return null;
      }
  };

  // Safely inject missing/empty alt attributes into HTML body images using sidecar meta.
  // Regex-based (no DOM), preserves the original HTML exactly and never drops the body.
  $injectAltIntoBody = function (?string $html) use ($altFromSidecar) {
      if ($html === null) return '';
      $html = (string) $html;

      $out = @preg_replace_callback('/<img\b[^>]*>/i', function ($m) use ($altFromSidecar) {
          $tag = $m[0];

          // Grab src
          $src = '';
          if (preg_match('/\bsrc\s*=\s*([\'"])(.*?)\1/i', $tag, $sm)) {
              $src = $sm[2];
          }

          // If there is an alt="" already and it's non-empty, keep it
          if (preg_match('/\balt\s*=\s*([\'"])(.*?)\1/i', $tag, $am)) {
              $current = trim($am[2]);
              if ($current !== '') {
                  return $tag; // keep as-is
              }
              // Fill the empty alt
              $alt = $altFromSidecar($src);
              if ($alt === null) return $tag;

              // Replace first alt=""
              $safeAlt = htmlspecialchars($alt, ENT_QUOTES, 'UTF-8');
              return preg_replace('/\balt\s*=\s*([\'"])(.*?)\1/i', 'alt="'.$safeAlt.'"', $tag, 1);
          }

          // No alt at all — inject one if we have sidecar meta
          $alt = $altFromSidecar($src);
          if ($alt === null) return $tag;

          $safeAlt = htmlspecialchars($alt, ENT_QUOTES, 'UTF-8');

          // Self-closing or not — inject before the closing bracket
          if (preg_match('/\/>\s*$/', $tag)) {
              return preg_replace('/\/>\s*$/', ' alt="'.$safeAlt.'" />', $tag, 1);
          }
          return preg_replace('/>\s*$/', ' alt="'.$safeAlt.'">', $tag, 1);
      }, $html);

      // If PCRE error occurred ($out === null), just return original HTML
      return is_string($out) ? $out : $html;
  };

  // === Core article data ====================================================
  $title   = $article->title ?? 'Untitled';
  $excerpt = $article->excerpt ?? Str::limit(strip_tags($article->body ?? ''), 160);

  // Normalize dates to PH time (UTC+8)
  $effBase = $article->effective_publish_at
    ?? $article->scheduled_publish_date
    ?? $article->published_at
    ?? $article->created_at;

  $pubDate = $effBase
    ? ($effBase instanceof Carbon ? $effBase->copy() : Carbon::parse($effBase))->timezone($tz)
    : null;

  $expiresAt = $article->expires_at ?: null;
  $expDate = $expiresAt
      ? ($expiresAt instanceof Carbon ? $expiresAt->copy() : Carbon::parse($expiresAt))->timezone($tz)
      : null;

  $isScheduled = $pubDate && $pubDate->greaterThan($now);
  $isExpired   = $expDate && $expDate->lessThanOrEqualTo($now);

  // Featured image + sidecar alt
  $rawImg = $article->featured_image ?? null;
  $img    = $rawImg
              ? (Str::startsWith($rawImg, ['http://','https://']) ? $rawImg : \Illuminate\Support\Facades\Storage::url($rawImg))
              : asset('images/placeholder/post-wide.jpg');
  $imgAlt = $altFromSidecar($rawImg) ?: $title; // fallback to title

  // Taxonomy
  $category = optional($article->category);
  $tagsCsv  = trim((string)($article->tags ?? ''));
  $tags     = collect(array_filter(array_map('trim', explode(',', $tagsCsv))));

  // Reading time
  $wordCount      = str_word_count(strip_tags($article->body ?? ''), 0);
  $readingMinutes = max(1, (int) ceil($wordCount / 200));

  // Sharing
  $shareUrl = url()->current();
  $fbShare  = 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode($shareUrl);
  $waShare  = 'https://wa.me/?text=' . rawurlencode($title . ' — ' . $shareUrl);
  $viberShare = 'viber://forward?text=' . rawurlencode($title . ' — ' . $shareUrl);

  // Dates for JSON-LD
  $updatedAt = $article->updated_at ?? null;
  $updDate = $updatedAt
      ? ($updatedAt instanceof Carbon ? $updatedAt->copy() : Carbon::parse($updatedAt))->timezone($tz)
      : null;

  // Helper: build article URL /YY/MM/DD/slug (2-digit year)
  $articleUrl = function ($a) use ($tz) {
      if (!$a) {
          return '#';
      }

      $base = $a->effective_publish_at
          ?? $a->published_at
          ?? $a->created_at;

      if (!$base) {
          return '#';
      }

      if ($base instanceof Carbon) {
          $dt = $base->copy()->timezone($tz);
      } else {
          $dt = Carbon::parse($base)->timezone($tz);
      }

      return route('articles.show', [
          'year'  => $dt->format('y'), // 2-digit year
          'month' => $dt->format('m'),
          'day'   => $dt->format('d'),
          'slug'  => $a->slug,
      ]);
  };


  // Fallback recent posts if controller didn't pass $recent
  $recent = $recent
      ?? \App\Models\Article::published()
          ->where('id', '<>', $article->id)
          ->latest('published_at')
          ->take(6)
          ->get();

  // Body with alt injected (for images inserted via the media library)
  $rawBody   = (string)($article->body ?? '');
  $processed = $injectAltIntoBody($rawBody);
  // Hard fallback so content always shows even if injector fails/returns empty
  $bodyHtml  = (is_string($processed) && trim($processed) !== '') ? $processed : $rawBody;

@endphp

@push('head')
  {{-- SEO basics --}}
  <meta name="description" content="{{ $excerpt }}">
  <link rel="canonical" href="{{ $shareUrl }}">

  {{-- Open Graph --}}
  <meta property="og:type" content="article">
  <meta property="og:title" content="{{ $title }}">
  <meta property="og:description" content="{{ $excerpt }}">
  <meta property="og:url" content="{{ $shareUrl }}">
  <meta property="og:image" content="{{ $img }}">
  <meta property="og:image:alt" content="{{ $imgAlt }}">
  <meta property="og:site_name" content="Astoria Vacation & Leisure Club, Incorporated">

  {{-- Twitter --}}
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="{{ $title }}">
  <meta name="twitter:description" content="{{ $excerpt }}">
  <meta name="twitter:image" content="{{ $img }}">
  <meta name="twitter:image:alt" content="{{ $imgAlt }}">

  {{-- Optional: avoid indexing scheduled/expired posts --}}
  @if($isScheduled || $isExpired)
    <meta name="robots" content="noindex,follow">
  @endif

  {{-- JSON-LD Article schema (ImageObject with caption for ALT) --}}
  <script type="application/ld+json">
  {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Article',
        'headline' => $title,
        'description' => $excerpt,
        'image' => [
          '@type' => 'ImageObject',
          'url' => $img,
          'caption' => $imgAlt,
        ],
        'datePublished' => $pubDate ? $pubDate->toIso8601String() : null,
        'dateModified'  => $updDate ? $updDate->toIso8601String() : null,
        'author' => [
          '@type' => 'Person',
          'name'  => $article->author ?? config('app.name'),
        ],
        'mainEntityOfPage' => [
          '@type' => 'WebPage',
          '@id'   => $shareUrl,
        ],
      ], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT) !!}
  </script>
@endpush

<main
  class="bg-white"
  x-data="{
    copied:false,
    copying:false,
    async copyLink(url){
      try{
        this.copying = true;
        if (navigator.clipboard && window.isSecureContext !== false) {
          await navigator.clipboard.writeText(url);
        } else {
          const ta = document.createElement('textarea');
          ta.value = url;
          ta.setAttribute('readonly','');
          ta.style.position = 'fixed';
          ta.style.top = '-1000px';
          ta.style.opacity = '0';
          document.body.appendChild(ta);
          ta.focus();
          ta.select();
          document.execCommand('copy');
          document.body.removeChild(ta);
        }
        this.copied = true;
        setTimeout(()=>{ this.copied = false; this.copying = false; }, 1600);
      } catch(e){
        this.copying = false;
      }
    }
  }"
>

  {{-- Hero / Cover (wider container) --}}
  <section class="relative">
    <div class="relative h-[560px] md:h-[480px] lg:h-[520px] overflow-hidden">
      <img src="{{ $img }}" alt="{{ $imgAlt }}" loading="eager" decoding="async" class="w-full h-full object-cover">
      <div class="absolute inset-0 bg-gradient-to-b from-black/55 via-black/30 to-black/60"></div>

      <div class="absolute inset-0">
        <div class="max-w-7xl mx-auto h-full px-6 flex items-end pb-10">
          <div class="text-white w-full">
            <div class="flex flex-wrap items-center gap-3 text-sm text-white/90">
              <a href="{{ route('blogs') }}" class="hover:underline">Blog</a>
              <span aria-hidden="true">/</span>
              @if($category && $category->exists)
                @php $catUrl = url('/blogs') . '?' . http_build_query(['category' => $category->slug]); @endphp
                <a href="{{ $catUrl }}" class="hover:underline">{{ $category->name }}</a>
                <span aria-hidden="true">/</span>
              @endif
              @if($pubDate)
                @if($isScheduled)
                  <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 text-amber-800 px-2 py-0.5 text-xs font-semibold">
                    <i class="fa-regular fa-clock"></i> Scheduled
                  </span>
                  <time class="opacity-90" datetime="{{ $pubDate->toIso8601String() }}">@php echo $pubDate->format('F j, Y • g:i A') @endphp</time>
                @else
                  <time class="opacity-90" datetime="{{ $pubDate->toIso8601String() }}">@php echo $pubDate->format('F j, Y') @endphp</time>
                @endif
                <span class="opacity-70" aria-hidden="true">•</span>
              @endif
              <span class="opacity-90">{{ $readingMinutes }} min read</span>
            </div>

            <h1 class="mt-3 text-3xl md:text-5xl lg:text-6xl font-semibold leading-tight max-w-6xl">
              {{ $title }}
            </h1>
            @if($excerpt)
              <p class="mt-3 md:mt-4 text-white/90 md:text-lg max-w-3xl">{{ $excerpt }}</p>
            @endif
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- Content + Sidebar --}}
  <section class="py-10 md:py-14">
    <div class="max-w-7xl mx-auto px-6 grid lg:grid-cols-12 gap-10">

      {{-- Article --}}
      <article class="lg:col-span-8">

        {{-- Meta + Share icons + Copy Link --}}
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
          <div class="text-sm text-gray-600 flex items-center gap-2">
            @if($pubDate)
              @if($isScheduled)
                <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 text-amber-800 px-2 py-0.5 text-xs font-semibold">
                  <i class="fa-regular fa-clock"></i> Scheduled
                </span>
                <time datetime="{{ $pubDate->toIso8601String() }}">{{ $pubDate->format('M j, Y • g:i A') }}</time>
              @else
                <time datetime="{{ $pubDate->toIso8601String() }}">{{ $pubDate->format('M j, Y') }}</time>
              @endif
              <span aria-hidden="true">•</span>
            @endif
            <span>{{ $readingMinutes }} min read</span>
            @if($expDate)
              <span class="text-gray-400">•</span>
              <span>Expires: {{ $expDate->format('M j, Y • g:i A') }}</span>
            @endif
          </div>

          <div class="flex flex-wrap items-center gap-2">
            {{-- Social share icons --}}
            <a href="{{ $fbShare }}" target="_blank" rel="noopener"
               class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-gray-200 text-[#1877F2] hover:bg-[#1877F2]/10 transition"
               aria-label="Share on Facebook">
              <i class="fa-brands fa-facebook-f text-sm"></i>
            </a>

            <a href="{{ $waShare }}" target="_blank" rel="noopener"
               class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-gray-200 text-[#25D366] hover:bg-[#25D366]/10 transition"
               aria-label="Share on WhatsApp">
              <i class="fa-brands fa-whatsapp text-sm"></i>
            </a>

            <a href="{{ $viberShare }}"
               class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-gray-200 text-[#7360F2] hover:bg-[#7360F2]/10 transition"
               aria-label="Share on Viber">
              <i class="fa-brands fa-viber text-sm"></i>
            </a>

            {{-- Copy link --}}
            <button
              type="button"
              @click="copyLink('{{ $shareUrl }}')"
              class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-gray-200 hover:bg-gray-50 text-sm"
              :class="copying ? 'opacity-70 cursor-wait' : ''"
            >
              <i class="fa-solid fa-link text-[#CF4520]"></i>
              <span x-show="!copied" x-cloak>Copy link</span>
              <span x-show="copied" x-cloak class="text-[#CF4520]" aria-live="polite">Copied!</span>
            </button>
          </div>
        </div>

        {{-- Body (with image ALT injected from sidecar when missing) --}}
        <div class="prose max-w-none prose-img:rounded-xl prose-img:shadow-sm">
          {!! $bodyHtml !!}
        </div>

        {{-- Tags --}}
        @if($tags->count())
          <div class="mt-10 flex flex-wrap gap-2">
            @foreach($tags as $t)
              @php $tagUrl = url('/blogs') . '?' . http_build_query(['tag' => Str::slug($t)]); @endphp
              <a href="{{ $tagUrl }}" class="inline-flex items-center gap-1 text-sm px-3 py-1.5 rounded-full bg-[#CF4520]/10 text-[#CF4520] border border-[#CF4520]/40">
                <i class="fa-solid fa-hashtag text-xs"></i> {{ $t }}
              </a>
            @endforeach
          </div>
        @endif

        {{-- Prev / Next --}}
        @if(!empty($prev) || !empty($next))
          <nav class="mt-12 grid md:grid-cols-2 gap-4">
            @if(!empty($prev))
              @php
                $pImgRaw = $prev->featured_image ?? null;
                $pImg    = $pImgRaw
                            ? (Str::startsWith($pImgRaw, ['http://','https://']) ? $pImgRaw : \Illuminate\Support\Facades\Storage::url($pImgRaw))
                            : asset('images/placeholder/post.jpg');
                $pAlt    = $altFromSidecar($pImgRaw) ?: ($prev->title ?? 'Previous article');
              @endphp
              <a href="{{ $articleUrl($prev) }}"
                 class="group rounded-2xl border border-gray-200 overflow-hidden hover:shadow-md transition">
                <div class="aspect-[16/9] overflow-hidden">
                  <img src="{{ $pImg }}" alt="{{ $pAlt }}" loading="lazy" decoding="async" class="w-full h-full object-cover group-hover:scale-[1.02] transition-transform">
                </div>
                <div class="p-4">
                  <div class="text-xs uppercase tracking-wide text-gray-500 mb-1">Previous</div>
                  <div class="font-semibold text-slate-800 line-clamp-2 group-hover:text-[#CF4520]">{{ $prev->title }}</div>
                </div>
              </a>
            @endif
            @if(!empty($next))
              @php
                $nImgRaw = $next->featured_image ?? null;
                $nImg    = $nImgRaw
                            ? (Str::startsWith($nImgRaw, ['http://','https://']) ? $nImgRaw : \Illuminate\Support\Facades\Storage::url($nImgRaw))
                            : asset('images/placeholder/post.jpg');
                $nAlt    = $altFromSidecar($nImgRaw) ?: ($next->title ?? 'Next article');
              @endphp
              <a href="{{ $articleUrl($next) }}"
                 class="group rounded-2xl border border-gray-200 overflow-hidden hover:shadow-md transition md:text-right">
                <div class="aspect-[16/9] overflow-hidden">
                  <img src="{{ $nImg }}" alt="{{ $nAlt }}" loading="lazy" decoding="async" class="w-full h-full object-cover group-hover:scale-[1.02] transition-transform">
                </div>
                <div class="p-4">
                  <div class="text-xs uppercase tracking-wide text-gray-500 mb-1">Next</div>
                  <div class="font-semibold text-slate-800 line-clamp-2 group-hover:text-[#CF4520]">{{ $next->title }}</div>
                </div>
              </a>
            @endif
          </nav>
        @endif

        {{-- Back to blog (CTA icon in main color) --}}
        <div class="mt-10">
          <a href="{{ route('blogs') }}"
             class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-[#CF4520] text-white font-semibold shadow-sm hover:bg-[#3F2021] transition">
            <i class="fa-solid fa-arrow-left-long bg-white text-[#CF4520] rounded-full p-1 text-xs"></i>
            <span>Back to Blogs</span>
          </a>
        </div>
      </article>

      {{-- Sidebar: Recent Posts + Article Info --}}
      <aside class="lg:col-span-4 space-y-6">

        {{-- Recent Posts (redesigned) --}}
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
          <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between gap-2">
            <div class="flex items-center gap-3">
              <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-[#CF4520]/10 text-[#CF4520]">
                <i class="fa-solid fa-clock-rotate-left text-sm"></i>
              </span>
              <div>
                <h3 class="text-sm font-semibold text-gray-900">Recent Posts</h3>
                <p class="text-[12px] font-medium text-gray-700">Latest stories from the blog</p>
              </div>
            </div>
            <a href="{{ route('blogs') }}"
               class="text-[11px] font-semibold text-[#CF4520] hover:text-[#CF4520] inline-flex items-center gap-1">
              View all
              <i class="fa-solid fa-arrow-right-long text-[10px]"></i>
            </a>
          </div>

          <div class="p-5 space-y-4 max-h-[460px] overflow-y-auto">
            @forelse($recent as $r)
              @php
                $rImgRaw = $r->featured_image ?? null;
                $rImg    = $rImgRaw
                            ? (Str::startsWith($rImgRaw, ['http://','https://']) ? $rImgRaw : \Illuminate\Support\Facades\Storage::url($rImgRaw))
                            : asset('images/placeholder/post.jpg');
                $rAlt    = $altFromSidecar($rImgRaw) ?: ($r->title ?? 'Recent article');
                $rBase   = $r->effective_publish_at
                  ?? $r->scheduled_publish_date
                  ?? $r->published_at
                  ?? $r->created_at;
                $rDate   = ($rBase instanceof Carbon ? $rBase->copy() : Carbon::parse($rBase))->timezone($tz);

                // Dynamic category label per recent article
                $rCategory      = optional($r->category);
                $rCategoryLabel = ($rCategory && $rCategory->exists) ? $rCategory->name : 'Article';
              @endphp
              <a href="{{ $articleUrl($r) }}" class="group flex gap-3 items-stretch">
                <div class="w-24 h-20 shrink-0 rounded-xl overflow-hidden border border-gray-200 bg-slate-100 relative">
                  <img src="{{ $rImg }}" alt="{{ $rAlt }}" loading="lazy" decoding="async"
                       class="h-full w-full object-cover group-hover:scale-[1.03] transition-transform">
                  <span class="absolute inset-0 ring-1 ring-black/5 pointer-events-none"></span>
                </div>
                <div class="min-w-0 flex-1">
                  <p class="text-[10px] uppercase tracking-[.18em] text-[#CF4520] font-semibold">
                    {{ $rCategoryLabel }}
                  </p>
                  <div class="mt-0.5 text-sm font-semibold leading-snug text-slate-900 line-clamp-2 group-hover:text-[#CF4520]">
                    {{ $r->title }}
                  </div>
                  <div class="mt-1 text-[12px] font-medium text-gray-700 flex items-center gap-1.5">
                    <i class="fa-regular fa-calendar text-[10px]"></i>
                    {{ $rDate->format('M j, Y') }}
                  </div>
                </div>
              </a>
            @empty
              <div class="flex flex-col items-center justify-center text-sm text-gray-600 py-6">
                <i class="fa-regular fa-file-lines text-2xl text-gray-300 mb-2"></i>
                <span>No recent posts yet.</span>
              </div>
            @endforelse
          </div>
        </div>

        {{-- Article Info (redesigned) --}}
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
          <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-3">
            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-[#3F2021]/10 text-[#3F2021]">
              <i class="fa-solid fa-circle-info text-sm"></i>
            </span>
            <div>
              <h3 class="text-sm font-semibold text-gray-900">Article Info</h3>
              <p class="text-[12px] font-medium text-gray-700">Quick details at a glance</p>
            </div>
          </div>

          <dl class="px-5 py-4 space-y-3 text-sm text-gray-700">

            @if($category && $category->exists)
              <div class="flex items-start gap-2.5">
                <span class="mt-1 inline-flex h-6 w-6 items-center justify-center rounded-full bg-[#CF4520]/10 text-[#CF4520] text-xs">
                  <i class="fa-solid fa-folder-open"></i>
                </span>
                <div>
                  <dt class="text-[12px] font-medium uppercase tracking-[.18em] text-gray-700">Category</dt>
                  <dd class="font-semibold text-gray-900">{{ $category->name }}</dd>
                </div>
              </div>
            @endif

            <div class="flex items-start gap-2.5">
              <span class="mt-1 inline-flex h-6 w-6 items-center justify-center rounded-full bg-slate-100 text-slate-700 text-xs">
                <i class="fa-regular fa-user"></i>
              </span>
              <div>
                <dt class="text-[12px] font-medium  uppercase tracking-[.18em] text-gray-500">Author</dt>
                <dd class="font-semibold text-gray-900">{{ $article->author ?? 'Astoria Plaza' }}</dd>
              </div>
            </div>

            @if($pubDate)
              <div class="flex items-start gap-2.5">
                <span class="mt-1 inline-flex h-6 w-6 items-center justify-center rounded-full bg-[#CF4520]/10 text-[#CF4520] text-xs">
                  <i class="fa-regular fa-calendar-check"></i>
                </span>
                <div>
                  <dt class="text-[12px] font-medium  uppercase tracking-[.18em] text-gray-500">
                    {{ $isScheduled ? 'Scheduled' : 'Published' }}
                  </dt>
                  <dd class="font-medium text-gray-900">
                    {{ $pubDate->format('M j, Y • g:i A') }} <span class="text-xs text-gray-500">(GMT+8)</span>
                  </dd>
                </div>
              </div>
            @endif

            @if($expDate)
              <div class="flex items-start gap-2.5">
                <span class="mt-1 inline-flex h-6 w-6 items-center justify-center rounded-full bg-[#3F2021]/10 text-[#3F2021] text-xs">
                  <i class="fa-regular fa-calendar-xmark"></i>
                </span>
                <div>
                  <dt class="text-[12px] font-medium  uppercase tracking-[.18em] text-gray-500">Expires</dt>
                  <dd class="font-medium text-gray-900">
                    {{ $expDate->format('M j, Y • g:i A') }} <span class="text-xs text-gray-500">(GMT+8)</span>
                  </dd>
                </div>
              </div>
            @endif

            @if($tags->count())
              <div class="pt-1">
                <dt class="ttext-[12px] font-medium uppercase tracking-[.18em] text-gray-500 mb-1">Tags</dt>
                <dd>
                  <div class="flex flex-wrap gap-2">
                    @foreach($tags as $t)
                      @php $tagUrl = url('/blogs') . '?' . http_build_query(['tag' => \Illuminate\Support\Str::slug($t)]); @endphp
                      <a href="{{ $tagUrl }}"
                         class="inline-flex items-center gap-1 text-[11px] px-2.5 py-1 rounded-full bg-[#CF4520]/5 text-[#CF4520] border border-[#CF4520]/30">
                        <i class="fa-solid fa-tag text-[10px]"></i>
                        {{ $t }}
                      </a>
                    @endforeach
                  </div>
                </dd>
              </div>
            @endif

          </dl>
        </div>

      </aside>

    </div>
  </section>
</main>

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
