@extends('layouts.app')

@section('title', isset($promo->title) ? $promo->title : 'Promo')

@section('content')
@php
  use Illuminate\Support\Str;
  use Illuminate\Support\Carbon;

  $tz   = 'Asia/Manila';
  $now  = Carbon::now($tz);
  $disk = \Illuminate\Support\Facades\Storage::disk('public');

  // --- helpers --------------------------------------------------------------
  $toPublicPath = function (?string $src): ?string {
      if (!$src) return null;
      $path = parse_url($src, PHP_URL_PATH) ?: $src;
      $path = ltrim($path, '/');
      $path = preg_replace('#^storage/#', '', $path);
      $path = preg_replace('#^public/#',  '', $path);
      return $path ?: null;
  };

  // Read sidecar meta (e.g. foo.jpg.meta.json) and return 'alt' if present
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

  // Inject missing/empty alt into all <img> in HTML using sidecar alt (regex-based, safe fallback)
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

  // --- core fields ----------------------------------------------------------
  $get = function ($key, $fallback=null) use ($promo) {
    if (is_array($promo))  return $promo[$key]    ?? $fallback;
    if (is_object($promo)) return $promo->{$key}  ?? $fallback;
    return $fallback;
  };

  $title   = $get('title', 'Untitled Promo');
  $slug    = $get('slug', Str::slug($title));
  $excerpt = $get('excerpt', '');
  $body    = $get('body', '');

  // Featured image + computed alt from sidecar (fallback to title)
  $imgRaw  = $get('featured_image');
  $img     = $imgRaw
              ? (Str::startsWith($imgRaw, ['http://','https://'])
                  ? $imgRaw
                  : \Illuminate\Support\Facades\Storage::url($imgRaw))
              : asset('images/placeholder/post-wide.jpg');
  $imgAlt  = $altFromSidecar($imgRaw) ?: $title;

  // Body with alt injected (hard fallback to raw body if anything fails/empties)
  $rawBody  = (string) $body;
  $processed = $injectAltIntoBody($rawBody);
  $bodyHtml = (is_string($processed) && trim($processed) !== '') ? $processed : $rawBody;

  // Dates
  $toPH = function ($val) use ($tz) {
    if (empty($val)) return null;
    if ($val instanceof \Carbon\CarbonInterface) return $val->copy()->timezone($tz);
    try { return Carbon::parse($val, 'UTC')->timezone($tz); } catch (\Throwable $e) {}
    try { return Carbon::parse($val, $tz); }  catch (\Throwable $e) {}
    return null;
  };

  $createdAt = $toPH($get('created_at'));
  $updatedAt = $toPH($get('updated_at'));

  $schedAt = $toPH($get('scheduled_publish_date') ?? $get('starts_at'));
  $pubAt   = $toPH($get('published_at'));
  $expAt   = $toPH($get('expires_at') ?? $get('ends_at'));

  // Effective publish
  $pubDate = ($schedAt && $schedAt->greaterThan($now)) ? $schedAt : ($pubAt ?: ($schedAt ?: $createdAt));

  $isScheduled = $pubDate && $pubDate->greaterThan($now);
  $isExpired   = $expAt   && $expAt->lessThanOrEqualTo($now);

  $status = $isExpired ? 'Expired' : ($isScheduled ? 'Scheduled' : 'Published');

  // Status icon (FA)
  $statusIcon = match (true) {
    $isExpired   => 'fa-hourglass-end',
    $isScheduled => 'fa-clock',
    default      => 'fa-circle-check',
  };

  // Validity label
  $startLbl = $schedAt ? $schedAt->isoFormat('MMM D, YYYY') : null;
  $endLbl   = $expAt   ? $expAt->isoFormat('MMM D, YYYY')   : null;
  $validity = $startLbl && $endLbl ? "{$startLbl} – {$endLbl}"
            : ($endLbl ? "Until {$endLbl}" : ($startLbl ? "From {$startLbl}" : null));

  // Back link
  $backHref = \Illuminate\Support\Facades\Route::has('promos.index')
    ? route('promos.index')
    : url('/promos');

  // Share links
  $canonicalUrl = url()->current();
  $shareSnippet = trim($excerpt) ? Str::limit(strip_tags($excerpt), 120, '…') : $title;
  $fbShare     = 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode($canonicalUrl);
  $waShare     = 'https://wa.me/?text=' . rawurlencode($title . ' — ' . $canonicalUrl);
  $viberShare  = 'viber://forward?text=' . rawurlencode($title . ' — ' . $canonicalUrl);
