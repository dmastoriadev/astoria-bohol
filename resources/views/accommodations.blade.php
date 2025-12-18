@extends('layouts.app')

@section('title', 'Accommodations | Astoria Bohol')

@section('content')

{{-- === ACCOMMODATIONS HERO: Angled Split + Header Vectors & Slide FX === --}}
<section
  id="rooms-hero-angled-left-55"
  class="relative w-full overflow-hidden text-[#25282a] bg-white"
  aria-label="About Astoria Hotels and Resorts hero banner"
  x-data="{ loaded:false }"
  x-init="requestAnimationFrame(()=>{ loaded=true })"
  :class="loaded ? 'is-in' : ''"
>
  {{-- DESKTOP BG (angled image) --}}
  <div class="absolute inset-0 right-clip hidden lg:block">
    <img
      src="{{ asset('images/Rooms/rooms-header.webp') }}"
      alt="Astoria Hotels and Resorts"
      class="absolute inset-0 h-full w-full object-cover bg-shift will-change-transform transition-transform duration-700 lg:hover:scale-[1.03]"
      loading="eager"
      fetchpriority="high"
    />
    <div
      class="absolute inset-0 pointer-events-none"
      style="background: linear-gradient(135deg, rgba(0,0,0,0.35) 0%, rgba(0,0,0,0.0) 45%);"
    ></div>
  </div>

  {{-- TOP-LEFT HEADER VECTOR (all breakpoints, under content, animated) --}}
  <div aria-hidden="true" class="pointer-events-none absolute inset-0 z-[6]">
    <div
      class="vector-top-fx absolute
             top-[60px] sm:top-[50px] md:top-[60px] lg:top-[90px]
             left-3"
    >
      <img
        src="{{ asset('images/header-top.webp') }}"
        alt=""
        class="w-12 sm:w-40 md:w-24 lg:w-36
               max-w-none select-none
               -translate-x-12%] sm:-translate-x-[10%] md:-translate-x-[8%] lg:-translate-x-[5%]"
        loading="lazy"
        decoding="async"
        draggable="false"
      >
    </div>
  </div>

  {{-- BOTTOM-LEFT VECTOR: DESKTOP VERSION (behind content, animated) --}}
  <div aria-hidden="true" class="pointer-events-none absolute inset-0 z-[1] hidden lg:block">
    <div class="vector-btm-desktop-fx absolute left-3 bottom-5">
      <img
        src="{{ asset('images/header-btm.webp') }}"
        alt=""
        class="w-12 lg:w-56
               max-w-none select-none
               -translate-x-[4%]
               lg:-translate-y-[6%] xl:-translate-y-[10%] 2xl:-translate-y-[0%]"
        loading="lazy"
        decoding="async"
        draggable="false"
      >
    </div>
  </div>

  {{-- MOBILE & TABLET (3 rows: text / vector / image) --}}
  <div class="tablet-stack relative pt-[120px] z-10 grid lg:hidden min-h-[860px] md:min-h-[900px] grid-rows-[auto_auto_minmax(0,1fr)]">
    {{-- Row 1: Content (centered, with bottom space reserved) --}}
    <div class="relative px-6 pt-10 pb-10 sm:pb-12 md:pb-14 flex items-center justify-center overflow-visible">
      <div class="w-full max-w-3xl text-center relative z-10">
        {{-- SUB HEADING (orange) --}}
        <p class="fx fx-right text-sm sm:text-base font-semibold tracking-[.28em] uppercase" style="--d:40ms; color:#3F2021;">
         OLD WORLD BEAUTY AND NOSTALGIA

        </p>

        {{-- HEADER (teal) --}}
        <h1 class="fx fx-right font-semibold leading-tight tracking-tight text-3xl sm:text-4xl md:text-5xl mt-2" style="--d:100ms">
          <span class="bg-clip-text text-transparent bg-gradient-to-r from-[#CF4520] via-[#CF4520] to-[#CF4520]/85">
           ACCOMMODATIONS


          </span>
        </h1>

        {{-- Divider (orange) --}}
        <div
          class="fx fx-right mx-auto my-5 h-0.5 w-16 rounded-full"
          style="--d:160ms; background-color:#3F2021;"
          aria-hidden="true"
        ></div>

        {{-- DESCRIPTION (dark) --}}
        <p class="fx fx-right text-base sm:text-lg md:text-xl font-medium text-[#25282a]/90" style="--d:220ms">
        Beautifully intricate details in every corner



        </p>
      </div>
    </div>

    {{-- Row 2: Vector row (sits at the seam, overlaps slightly using -mt) --}}
    <div class="relative -mt-6 sm:-mt-8 md:-mt-10 pb-2 flex items-start justify-start">
      <div class="vector-btm-mobile-fx pl-0">
        <img
          src="{{ asset('images/header-btm.webp') }}"
          alt=""
          class="w-28 sm:w-32 md:w-40
                 max-w-none select-none
                 -translate-x-[0%]
                 opacity-95"
          loading="lazy"
          decoding="async"
          draggable="false"
        >
      </div>
    </div>

    {{-- Row 3: Image (full width, no gaps) --}}
    <div class="relative">
      <img
        src="{{ asset('images/Rooms/rooms-header.webp') }}"
        alt="Astoria Bohol"
        class="absolute inset-0 h-full w-full object-cover"
        loading="lazy"
      />
    </div>
  </div>

  {{-- DESKTOP (lg+): angled split, viewport height --}}
  <div class="desktop-angled relative z-10 left-clip hidden lg:flex min-h-[100svh] items-center">
    <div class="content-wrap w-full text-left px-6 lg:pl-24 lg:pr-10">
      <div class="max-w-2xl">
        {{-- SUB HEADING (orange) --}}
        <p class="fx fx-right text-sm md:text-base font-semibold tracking-[.28em] uppercase" style="--d:300ms; color:#3F2021;">
        OLD WORLD BEAUTY AND NOSTALGIA




        </p>

        {{-- HEADER (teal) --}}
        <h1 class="fx fx-right font-semibold leading-tight tracking-tight text-5xl lg:text-6xl mt-2" style="--d:360ms">
          <span class="bg-clip-text text-transparent bg-gradient-to-r from-[#CF4520] via-[#CF4520] to-[#CF4520]/80">
         ACCOMMODATIONS

          </span>
        </h1>

        {{-- Divider (orange) --}}
        <div
          class="fx fx-right my-5 h-1 w-16 rounded-full"
          style="--d:440ms; background-color:#3F2021;"
          aria-hidden="true"
        ></div>

        {{-- DESCRIPTION (dark) --}}
        <p class="fx fx-right font-medium pr-[100px] text-[#25282a]/90 text-2xl" style="--d:520ms">
        Beautifully intricate details in every corner

        </p>
      </div>
    </div>

    <span aria-hidden="true" class="pointer-events-none absolute inset-0 ring-1 ring-inset ring-white/10"></span>
  </div>

  <style>
    /* Default: no clipping for non-desktop */
    #rooms-hero-angled-left-55 .left-clip,
    #rooms-hero-angled-left-55 .right-clip {
      clip-path: none;
      -webkit-clip-path: none;
    }

    /* Desktop (lg+): 55° seam */
    @media (min-width: 1024px){
      #rooms-hero-angled-left-55 .left-clip{
        clip-path: polygon(0% 0%, 70% 0%, 30% 100%, 0% 100%);
        -webkit-clip-path: polygon(0% 0%, 70% 0%, 30% 100%, 0% 100%);
      }
      #rooms-hero-angled-left-55 .right-clip{
        clip-path: polygon(70% 0%, 100% 0%, 100% 100%, 30% 100%);
        -webkit-clip-path: polygon(70% 0%, 100% 0%, 100% 100%, 30% 100%);
      }

    #rooms-hero-angled-left-55 .right-clip > img.bg-shift{
        left: 10vw !important;
        right: auto !important;
        width: calc(100% + 12vw) !important;
        height: 100% !important;
        object-fit: cover !important;
        object-position: left center !important;
        transform: translateZ(0);
      }
    }

    /* iPad Pro/tablet landscapes: force tablet stack (show stack, hide desktop) */
    @media (min-width:1024px) and (max-width:1368px) and (hover:none) and (pointer:coarse) {
      #rooms-hero-angled-left-55 .tablet-stack {
        display: grid !important;
        min-height: 900px !important;
        padding-top: 32px !important;
      }
      #rooms-hero-angled-left-55 .desktop-angled,
      #rooms-hero-angled-left-55 .right-clip {
        display: none !important;
      }
      #rooms-hero-angled-left-55 .left-clip,
      #rooms-hero-angled-left-55 .right-clip {
        clip-path: none !important;
        -webkit-clip-path: none !important;
      }
    }
    @media (min-width:1024px) and (max-width:1368px) and (aspect-ratio: 3/4),
           (min-width:1194px) and (max-width:1368px) and (aspect-ratio: 4/3) {
      #rooms-hero-angled-left-55 .tablet-stack {
        display: grid !important;
        min-height: 900px !important;
        padding-top: 32px !important;
      }
      #rooms-hero-angled-left-55 .desktop-angled,
      #rooms-hero-angled-left-55 .right-clip {
        display: none !important;
      }
      #rooms-hero-angled-left-55 .left-clip,
      #rooms-hero-angled-left-55 .right-clip {
        clip-path: none !important;
        -webkit-clip-path: none !important;
      }
    }

    /* Entrance FX for text */
    #rooms-hero-angled-left-55 .fx{
      opacity: .001;
      transform: translateX(-14px);
      will-change: transform, opacity;
      transition:
        opacity 520ms ease,
        transform 640ms cubic-bezier(.22,1,.36,1);
      transition-delay: var(--d, 0ms);
    }
    #rooms-hero-angled-left-55.is-in .fx{
      opacity: 1;
      transform: translateX(0);
    }

    /* Slide-in FX for vectors (top + bottom) – from left to right, slower */
    #rooms-hero-angled-left-55 .vector-top-fx,
    #rooms-hero-angled-left-55 .vector-btm-mobile-fx,
    #rooms-hero-angled-left-55 .vector-btm-desktop-fx{
      opacity: 0;
      transform: translateX(-32px);
      will-change: transform, opacity;
      transition:
        opacity 700ms ease,
        transform 950ms cubic-bezier(.16,1,.3,1);
    }

    #rooms-hero-angled-left-55.is-in .vector-top-fx{
      opacity: 1;
      transform: translateX(0);
      transition-delay: 260ms;
    }

    #rooms-hero-angled-left-55.is-in .vector-btm-mobile-fx{
      opacity: 1;
      transform: translateX(0);
      transition-delay: 320ms;
    }

    #rooms-hero-angled-left-55.is-in .vector-btm-desktop-fx{
      opacity: 1;
      transform: translateX(0);
      transition-delay: 320ms;
    }

    /* Extra safety: very small phones (optional tweak if needed) */
    @media (max-width: 400px){
      #rooms-hero-angled-left-55 .vector-btm-mobile-fx img{
        width: 6rem;
      }
    }
    @media (max-width: 360px){
      #rooms-hero-angled-left-55 .vector-btm-mobile-fx{
        display: none;
      }
    }

    @media (prefers-reduced-motion: reduce){
      #rooms-hero-angled-left-55 .fx{
        opacity: 1 !important;
        transform: none !important;
        transition: none !important;
      }
      #rooms-hero-angled-left-55 img{
        transition: none !important;
        transform: none !important;
      }
      #rooms-hero-angled-left-55 .vector-top-fx,
      #rooms-hero-angled-left-55 .vector-btm-mobile-fx,
      #rooms-hero-angled-left-55 .vector-btm-desktop-fx{
        opacity: 1 !important;
        transform: none !important;
        transition: none !important;
      }
    }
  </style>
