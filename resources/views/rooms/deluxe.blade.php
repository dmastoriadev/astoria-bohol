@extends('layouts.app')

@section('title', 'Deluxe Room | Astoria Bohol')

@section('content')

<section
  id="deluxe-hero-angled-left-55"
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
        DELUXE ROOMS

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
       DELUXE ROOMS




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
    #deluxe-hero-angled-left-55 .left-clip,
    #deluxe-hero-angled-left-55 .right-clip {
      clip-path: none;
      -webkit-clip-path: none;
    }

    /* Desktop (lg+): 55° seam */
    @media (min-width: 1024px){
      #deluxe-hero-angled-left-55 .left-clip{
        clip-path: polygon(0% 0%, 70% 0%, 30% 100%, 0% 100%);
        -webkit-clip-path: polygon(0% 0%, 70% 0%, 30% 100%, 0% 100%);
      }
      #deluxe-hero-angled-left-55 .right-clip{
        clip-path: polygon(70% 0%, 100% 0%, 100% 100%, 30% 100%);
        -webkit-clip-path: polygon(70% 0%, 100% 0%, 100% 100%, 30% 100%);
      }

    #deluxe-hero-angled-left-55 .right-clip > img.bg-shift{
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
      #deluxe-hero-angled-left-55 .tablet-stack {
        display: grid !important;
        min-height: 900px !important;
        padding-top: 32px !important;
      }
      #deluxe-hero-angled-left-55 .desktop-angled,
      #deluxe-hero-angled-left-55 .right-clip {
        display: none !important;
      }
      #deluxe-hero-angled-left-55 .left-clip,
      #deluxe-hero-angled-left-55 .right-clip {
        clip-path: none !important;
        -webkit-clip-path: none !important;
      }
    }
    @media (min-width:1024px) and (max-width:1368px) and (aspect-ratio: 3/4),
           (min-width:1194px) and (max-width:1368px) and (aspect-ratio: 4/3) {
      #deluxe-hero-angled-left-55 .tablet-stack {
        display: grid !important;
        min-height: 900px !important;
        padding-top: 32px !important;
      }
      #deluxe-hero-angled-left-55 .desktop-angled,
      #deluxe-hero-angled-left-55 .right-clip {
        display: none !important;
      }
      #deluxe-hero-angled-left-55 .left-clip,
      #deluxe-hero-angled-left-55 .right-clip {
        clip-path: none !important;
        -webkit-clip-path: none !important;
      }
    }

    /* Entrance FX for text */
    #deluxe-hero-angled-left-55 .fx{
      opacity: .001;
      transform: translateX(-14px);
      will-change: transform, opacity;
      transition:
        opacity 520ms ease,
        transform 640ms cubic-bezier(.22,1,.36,1);
      transition-delay: var(--d, 0ms);
    }
    #deluxe-hero-angled-left-55.is-in .fx{
      opacity: 1;
      transform: translateX(0);
    }

    /* Slide-in FX for vectors (top + bottom) – from left to right, slower */
    #deluxe-hero-angled-left-55 .vector-top-fx,
    #deluxe-hero-angled-left-55 .vector-btm-mobile-fx,
    #deluxe-hero-angled-left-55 .vector-btm-desktop-fx{
      opacity: 0;
      transform: translateX(-32px);
      will-change: transform, opacity;
      transition:
        opacity 700ms ease,
        transform 950ms cubic-bezier(.16,1,.3,1);
    }

    #deluxe-hero-angled-left-55.is-in .vector-top-fx{
      opacity: 1;
      transform: translateX(0);
      transition-delay: 260ms;
    }

    #deluxe-hero-angled-left-55.is-in .vector-btm-mobile-fx{
      opacity: 1;
      transform: translateX(0);
      transition-delay: 320ms;
    }

    #deluxe-hero-angled-left-55.is-in .vector-btm-desktop-fx{
      opacity: 1;
      transform: translateX(0);
      transition-delay: 320ms;
    }

    /* Extra safety: very small phones (optional tweak if needed) */
    @media (max-width: 400px){
      #deluxe-hero-angled-left-55 .vector-btm-mobile-fx img{
        width: 6rem;
      }
    }
    @media (max-width: 360px){
      #deluxe-hero-angled-left-55 .vector-btm-mobile-fx{
        display: none;
      }
    }

    @media (prefers-reduced-motion: reduce){
      #deluxe-hero-angled-left-55 .fx{
        opacity: 1 !important;
        transform: none !important;
        transition: none !important;
      }
      #deluxe-hero-angled-left-55 img{
        transition: none !important;
        transform: none !important;
      }
      #deluxe-hero-angled-left-55 .vector-top-fx,
      #deluxe-hero-angled-left-55 .vector-btm-mobile-fx,
      #deluxe-hero-angled-left-55 .vector-btm-desktop-fx{
        opacity: 1 !important;
        transform: none !important;
        transition: none !important;
      }
    }
  </style>