@endphp

@push('head')
  <meta name="description" content="{{ $shareSnippet }}">
  <link rel="canonical" href="{{ $canonicalUrl }}">

  <meta property="og:type" content="article">
  <meta property="og:title" content="{{ $title }}">
  <meta property="og:description" content="{{ $shareSnippet }}">
  <meta property="og:url" content="{{ $canonicalUrl }}">
  <meta property="og:image" content="{{ $img }}">
  <meta property="og:image:alt" content="{{ $imgAlt }}">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="{{ $title }}">
  <meta name="twitter:description" content="{{ $shareSnippet }}">
  <meta name="twitter:image" content="{{ $img }}">
  <meta name="twitter:image:alt" content="{{ $imgAlt }}">

  @if($isScheduled || $isExpired)
    <meta name="robots" content="noindex,follow">
  @endif

  {{-- Font Awesome (load once) --}}
  @once
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
          integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
          crossorigin="anonymous" referrerpolicy="no-referrer" />
  @endonce

  {{-- JSON-LD with ImageObject + caption (ALT) --}}
  <script type="application/ld+json">
    {!! json_encode([
      '@context' => 'https://schema.org',
      '@type'    => 'Article',
      'headline' => $title,
      'description' => $shareSnippet,
      'image' => [
        '@type'    => 'ImageObject',
        'url'      => $img,
        'caption'  => $imgAlt,
      ],
      'datePublished' => $pubDate ? $pubDate->toIso8601String() : null,
      'dateModified'  => $updatedAt ? $updatedAt->toIso8601String() : null,
      'author' => [
        '@type' => 'Organization',
        'name'  => 'AVLCI',
      ],
      'mainEntityOfPage' => [
        '@type' => 'WebPage',
        '@id'   => $canonicalUrl,
      ],
    ], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT) !!}
  </script>
@endpush