</section>



{{-- ===== Resort Living That Feels Like Home • Rooms & Villas (centered intro) ===== --}}
<section id="rooms-and-villas" class="relative w-full bg-white text-[#25282a]" aria-labelledby="rooms-title">
  <div class="mx-auto max-w-[1200px] px-4 sm:px-6 lg:px-8 py-8 md:py-12">
    <div class="text-center ev-fade" data-ev-fade style="--ev-delay:60ms;">
      <p class="text-sm md:text-base tracking-[.22em] uppercase text-[#3F2021] font-bold">
      A ROMANCE OF STYLE, COMFORT AND SCENERY



      </p>
      <h2 id="rooms-title" class="mt-1 text-3xl sm:text-4xl md:text-5xl font-semibold leading-[1.05] tracking-tight">
        <span class="bg-clip-text text-transparent bg-[#CF4520]">
         ROOMS

        </span>
      </h2>
      <div class="mx-auto mt-3 h-1 w-20 rounded-full" style="background-color:#3F2021;"></div>
      <p class="mt-5 text-base sm:text-lg md:text-xl text-[#25282a]/90">
       Our 8 very capacious rooms evoke a blend of modern Filipino and Boholano aesthetics designed by Ed Gallego of Gallego Architects. Each of our room’s interiors is brought to life by renowned designers Cynthia & Ivy Almario, made to be captivating against the mesmerizing Mindanao Sea.


      </p>
    </div>
  </div>