</section>


{{-- ===== Accommodations Tabs — Full width, no scroll, mobile shows all ===== --}}
@php
  $tabs = [
    ['label' => 'DELUXE ROOM',   'href' => route('deluxe'),        'active' => request()->routeIs('deluxe*')],
    ['label' => 'LUXURY ROOM',  'href' => route('luxury'),       'active' => request()->routeIs('luxury*')],
    ['label' => 'All',           'href' => route('accommodations'),'active' => request()->routeIs('accommodations*')],
  ];
@endphp

<section class="w-full pb-5 sm:pb-5 md:pb-5 lg:pb-0 bg-[#CF4520] text-white" aria-label="Accommodations tabs">
  <nav class="mx-auto w-full max-w-[1600px] px-4">
    {{-- Mobile/Tablet: 2 cols (last tab spans 2 = full width); Desktop: 5 equal cols --}}
    <div class="grid grid-cols-2 xl:grid-cols-3 gap-0">
      @foreach ($tabs as $t)
        @php
          $isLast = $loop->last; // "All" tab
        @endphp
        <a
          href="{{ $t['href'] }}"
          class="{{ \Illuminate\Support\Arr::toCssClasses([
            // layout
            'min-h-[50px] flex items-center justify-center px-4 sm:px-5 md:px-8 text-center',
            // full-width last tab on mobile/tablet; equal width on desktop
            $isLast ? 'col-span-2 xl:col-span-1' : 'col-span-1',
            // typography
            'uppercase font-semibold tracking-wide text-sm md:text-[0.95rem]',
            // separators
            'border border-[#E6E7E8]',
            // colors
            $t['active'] ? 'bg-[#3F2021]' : 'bg-transparent hover:bg-[#3F2021]',
            // a11y focus ring
            'transition-colors duration-150 focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-[#18206b]',
          ]) }}"
          {!! $t['active'] ? 'aria-current="page"' : '' !!}
        >
          {{ $t['label'] }}
        </a>
      @endforeach
    </div>
  </nav>
</section>


{{-- ===================== DELUXE ROOM  ===================== --}}
@php
  // Replace with your real image paths
  $gallery = [
    ['src' => asset('images/Rooms/deluxe1.webp'), 'alt' => 'Deluxe Room'],
    ['src' => asset('images/Rooms/deluxe2.webp'), 'alt' => 'Deluxe Room'],
    ['src' => asset('images/Rooms/deluxe3.webp'), 'alt' => 'Deluxe Room'],
    ['src' => asset('images/Rooms/deluxe4.webp'), 'alt' => 'Deluxe Room'],
    ['src' => asset('images/Rooms/deluxe5.webp'), 'alt' => 'Deluxe Room'],
  ];
  $bookingUrl = '/contact-us';
@endphp

<section id="deluxe"
  class="relative bg-white text-[#25282a]"
  aria-labelledby="sp-title"
  x-data="{
    idx: 0,
    lightbox: false,
    slides: @js($gallery),
    next(){ this.idx = (this.idx + 1) % this.slides.length },
    prev(){ this.idx = (this.idx - 1 + this.slides.length) % this.slides.length },
    set(i){ this.idx = i },
  }"
  @keydown.arrow-right.window="lightbox && next()"
  @keydown.arrow-left.window="lightbox && prev()"
  @keydown.escape.window="lightbox = false"