<style>
  :root{
    --ap-teal:#CF4520;
    --ap-orange:#CF4520;
    --ap-ink:#3F2021;
  }

  [x-cloak]{display:none!important}

  /* Larger promo body text */
  .prose{
    font-size:1.02rem;
    line-height:1.75;
  }
  @media (min-width:768px){
    .prose{
      font-size:1.08rem;
    }
  }
  .prose a{
    color:var(--ap-teal);
    text-decoration-color:var(--ap-teal);
  }
  .prose a:hover{ opacity:.9; }
  .prose strong,
  .prose h2,
  .prose h3{
    color:var(--ap-teal);
  }

  /* HERO — mix teal + orange instead of black */
  #promo-hero{
    background-color:#3F2021;
  }
  #promo-hero .fx{
    opacity:0;
    transform:translateY(14px);
    animation:promoHeroFade .7s cubic-bezier(.22,1,.36,1) forwards;
    animation-delay:var(--d,0ms);
    will-change:transform,opacity;
  }
  @keyframes promoHeroFade{
    from{opacity:0;transform:translateY(18px);}
    to{opacity:1;transform:none;}
  }
  @media (prefers-reduced-motion: reduce){
    #promo-hero .fx{animation:none!important;opacity:1!important;transform:none!important;}
  }

  .promo-glass{
    background:linear-gradient(135deg,rgba(0,26,24,0.9),rgba(0,26,24,0.78));
    border-radius:1.75rem;
    border:1px solid rgba(255,255,255,0.2);
    box-shadow:0 24px 60px rgba(0,0,0,.55);
    backdrop-filter:blur(14px);
  }

  .chip{
    display:inline-flex;
    align-items:center;
    gap:.45rem;
    border-radius:9999px;
    padding:.375rem .8rem;
    font-size:.78rem;
    font-weight:600;
    letter-spacing:.02em;
    background:#CF4520;
    border:1px solid rgba(255,255,255,.22);
    color:#fff;
  }

  .chip--status{
    border-color:#3F2021;
    background:#3F2021;
  }
  .chip--date{
    border-color:rgba(255,255,255,.25);
  }
  .chip--accent{
    border-color:#CF4520;
    background:#3F2021;
  }

  .promo-shell{
    background:
      radial-gradient(120% 120% at 0% 0%, rgba(0,168,149,0.06) 0%, transparent 52%),
      radial-gradient(120% 120% at 100% 100%, rgba(240,83,35,0.06) 0%, transparent 52%);
  }

  .promo-badge{
    display:inline-flex;
    align-items:center;
    gap:.4rem;
    border-radius:9999px;
    padding:.25rem .7rem;
    font-size:.72rem;
    font-weight:700;
    letter-spacing:.2em;
    text-transform:uppercase;
    background:rgba(0,0,0,.45);
    border:1px solid rgba(255,255,255,.28);
  }

  .promo-poster-tag{
    font-size:.75rem;
    font-weight:600;
    text-transform:uppercase;
    letter-spacing:.16em;
    color:var(--ap-teal);
  }

  .soft-radial{
    background:
      radial-gradient(120% 120% at 0% 0%, rgba(0,168,149,0.10) 0%, rgba(0,168,149,0) 60%),
      radial-gradient(120% 120% at 100% 100%, rgba(240,83,35,0.08) 0%, rgba(240,83,35,0) 60%);
    background-color:#ffffff;
  }

  .other-promo-pill{
    border-radius:9999px;
    padding:.2rem .7rem;
    font-size:.72rem;
    font-weight:600;
    text-transform:uppercase;
    letter-spacing:.12em;
    color:var(--ap-teal);
    background:rgba(0,168,149,0.08);
    border:1px solid rgba(0,168,149,0.15);
  }

  .other-promo-card{
    position:relative;
    border-radius:1.25rem;
    border:1px solid #e5e7eb;
    padding:1rem 1.1rem 1.05rem 1.05rem;
    background:linear-gradient(135deg,#ffffff,rgba(0,168,149,0.02));
    box-shadow:0 8px 24px rgba(15,23,42,.06);
  }
  .other-promo-card::before{
    content:'';
    position:absolute;
    inset:0;
    border-radius:1.15rem;
    border-left:4px solid var(--ap-teal);
    opacity:.95;
    pointer-events:none;
  }

  .share-icon-btn{
    height:2.5rem;
    width:2.5rem;
    border-radius:9999px;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    border-width:1px;
    border-style:solid;
    font-size:1rem;
  }
</style>

<main class="bg-white promo-shell">

  {{-- =========================
       HERO — Brand gradient, glass card, status chips
  ========================== --}}
  <section id="promo-hero" class="relative text-white">
    <div class="mx-auto max-w-[1500px] px-6">
      <div class="min-h-[72vh] pt-28 sm:pt-32 pb-12 flex items-center">
        <div class="promo-glass w-full px-5 sm:px-7 md:px-9 py-6 sm:py-8 md:py-9">
          {{-- breadcrumb + tag --}}
          <div class="flex flex-wrap items-center justify-between gap-3">
            <nav class="fx flex items-center gap-3 text-xs sm:text-sm text-white/85" style="--d:80ms">
              <a href="{{ $backHref }}" class="hover:underline flex items-center gap-1.5">
                <i class="fa-solid fa-arrow-left-long text-[11px]"></i>
                <span>Back to promos</span>
              </a>
              <span class="opacity-70">/</span>
              <span class="truncate max-w-[12rem] sm:max-w-xs">{{ $title }}</span>
            </nav>

            <div class="fx" style="--d:120ms">
              <span class="promo-badge">
                <span class="inline-block h-1.5 w-1.5 rounded-full bg-[#CF4520]"></span>
                SPECIAL OFFER
              </span>
            </div>
          </div>

          {{-- main heading + excerpt + chips (no header image) --}}
          <div class="mt-5">
            <div>
              <h1 class="fx text-3xl sm:text-4xl md:text-5xl font-extrabold leading-tight tracking-tight" style="--d:160ms">
                {{ $title }}
              </h1>

              @if($excerpt)
                <p class="fx mt-3 text-sm sm:text-base md:text-lg text-white/90 max-w-xl" style="--d:220ms">
                  {{ $excerpt }}
                </p>
              @endif

              {{-- status + dates row, with FA chevron between status and published --}}
              <div class="fx mt-4 flex flex-wrap gap-2 items-center" style="--d:260ms">
                {{-- Status --}}
                <span class="chip chip--status">
                  <i class="fa-solid {{ $statusIcon }}"></i>
                  {{ $status }}
                </span>

                {{-- Chevron separator + Published --}}
                @if($pubDate)
                  <span class="hidden sm:inline-flex items-center justify-center w-5 text-xs text-white/75">
                    <i class="fa-solid fa-chevron-right"></i>
                  </span>
                  <span class="chip chip--date">
                    <i class="fa-solid fa-calendar-check text-[var(--ap-teal)]"></i>
                    {{ $isScheduled ? 'Goes live' : 'Published' }}:
                    {{ $pubDate->isoFormat('MMM D, YYYY • h:mm A') }}
                  </span>
                @endif

                {{-- Validity --}}
                @if($validity)
                  <span class="chip chip--accent">
                    <i class="fa-solid fa-calendar-days"></i>
                    {{ $validity }}
                  </span>
                @endif

                {{-- Expiry --}}
                @if($expAt)
                  <span class="chip chip--date">
                    <i class="fa-solid fa-calendar-xmark text-[var(--ap-orange)]"></i>
                    Expires: {{ $expAt->isoFormat('MMM D, YYYY • h:mm A') }}
                  </span>
                @endif
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </section>

  {{-- =========================
       MAIN CONTENT — Poster + Body
  ========================== --}}
  <section class="bg-white pb-12 pt-6 md:pt-10">
    <div class="max-w-[1500px] mx-auto px-6 grid gap-8 lg:grid-cols-[1.25fr,1.45fr] items-start">

      {{-- LEFT: poster + status notice (poster a bit wider now) --}}
      <aside class="space-y-4">
        {{-- Poster card --}}
        <div class="rounded-2xl border border-gray-200 bg-white overflow-hidden shadow-sm">
          <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between gap-3">
            <div class="promo-poster-tag flex items-center gap-2">
              <span class="inline-block h-1.5 w-1.5 rounded-full bg-[var(--ap-teal)]"></span>
              PROMO POSTER
            </div>
            @if($validity)
              <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-gray-600">
                <i class="fa-regular fa-clock text-[var(--ap-orange)]"></i>
                {{ $validity }}
              </span>
            @endif
          </div>
          <div class="p-3">
            <button
              type="button"
              x-data
              @click="$store.lb.open('{{ $img }}', '{{ e($imgAlt) }}')"
              class="group relative block w-full"
              style="cursor: zoom-in;"
            >
              <img
                src="{{ $img }}"
                alt="{{ $imgAlt }}"
                loading="eager"
                decoding="async"
                class="w-full h-auto object-contain rounded-xl bg-slate-100 transition group-hover:scale-[1.01] group-hover:shadow-md"
                onerror="this.src='{{ asset('images/placeholder/post-wide.jpg') }}'">
              <span class="absolute bottom-3 right-3 text-[11px] font-medium px-2.5 py-1 rounded-full bg-black/70 text-white opacity-0 group-hover:opacity-100 transition">
                <i class="fa-solid fa-magnifying-glass-plus mr-1 text-[10px]"></i> Click to zoom
              </span>
            </button>
          </div>
        </div>

        {{-- Status note --}}
        @if($status !== 'Published')
          <div class="rounded-xl border px-4 py-3 text-sm
                      {{ $isExpired ? 'bg-orange-50 text-orange-900 border-orange-200' : 'bg-teal-50 text-teal-900 border-teal-200' }}">
            <strong class="font-semibold">
              <i class="fa-solid {{ $statusIcon }} mr-1"></i>{{ $status }}:
            </strong>
            @if($isScheduled && $pubDate)
              Scheduled for {{ $pubDate->isoFormat('MMM D, YYYY • h:mm A') }}.
            @endif
            @if($isExpired && $expAt)
              Ended {{ $expAt->isoFormat('MMM D, YYYY • h:mm A') }}.
            @endif
          </div>
        @endif
      </aside>

      {{-- RIGHT: body only --}}
      <article>
        <div class="rounded-2xl border border-gray-200 bg-white overflow-hidden shadow-sm">
          <div class="px-5 py-3.5 border-b border-gray-100 flex items-center gap-2">
            <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-[var(#18206b)]/10 text-[var(#18206b)]">
              <i class="fa-solid fa-gift text-xs"></i>
            </span>
            <span class="text-sm font-semibold text-[var(--ap-ink)]">
              Promo Details
            </span>
          </div>
          <div class="prose max-w-none px-5 py-6">
            {!! $bodyHtml !!}
          </div>
        </div>
      </article>
    </div>
  </section>
  

  {{-- =========================
       CTAs BELOW CONTENT (Back + Share, icon-only)
  ========================== --}}
  <section class="bg-white -mt-3 pb-12">
    <div class="max-w-[1500px] mx-auto px-6">
      <div
        x-data="shareBox({ title: @js($title), text: @js($shareSnippet), url: @js($canonicalUrl) })"
        class="rounded-2xl border border-gray-200 p-4 md:p-6 shadow-sm soft-radial"
      >
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          {{-- Back button --}}
          <a href="{{ $backHref }}"
             class="inline-flex items-center gap-2 rounded-xl border text-[var(--ap-ink)] bg-white"
             style="border-color:var(--ap-teal);padding:.6rem .9rem">
            <i class="fa-solid fa-arrow-left text-[var(--ap-teal)]"></i>
            Back to Promos
          </a>

          {{-- Icon-only share buttons --}}
          <div class="flex flex-wrap items-center gap-2">
            {{-- Native share --}}
            <button type="button"
                    @click="share()"
                    class="share-icon-btn border-[var(--ap-teal)] text-white"
                    style="background:var(--ap-teal);"
                    aria-label="Share this promo">
              <i class="fa-solid fa-share-nodes"></i>
            </button>

            {{-- Facebook --}}
            <a href="{{ $fbShare }}" target="_blank" rel="noopener"
               class="share-icon-btn border-[#1877F2] text-[#1877F2] bg-white"
               aria-label="Share on Facebook">
              <i class="fa-brands fa-facebook-f"></i>
            </a>

            {{-- WhatsApp --}}
            <a href="{{ $waShare }}" target="_blank" rel="noopener"
               class="share-icon-btn border-[#25D366] text-[#25D366] bg-white"
               aria-label="Share on WhatsApp">
              <i class="fa-brands fa-whatsapp"></i>
            </a>

            {{-- Viber --}}
            <a href="{{ $viberShare }}"
               class="share-icon-btn border-[#7360F2] text-[#7360F2] bg-white"
               aria-label="Share on Viber">
              <i class="fa-brands fa-viber"></i>
            </a>

            {{-- Copy link --}}
            <button type="button"
                    @click="copy()"
                    :disabled="copied"
                    :class="copied ? 'opacity-70 cursor-default' : ''"
                    class="share-icon-btn border-gray-300 text-gray-800 bg-white"
                    aria-label="Copy promo link">
              <i class="fa-solid fa-link"></i>
            </button>
          </div>
        </div>

        {{-- Toast --}}
        <div class="pointer-events-none fixed inset-x-0 bottom-6 flex justify-center" x-cloak>
          <div x-show="copied" x-transition.duration.200ms
               class="rounded-lg bg-gray-900 text-white text-sm px-3 py-2 shadow-lg">
            Copied!
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- =========================
       OTHER PROMOS
  ========================== --}}
  @php
    // Other promos (normalize + exclude current + sort by effective publish)
    $othersIn = $others ?? ($related ?? collect());

    if ($othersIn instanceof \Illuminate\Contracts\Pagination\Paginator ||
        $othersIn instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator) {
      $othersIn = $othersIn->getCollection();
    }

    $currId   = is_object($promo) ? ($promo->id ?? null) : ($promo['id'] ?? null);
    $currSlug = is_object($promo) ? ($promo->slug ?? null) : ($promo['slug'] ?? null);

    $others = collect($othersIn)
      ->filter(function ($rp) use ($currId, $currSlug) {
        $rid = is_object($rp) ? ($rp->id   ?? null) : ($rp['id']   ?? null);
        $rsl = is_object($rp) ? ($rp->slug ?? null) : ($rp['slug'] ?? null);
        if ($currId && $rid && $rid == $currId) return false;
        if ($currSlug && $rsl && $rsl === $currSlug) return false;
        return true;
      })
      ->map(function ($rp) use ($toPH) {
        $title = is_object($rp) ? ($rp->title ?? 'Untitled') : ($rp['title'] ?? 'Untitled');
        $slug  = is_object($rp) ? ($rp->slug  ?? Str::slug($title)) : ($rp['slug'] ?? Str::slug($title));

        $sched = is_object($rp) ? ($rp->scheduled_publish_date ?? ($rp->starts_at ?? null))
                                : ($rp['scheduled_publish_date'] ?? ($rp['starts_at'] ?? null));
        $pub   = is_object($rp) ? ($rp->published_at ?? null) : ($rp['published_at'] ?? null);
        $eff   = $toPH(($sched && ($toPH($sched)?->isFuture())) ? $sched : ($pub ?: $sched));

        return (object)[
          'title' => $title,
          'slug'  => $slug,
          'ts'    => $eff ? $eff->getTimestamp() : 0,
          'eff'   => $eff,
        ];
      })
      ->sortByDesc('ts')
      ->values();
  @endphp

  @if($others->count())
    <section class="bg-gray-50 py-10 md:py-14 overflow-hidden">
      <div class="max-w-[1500px] mx-auto px-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
          <div>
            <p class="other-promo-pill inline-flex items-center gap-2">
              <span class="inline-block h-1.5 w-1.5 rounded-full bg-[var(--ap-orange)]"></span>
              More ways to save
            </p>
            <h2 class="mt-2 text-xl md:text-2xl font-extrabold text-[var(--ap-ink)]">
              Other Promos You Might Like
            </h2>
            <p class="mt-1 text-sm text-gray-600 max-w-xl">
              Explore more Astoria Palawan deals that match your travel dates and preferences.
            </p>
          </div>

          <a href="{{ $backHref }}"
             class="inline-flex items-center gap-2 rounded-full border border-[var(--ap-teal)] bg-white text-xs font-semibold px-3 py-1.5 text-[var(--ap-teal)]">
            View all promos
            <i class="fa-solid fa-arrow-right-long text-[11px]"></i>
          </a>
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-2">
          @foreach($others as $rp)
            @php
              $link = \Illuminate\Support\Facades\Route::has('promos.show')
                ? route('promos.show', ['promo' => $rp->slug])
                : url('/promos#'.$rp->slug);
              $dateLabel = $rp->eff ? $rp->eff->isoFormat('MMM D, YYYY') : null;
            @endphp
            <article class="other-promo-card">
              {{-- keep card-wide click --}}
              <a href="{{ $link }}" class="absolute inset-0 z-10" aria-label="View promo: {{ $rp->title }}"></a>

              <div class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-start gap-3 min-w-0 flex-1">
                  <span class="mt-1 inline-flex h-7 w-7 items-center justify-center rounded-full bg-[var(--ap-teal)]/10 text-[var(--ap-teal)] shrink-0">
                    <i class="fa-solid fa-ticket-simple text-xs"></i>
                  </span>
                  <div class="min-w-0">
                    <h3 class="font-semibold text-[var(--ap-ink)] text-sm sm:text-[15px] leading-snug line-clamp-2">
                      {{ $rp->title }}
                    </h3>
                    @if($dateLabel)
                      <p class="mt-1 text-[11px] text-gray-500 flex items-center gap-1">
                        <i class="fa-regular fa-calendar text-[10px]"></i>
                        Updated {{ $dateLabel }}
                      </p>
                    @endif
                  </div>
                </div>

                <div class="shrink-0 self-start sm:self-center flex flex-col items-end gap-1">
                  {{-- "View offer" is now a working link --}}
                  <a href="{{ $link }}"
                     class="relative z-20 inline-flex items-center gap-1.5 rounded-full bg-[var(--ap-teal)] text-white text-xs font-semibold px-3 py-1.5 shadow-sm hover:shadow-md">
                    View offer
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                  </a>
                  <span class="hidden sm:inline-block text-[10px] uppercase tracking-[.18em] text-[var(--ap-orange)] mt-0.5">
                    Limited-time deal
                  </span>
                </div>
              </div>
            </article>
          @endforeach
        </div>
      </div>
    </section>
  @endif

</main>

{{-- Lightbox (FA close icon) --}}
<div
  x-data
  x-show="$store.lb.opened"
  x-transition.opacity
  class="fixed inset-0 z-[100] bg-black/80 flex items-center justify-center p-4"
  style="display:none"
  aria-modal="true" role="dialog"
  @keydown.escape.window="$store.lb.close()"
  @click.self="$store.lb.close()"
>
  <figure class="relative" x-transition.scale.origin.center>
    <img
      :src="$store.lb.src"
      :alt="$store.lb.alt"
      class="max-h-[90vh] max-w-[95vw] w-auto h-auto object-contain rounded-xl shadow-2xl"
      draggable="false"
    />
    <button
      type="button"
      class="absolute -top-3 -right-3 md:top-3 md:right-3 inline-flex items-center gap-2 rounded-full bg-white/90 hover:bg-white text-gray-900 px-3 py-1.5 text-sm font-semibold shadow"
      @click="$store.lb.close()"
      aria-label="Close"
    >
      Close
      <i class="fa-solid fa-xmark"></i>
    </button>
  </figure>
</div>

<script>
  document.addEventListener('alpine:init', () => {
    Alpine.store('lb', {
      opened: false, src: null, alt: '',
      open(src, alt = 'Image') {
        this.src = src;
        this.alt = alt;
        this.opened = true;
        document.documentElement.style.overflow = 'hidden';
      },
      close() {
        this.opened = false;
        document.documentElement.style.overflow = '';
      }
    });
  });
</script>

<script>
  document.addEventListener('alpine:init', () => {
    Alpine.data('shareBox', (opts = {}) => ({
      title: opts.title || document.title,
      text:  opts.text  || '',
      url:   opts.url   || window.location.href,
      copied: false,
      share() {
        if (navigator.share) {
          navigator.share({ title: this.title, text: this.text, url: this.url }).catch(() => {});
        } else {
          const fb = 'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(this.url);
          window.open(fb, '_blank', 'noopener');
        }
      },
      async copy() {
        try {
          if (navigator.clipboard && window.isSecureContext) {
            await navigator.clipboard.writeText(this.url);
          } else {
            const ta = document.createElement('textarea');
            ta.value = this.url;
            ta.setAttribute('readonly','');
            ta.style.position='fixed';
            ta.style.top='-1000px';
            document.body.appendChild(ta);
            ta.select();
            document.execCommand('copy');
            document.body.removeChild(ta);
          }
          this.copied = true;
          setTimeout(() => (this.copied = false), 1600);
        } catch (e) {
          console.error('Copy failed', e);
        }
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