</section>

<style>
  .ev-fade{opacity:0;transform:translateZ(0);transition:opacity .6s ease;transition-delay:var(--ev-delay,0ms);will-change:opacity}
  .ev-fade.is-in{opacity:1}
  [x-cloak]{display:none!important}
  @media (prefers-reduced-motion: reduce){
    .ev-fade{ opacity:1 !important; transition:none !important; }
  }
</style>
<script>
  (function () {
    const scope = document.getElementById('rooms-and-villas') || document;
    const els = scope.querySelectorAll('[data-ev-fade]');
    if (!('IntersectionObserver' in window)) { els.forEach(e => e.classList.add('is-in')); return; }
    const io = new IntersectionObserver((entries, o) => {
      entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('is-in'); o.unobserve(e.target); }});
    }, { threshold: 0.15, rootMargin: '0px 0px -10% 0px' });
    els.forEach(e => io.observe(e));
  })();
</script>



{{-- ===== ROOMS & VILLAS • Cards (no filters). Mobile/Tablet: overlay always visible. Desktop: hover reveal with staggered entrance. ===== --}}
@php
  // Replace with real data/links/images as needed
  $rooms = [
    [
      'title' => 'Deluxe Room',
      'image' => asset('images/Rooms/deluxe1.webp'),
      'url'   => url('/accommodations/deluxe-room'),
      'desc'  => 'Perfect for small groups and families, our Deluxe Rooms’ interiors strike the balance between classic and modern style.',
    ],
    [
      'title' => 'Luxury Room',
      'image' => asset('images/Rooms/luxury1.webp'),
      'url'   => url('/accommodations/luxury-room'),
      'desc'  => 'With a touch of flair in each of the Luxury Rooms, the rich culture of Bohol is reflected in every detail of our sophisticated spaces.',
    ],
  ];