>
  <div class="mx-auto max-w-[1600px] px-4 sm:px-6 lg:px-8 py-10 sm:py-12 md:py-16">
    <img
      src="{{ asset('images/header-top.webp') }}"
      alt=""
      aria-hidden="true"
      class="pointer-events-none select-none absolute -top-6 pt-6 sm:pt-2 md:pt-5 -right-3 sm:-top-10 sm:-right-10 md:-top-5 md:-right-10 
             w-24 sm:w-36 md:w-48 lg:w-46 xl:w-48 opacity-90 -z-0" />

    {{-- Heading --}}
    <div class="max-w-3xl">
      <p class="text-[12px] sm:text-sm tracking-[.28em] uppercase text-[#CF4520] font-semibold">ROOMS</p>
      <div class="flex items-end gap-4 mb-5 mt-1">
        {{-- Desktop-only bump --}}
        <h2 id="sp-title" class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-semibold tracking-tight text-[#3F2021]">
          Deluxe Room
        </h2>
      </div>
      <span class="h-1 w-24 lg:w-28 rounded-full bg-[#CF4520] mb-2 hidden sm:block"></span>
    </div>

    {{-- Content --}}
    <div class="mt-8 grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8">
      {{-- Gallery (Left) --}}
      <div class="lg:col-span-7">
        {{-- Main image + nav buttons --}}
        <div class="relative overflow-hidden rounded-2xl border border-gray-200 shadow-sm">
          <div class="relative aspect-[4/3] md:aspect-[16/10] w-full group">
            <img
              :src="slides[idx].src"
              :alt="slides[idx].alt"
              class="absolute inset-0 h-full w-full object-cover will-change-transform transition-transform duration-[650ms] ease-[cubic-bezier(.22,1,.36,1)] group-hover:scale-[1.03] cursor-zoom-in"
              @click="lightbox = true"
              loading="lazy" decoding="async"
            />

            {{-- Left/Right nav --}}
            <button type="button" @click.stop="prev()" aria-label="Previous image"
              class="absolute left-3 top-1/2 -translate-y-1/2 w-12 h-12 grid place-items-center rounded-full
                     bg-white/90 text-gray-800 hover:bg-white shadow-md ring-1 ring-black/10
                     focus:outline-none focus-visible:ring-4 focus-visible:ring-[#CF4520]/40">
              <i class="fa-solid fa-chevron-left text-lg"></i>
            </button>

            <button type="button" @click.stop="next()" aria-label="Next image"
              class="absolute right-3 top-1/2 -translate-y-1/2 w-12 h-12 grid place-items-center rounded-full
                     bg-white/90 text-gray-800 hover:bg-white shadow-md ring-1 ring-black/10
                     focus:outline-none focus-visible:ring-4 focus-visible:ring-[#CF4520]/40">
              <i class="fa-solid fa-chevron-right text-lg"></i>
            </button>

            {{-- index pill --}}
            <div class="absolute left-3 bottom-3 inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-sm font-semibold bg-white/90 text-gray-800 shadow">
              <i class="fa-regular fa-image text-base"></i>
              <span x-text="(idx+1) + ' / ' + slides.length"></span>
            </div>
          </div>
        </div>

        {{-- Thumbnails --}}
        <div class="mt-3 grid grid-cols-5 gap-2 sm:gap-3">
          <template x-for="(s,i) in slides" :key="i">
            <button type="button" @click="set(i)"
              :aria-label="'Show image ' + (i+1)"
              class="group relative rounded-xl overflow-hidden border"
              :class="i===idx ? 'border-[#CF4520]' : 'border-transparent hover:border-gray-200'">
              <span class="absolute inset-0 ring-2 rounded-xl transition"
                    :class="i===idx ? 'ring-[#CF4520]' : 'ring-transparent group-hover:ring-black/5'"></span>
              <img :src="s.src" :alt="s.alt" class="h-16 sm:h-20 w-full object-cover">
            </button>
          </template>
        </div>
      </div>

      {{-- Details (Right) --}}
      <div class="lg:col-span-5">
        <div class="h-full rounded-2xl border border-gray-200 bg-white p-6 md:p-7 lg:p-9 shadow-sm">
          {{-- Quick facts badges --}}
          <div class="flex flex-wrap gap-2">
            <span class="inline-flex items-center gap-1.5 text-xs font-semibold rounded-full px-2.5 py-1 bg-[#3F2021] text-[#E6E7E8] lg:gap-2 lg:text-base lg:px-3.5 lg:py-1.5">
              <i class="fa-solid fa-bed lg:text-lg"></i>1 Queen bed
            </span>
          </div>

          {{-- Policies (updated) --}}
            <p class="mt-4 text-[14.5px] leading-7 lg:text-lg lg:leading-8">
              Perfect for small groups and families, our Deluxe Rooms’ interiors strike the balance between classic and modern style.
            </p>

            <ul class="mt-4 space-y-2 text-[14.5px] leading-7 lg:text-lg lg:leading-8 lg:space-y-3">
              <li class="flex items-start gap-2 lg:gap-3">
                <i class="fa-regular fa-clock mt-1 text-[#3F2021] text-base lg:text-lg"></i>
                <span>Check-in time is <strong>4:00 PM</strong>; Check-out time is <strong>11:00 AM</strong>.</span>
              </li>
              <li class="flex items-start gap-2 lg:gap-3">
                <i class="fa-solid fa-children mt-1 text-[#3F2021] text-base lg:text-lg"></i>
                <span>Only two (2) children aged eleven (11) years and below are free of charge on accommodation, excluding set breakfast.</span>
              </li>
              <li class="flex items-start gap-2 lg:gap-3">
                <i class="fa-solid fa-receipt mt-1 text-[#3F2021] text-base lg:text-lg"></i>
                <span>Room rates are inclusive of applicable taxes.</span>
              </li>
              <li class="flex items-start gap-2 lg:gap-3">
                <i class="fa-solid fa-shuttle-van mt-1 text-[#3F2021] text-base lg:text-lg"></i>
                <span>Roundtrip transfer charge excludes terminal and environmental fees and porterage.</span>
              </li>
            </ul>

            {{-- Room Features --}}
            <h3 class="mt-6 text-lg font-bold text-[#3F2021] lg:mt-8 lg:text-2xl lg:font-semibold">
              Room Features
            </h3>

            <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-2.5 text-[15px] lg:gap-3.5 lg:text-lg">
              <div class="flex items-center gap-2 lg:gap-3">
                <i class="fa-solid fa-shower text-[#3F2021] w-4 lg:w-5 text-center"></i>
                <span>Rainshower and bathtub</span>
              </div>

              <div class="flex items-center gap-2 lg:gap-3">
                <i class="fa-solid fa-snowflake text-[#3F2021] w-4 lg:w-5 text-center"></i>
                <span>Refrigerator</span>
              </div>

              <div class="flex items-center gap-2 lg:gap-3">
                <i class="fa-solid fa-wifi text-[#3F2021] w-4 lg:w-5 text-center"></i>
                <span>Wi-Fi</span>
              </div>

              <div class="flex items-center gap-2 lg:gap-3">
                <i class="fa-solid fa-lock text-[#3F2021] w-4 lg:w-5 text-center"></i>
                <span>Electronic safe</span>
              </div>

              <div class="flex items-center gap-2 lg:gap-3">
                <i class="fa-solid fa-bath text-[#3F2021] w-4 lg:w-5 text-center"></i>
                <span>Complete bathroom amenities</span>
              </div>

              <div class="flex items-center gap-2 lg:gap-3">
                <i class="fa-solid fa-mug-saucer text-[#3F2021] w-4 lg:w-5 text-center"></i>
                <span>Coffee and tea sets</span>
              </div>

              <div class="flex items-center gap-2 lg:gap-3">
                <i class="fa-solid fa-tv text-[#3F2021] w-4 lg:w-5 text-center"></i>
                <span>Smart TV with Netflix</span>
              </div>
            </div>

            {{-- Room Service --}}
            <h3 class="mt-6 text-lg font-bold text-[#3F2021] lg:mt-8 lg:text-2xl lg:font-semibold">
              Room Service
            </h3>
            <p class="mt-2 text-[14.5px] leading-7 lg:text-lg lg:leading-8">
              6:30 AM to 9:30 PM
            </p>



          {{-- CTA --}}
          <div class="mt-7 lg:mt-9 flex flex-wrap items-center gap-3 lg:gap-4">
            <a href="{{ $bookingUrl }}" target="_blank" rel="noopener"
               class="inline-flex items-center justify-center gap-2 rounded-xl px-6 py-3 text-white font-bold bg-[#3F2021] hover:brightness-110 shadow
                      focus:outline-none focus-visible:ring-4 focus-visible:ring-[#3F2021]/40 uppercase tracking-wider text-sm lg:gap-3 lg:px-7 lg:py-3.5 lg:text-base">
              <i class="fa-solid fa-calendar-check text-base lg:text-lg"></i> Contact Us
            </a>
            <a href="{{ route('accommodations') }}"
               class="inline-flex items-center justify-center gap-2 rounded-xl px-5 py-3 text-[##3F2021] font-bold ring-2 ring-[#3F2021] hover:bg-[#3F2021]/5
                      focus:outline-none focus-visible:ring-4 focus-visible:ring-[#3F2021]/40 uppercase tracking-wider text-sm lg:gap-3 lg:px-6 lg:py-3.5 lg:text-base">
              View All Rooms
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

 {{-- Lightbox --}}
<div x-show="lightbox"
     x-transition.opacity
     x-cloak
     class="fixed inset-0 z-[9999] bg-black/80 backdrop-blur-sm"
     @keydown.escape.window="lightbox = false"
     role="dialog" aria-modal="true" aria-label="Image lightbox">
  
  {{-- Clickable backdrop (overlay) --}}
  <div class="absolute inset-0" @click="lightbox = false"></div>

  {{-- Modal content wrapper (ignore clicks so backdrop can receive them) --}}
  <div class="relative z-10 flex min-h-full items-center justify-center p-4 pointer-events-none">
    <div class="w-full max-w-6xl pointer-events-auto">
      <div class="relative">
        <img :src="slides[idx].src" :alt="slides[idx].alt" class="w-full h-auto rounded-xl shadow-2xl">

        {{-- Controls --}}
        <button @click.stop="prev()"
                class="absolute left-2 top-1/2 -translate-y-1/2 w-10 h-10 sm:w-11 sm:h-11 grid place-items-center rounded-full
                       bg-white/90 text-gray-800 hover:bg-white shadow ring-1 ring-black/10
                       focus:outline-none focus-visible:ring-4 focus-visible:ring-[#CF4520]/40"
                aria-label="Previous image">
          <i class="fa-solid fa-chevron-left text-base sm:text-lg"></i>
        </button>

        <button @click.stop="next()"
                class="absolute right-2 top-1/2 -translate-y-1/2 w-10 h-10 sm:w-11 sm:h-11 grid place-items-center rounded-full
                       bg-white/90 text-gray-800 hover:bg-white shadow ring-1 ring-black/10
                       focus:outline-none focus-visible:ring-4 focus-visible:ring-[#CF4520]/40"
                aria-label="Next image">
          <i class="fa-solid fa-chevron-right text-base sm:text-lg"></i>
        </button>
        
        {{-- Close (circle) --}}
        <button @click="lightbox = false"
                class="absolute -top-4 -right-4 w-11 h-11 grid place-items-center rounded-full
                       bg-white/95 text-gray-900 shadow hover:bg-white ring-1 ring-black/10
                       focus:outline-none focus-visible:ring-4 focus-visible:ring-[#CF4520]/40"
                aria-label="Close">
          <i class="fa-solid fa-xmark text-lg"></i>
        </button>
      </div>

      {{-- thumbs in lightbox --}}
      <div class="mt-4 hidden sm:grid grid-cols-6 md:grid-cols-8 gap-2 w-full">
        <template x-for="(s,i) in slides" :key="'lb-'+i">
          <button @click="set(i)"
                  class="relative rounded-lg overflow-hidden border"
                  :class="i===idx ? 'border-[#CF4520]' : 'border-transparent hover:border-white/40'">
            <img :src="s.src" :alt="s.alt" class="h-14 w-full object-cover">
            <span class="absolute inset-0 ring-2 rounded-lg" :class="i===idx ? 'ring-[#CF4520]' : 'ring-transparent'"></span>
          </button>
        </template>
      </div>
    </div>
  </div>
</div>
</section>


{{-- ===== Full-width decorative vector ===== --}}
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