@endphp

<section id="rooms-grid" class="relative w-full mb-20 bg-white text-[#25282a]" aria-labelledby="rooms-grid-title">
  <div class="mx-auto max-w-[1600px] px-4 sm:px-6 lg:px-8 py-8 md:py-12">
    <div class="grid gap-6 sm:gap-8 grid-cols-1 md:grid-cols-2">
      @foreach ($rooms as $room)
        <article class="group relative isolate overflow-hidden rounded-2xl h-[340px] md:h-[420px] shadow-sm ring-1 ring-black/5 focus-within:ring-2 focus-within:ring-[#CF4520]">
          {{-- Background image --}}
          <img
            src="{{ $room['image'] }}"
            alt="{{ $room['title'] }}"
            class="absolute inset-0 h-full w-full object-cover transition-transform duration-500 lg:group-hover:scale-105"
            loading="lazy"
            decoding="async"
          >

          {{-- Subtle gradient --}}
          <div class="pointer-events-none absolute inset-0 bg-gradient-to-b from-black/20 via-transparent to-black/60"></div>

          {{-- Desktop-only capsule (visible until hover). Hidden on mobile/tablet --}}
          <div class="absolute left-4 bottom-4 z-10 transition-opacity duration-300 hidden lg:block lg:group-hover:opacity-0 lg:group-focus-within:opacity-0">
            <span class="inline-flex items-center rounded-full bg-[#CF4520] px-3 py-1.5 text-sm lg:text-base xl:text-lg font-semibold text-white shadow-sm">
              {{ $room['title'] }}
            </span>
          </div>

          {{-- Overlay content:
               • Mobile/Tablet: visible by default
               • Desktop: hidden by default; reveals on hover/focus WITH STAGGERED ENTRANCE --}}
          <div class="absolute left-4 right-4 bottom-4 z-10
                      lg:translate-y-3 lg:opacity-0
                      lg:group-hover:translate-y-0 lg:group-hover:opacity-100
                      lg:group-focus-within:translate-y-0 lg:group-focus-within:opacity-100
                      lg:transition-all lg:duration-500">
            <h3 class="text-lg sm:text-xl md:text-2xl lg:text-3xl font-extrabold text-white drop-shadow-sm
                       lg:opacity-0 lg:translate-y-2 lg:transition-all lg:duration-500 lg:delay-100
                       lg:group-hover:opacity-100 lg:group-hover:translate-y-0
                       lg:group-focus-within:opacity-100 lg:group-focus-within:translate-y-0">
              {{ $room['title'] }}
            </h3>

            <p class="mt-1 text-sm sm:text-base md:text-[17px] leading-snug text-white/95 line-clamp-3
                      lg:opacity-0 lg:translate-y-2 lg:transition-all lg:duration-500 lg:delay-200
                      lg:group-hover:opacity-100 lg:group-hover:translate-y-0
                      lg:group-focus-within:opacity-100 lg:group-focus-within:translate-y-0">
              {{ $room['desc'] }}
            </p>

            <div class="mt-3
                        lg:opacity-0 lg:translate-y-2 lg:transition-all lg:duration-500 lg:delay-300
                        lg:group-hover:opacity-100 lg:group-hover:translate-y-0
                        lg:group-focus-within:opacity-100 lg:group-focus-within:translate-y-0">
              <a href="{{ $room['url'] ?: '#' }}"
                 class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-xs font-extrabold uppercase tracking-widest
                        bg-white/90 text-[#3F2021] border-[#3F2021] hover:bg-[#3F2021] hover:text-white transition"
                 aria-label="Learn more about {{ $room['title'] }}">
                Learn more
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
              </a>
            </div>
          </div>

          <a class="absolute inset-0 z-0" href="{{ $room['url'] ?: '#' }}" aria-label="Open {{ $room['title'] }}" tabindex="-1"></a>
        </article>
      @endforeach
    </div>
  </div>
</section>

{{-- ===== Full-width decorative vector (responsive) ===== --}}
<section id="footer-vector" class="relative w-full overflow-hidden" aria-hidden="true">
  <img
    src="{{ asset('images/footer-vector.webp') }}"
    alt=""
    class="block w-full h-auto select-none pointer-events-none"
    loading="lazy"
    decoding="async"
  >
</section>

@endsection
