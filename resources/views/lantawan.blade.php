@extends('layouts.app') 

@section('title', 'Astoria Bohol Lantawan | Astoria Bohol')

@push('head')
  {{-- Font Awesome (no SRI to avoid mismatch) --}}
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
@endpush

@section('content')

<section
  id="meetings-hero-angled-left-55"
  class="relative w-full overflow-hidden text-[#25282a] bg-white"
  aria-label="About Astoria Hotels and Resorts hero banner"
  x-data="{ loaded:false }"
  x-init="requestAnimationFrame(()=>{ loaded=true })"
  :class="loaded ? 'is-in' : ''"
>
  {{-- DESKTOP BG (angled image) --}}
  <div class="absolute inset-0 right-clip hidden lg:block">
    <img
      src="{{ asset('images/Meetings/lantawan1.webp') }}"
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
        class="w-12 sm:w-40 md:w-24 lg:w-32
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
         A private beach escape in the heart of Bohol
        </p>

        {{-- HEADER (teal) --}}
        <h1 class="fx fx-right font-semibold leading-tight tracking-tight text-3xl sm:text-4xl md:text-5xl mt-2" style="--d:100ms">
          <span class="bg-clip-text text-transparent bg-gradient-to-r from-[#CF4520] via-[#CF4520] to-[#CF4520]/85">
         ASTORIA BOHOL LANTAWAN


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
        See Baclayon in a whole new light from the comforts of our exclusive villas




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
        src="{{ asset('images/Meetings/lantawan1.webp') }}"
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
         A private beach escape in the heart of Bohol


        </p>

        {{-- HEADER (teal) --}}
        <h1 class="fx fx-right font-semibold leading-tight tracking-tight text-5xl lg:text-6xl mt-2" style="--d:360ms">
          <span class="bg-clip-text text-transparent bg-gradient-to-r from-[#CF4520] via-[#CF4520] to-[#CF4520]/80">
           ASTORIA BOHOL LANTAWAN


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
        See Baclayon in a whole new light from the comforts of our exclusive villas



        </p>
      </div>
    </div>

    <span aria-hidden="true" class="pointer-events-none absolute inset-0 ring-1 ring-inset ring-white/10"></span>
  </div>

  <style>
    /* Default: no clipping for non-desktop */
    #meetings-hero-angled-left-55 .left-clip,
    #meetings-hero-angled-left-55 .right-clip {
      clip-path: none;
      -webkit-clip-path: none;
    }

    /* Desktop (lg+): 55° seam */
    @media (min-width: 1024px){
      #meetings-hero-angled-left-55 .left-clip{
        clip-path: polygon(0% 0%, 70% 0%, 30% 100%, 0% 100%);
        -webkit-clip-path: polygon(0% 0%, 70% 0%, 30% 100%, 0% 100%);
      }
      #meetings-hero-angled-left-55 .right-clip{
        clip-path: polygon(70% 0%, 100% 0%, 100% 100%, 30% 100%);
        -webkit-clip-path: polygon(70% 0%, 100% 0%, 100% 100%, 30% 100%);
      }

    #meetings-hero-angled-left-55 .right-clip > img.bg-shift{
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
      #meetings-hero-angled-left-55 .tablet-stack {
        display: grid !important;
        min-height: 900px !important;
        padding-top: 32px !important;
      }
      #meetings-hero-angled-left-55 .desktop-angled,
      #meetings-hero-angled-left-55 .right-clip {
        display: none !important;
      }
      #meetings-hero-angled-left-55 .left-clip,
      #meetings-hero-angled-left-55 .right-clip {
        clip-path: none !important;
        -webkit-clip-path: none !important;
      }
    }
    @media (min-width:1024px) and (max-width:1368px) and (aspect-ratio: 3/4),
           (min-width:1194px) and (max-width:1368px) and (aspect-ratio: 4/3) {
      #meetings-hero-angled-left-55 .tablet-stack {
        display: grid !important;
        min-height: 900px !important;
        padding-top: 32px !important;
      }
      #meetings-hero-angled-left-55 .desktop-angled,
      #meetings-hero-angled-left-55 .right-clip {
        display: none !important;
      }
      #meetings-hero-angled-left-55 .left-clip,
      #meetings-hero-angled-left-55 .right-clip {
        clip-path: none !important;
        -webkit-clip-path: none !important;
      }
    }

    /* Entrance FX for text */
    #meetings-hero-angled-left-55 .fx{
      opacity: .001;
      transform: translateX(-14px);
      will-change: transform, opacity;
      transition:
        opacity 520ms ease,
        transform 640ms cubic-bezier(.22,1,.36,1);
      transition-delay: var(--d, 0ms);
    }
    #meetings-hero-angled-left-55.is-in .fx{
      opacity: 1;
      transform: translateX(0);
    }

    /* Slide-in FX for vectors (top + bottom) – from left to right, slower */
    #meetings-hero-angled-left-55 .vector-top-fx,
    #meetings-hero-angled-left-55 .vector-btm-mobile-fx,
    #meetings-hero-angled-left-55 .vector-btm-desktop-fx{
      opacity: 0;
      transform: translateX(-32px);
      will-change: transform, opacity;
      transition:
        opacity 700ms ease,
        transform 950ms cubic-bezier(.16,1,.3,1);
    }

    #meetings-hero-angled-left-55.is-in .vector-top-fx{
      opacity: 1;
      transform: translateX(0);
      transition-delay: 260ms;
    }

    #meetings-hero-angled-left-55.is-in .vector-btm-mobile-fx{
      opacity: 1;
      transform: translateX(0);
      transition-delay: 320ms;
    }

    #meetings-hero-angled-left-55.is-in .vector-btm-desktop-fx{
      opacity: 1;
      transform: translateX(0);
      transition-delay: 320ms;
    }

    /* Extra safety: very small phones (optional tweak if needed) */
    @media (max-width: 400px){
      #meetings-hero-angled-left-55 .vector-btm-mobile-fx img{
        width: 6rem;
      }
    }
    @media (max-width: 360px){
      #meetings-hero-angled-left-55 .vector-btm-mobile-fx{
        display: none;
      }
    }

    @media (prefers-reduced-motion: reduce){
      #meetings-hero-angled-left-55 .fx{
        opacity: 1 !important;
        transform: none !important;
        transition: none !important;
      }
      #meetings-hero-angled-left-55 img{
        transition: none !important;
        transform: none !important;
      }
      #meetings-hero-angled-left-55 .vector-top-fx,
      #meetings-hero-angled-left-55 .vector-btm-mobile-fx,
      #meetings-hero-angled-left-55 .vector-btm-desktop-fx{
        opacity: 1 !important;
        transform: none !important;
        transition: none !important;
      }
    }
  </style>
</section>




{{-- ===== Title block (2-8-2) ===== --}}
<div class="mx-auto max-w-[1600px] px-4 sm:px-6 lg:px-8 pt-10 md:pt-10">
  <div class="grid grid-cols-12">
    <div class="hidden lg:block lg:col-span-2"></div>

    <div class="col-span-12 lg:col-span-8 text-center">

      <p class="fx fx-right mt-3  md:mt-4 text-sm md:text-base lg:text-lg font-medium text-[#25282a]/90" style="--d:200ms">
      Our versatile function rooms and events hall offer the perfect blend of elegance and functionality, ensuring the success of your next celebration or meeting.

      </p>

      <div class="fx fx-right mb-10 mx-auto mt-6 h-1 w-16 rounded-full" style="--d:260ms; background-color:#E6E7E8;" aria-hidden="true"></div>
    </div>

    <div class="hidden lg:block lg:col-span-2"></div>
  </div>
</div>

{{-- ===== Alpine slider helper (kept for future use if needed) ===== --}}
<script>
  document.addEventListener('alpine:init', () => {
    Alpine.data('amenitySlider', (initial) => ({
      idx: 0,
      lightbox: false,
      slides: initial.slides || [],
      next(){ this.idx = (this.idx + 1) % this.slides.length },
      prev(){ this.idx = (this.idx - 1 + this.slides.length) % this.slides.length },
      set(i){ this.idx = i },
    }));
  });
</script>

{{-- =========================
   VILLAS AND POOL
========================= --}}
<section id="meetings-venues" class="relative bg-white py-5 md:py-5 text-[#25282a]">
  <style>
    /* prevent background scroll ONLY when gallery/video is open */
    body.no-scroll {
      overflow: hidden !important;
    }

    #meetings-venues .thumb.on {
      outline: 2px solid #CF4520;
      outline-offset: 2px;
    }
  </style>

  <div
    class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8"
    x-data="meetingsGallery()"
  >
    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
      <div>
        <p class="text-xs md:text-sm font-semibold tracking-[.28em] uppercase text-[#3F2021]">
        </p>
        <h2 class="mt-2 text-2xl md:text-3xl lg:text-4xl font-semibold leading-tight tracking-tight">
          Villas and Infinity Pool
        </h2>
      </div>
    </div>

    {{-- Venue cards --}}
    <div class="mt-6 grid gap-8 md:gap-10 md:grid-cols-2 xl:grid-cols-2">
      <template x-for="(v, idx) in venues" :key="v.id">
        <article class="flex flex-col rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
          {{-- Image --}}
          <button
            type="button"
            class="relative group w-full overflow-hidden"
            @click="openVenue(idx)"
          >
            <div class="aspect-[16/10] w-full">
              <img
                :src="v.images[0]"
                :alt="v.name"
                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                loading="lazy"
              >
            </div>

            <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/45 via-transparent to-transparent"></div>

            <div class="absolute left-3 bottom-3 flex items-center gap-2 rounded-full bg-black/75 text-white px-3 py-1.5 text-[11px] font-semibold">
              <i class="fa-regular fa-images text-[#CF4520]"></i>
              <span x-text="v.images.length + ' photos'"></span>
            </div>
          </button>

          {{-- Content --}}
          <div class="flex flex-col flex-1 p-5 md:p-6">
            <div class="flex items-start justify-between gap-3">
              <h3 class="text-xl md:text-xl lg:text-3xl font-semibold leading-tight tracking-tight" x-text="v.name"></h3>
              <span
                class="inline-flex items-center gap-1.5 rounded-full bg-[#CF4520]/10 text-[#CF4520] px-3 py-1 text-[11px] font-bold border border-[#CF4520]/30"
                x-text="v.tag"
              ></span>
            </div>

            <p class="mt-3 text-sm md:text-[15px] lg:text-base font-medium text-gray-700" x-text="v.desc"></p>

            {{-- Info chips --}}
            <div class="mt-4 flex flex-wrap gap-2 text-xs font-medium md:text-sm lg:text-base text-gray-800">
              <template x-if="v.capacity">
                <span class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-gray-50 px-3 py-1.5">
                  <i class="fa-solid fa-users text-[#CF4520]"></i>
                  <span x-text="v.capacity"></span>
                </span>
              </template>

              <template x-if="v.area">
                <span class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-gray-50 px-3 py-1.5">
                  <i class="fa-solid fa-ruler-combined text-[#CF4520]"></i>
                  <span x-text="v.area"></span>
                </span>
              </template>

              <template x-if="v.location">
                <span class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-gray-50 px-3 py-1.5">
                  <i class="fa-solid fa-location-dot text-[#CF4520]"></i>
                  <span x-text="v.location"></span>
                </span>
              </template>

              <template x-if="v.schedule">
                <span class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-gray-50 px-3 py-1.5">
                  <i class="fa-regular fa-clock text-[#CF4520]"></i>
                  <span x-text="v.schedule"></span>
                </span>
              </template>
            </div>

            <div class="mt-5 flex flex-wrap items-center gap-3">
              <button
                type="button"
                class="inline-flex items-center gap-2 rounded-xl bg-[#CF4520] px-4 py-2 text-xs md:text-sm font-semibold text-white hover:bg-[#3F2021] transition"
                @click="openVenue(idx)"
              >
                View Gallery
                <i class="fa-solid fa-arrow-right"></i>
              </button>
            </div>
          </div>
        </article>
      </template>
    </div>

    {{-- Shared Gallery Modal --}}
    <div
      x-show="open"
      x-cloak
      x-transition.opacity
      @click.self="close()"
      @keydown.escape.window="open && close()"
      class="fixed inset-0 z-[99999] bg-black/90 flex items-center justify-center px-3 sm:px-4 py-10 overflow-y-auto"
      role="dialog"
      aria-modal="true"
    >
      {{-- Close button --}}
      <button
        type="button"
        @click="close()"
        class="absolute right-4 top-4 inline-flex items-center justify-center w-10 h-10 rounded-full bg-white/95 hover:bg-white text-gray-900 shadow"
      >
        <i class="fa-solid fa-xmark"></i>
      </button>

      <div class="w-full max-w-[1000px] mx-auto">
        {{-- Header --}}
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4 text-white">
          <div>
            <p class="text-[11px] uppercase tracking-[.22em] text-[#E6E7E8]">
              Villas &amp; Pool
            </p>
            <h3 class="mt-1 text-lg sm:text-xl md:text-2xl font-extrabold leading-tight" x-text="currentVenue.name"></h3>
            <p class="mt-1 text-xs sm:text-sm text-white/70">
              Venue Photos
            </p>
          </div>
          <div class="flex flex-col items-end gap-1 text-right">
            <span
              class="inline-flex items-center gap-1.5 rounded-full bg-white/10 text-white px-3 py-1 text-[11px] font-semibold border border-white/30"
            >
              <i class="fa-solid fa-bed text-[#CF4520]"></i>
              <span x-text="currentVenue.tag"></span>
            </span>
            <template x-if="currentVenue.capacity">
              <span class="inline-flex items-center gap-1.5 text-[11px] uppercase tracking-[.18em] text-white/60">
                <i class="fa-solid fa-users text-[#E6E7E8]"></i>
                <span x-text="currentVenue.capacity"></span>
              </span>
            </template>
          </div>
        </div>

        {{-- Main image + nav --}}
        <div class="relative rounded-2xl overflow-hidden border border-white/10 bg-black/60">
          <div class="flex items-center justify-center bg-black">
            <img
              :src="currentVenue.images[slide]"
              :alt="currentVenue.name + ' photo ' + (slide+1)"
              class="max-h-[calc(100svh-260px)] w-full object-contain"
            >
          </div>

          <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent"></div>

          {{-- nav --}}
          <button
            type="button"
            @click.stop="prev()"
            class="absolute left-2 sm:left-4 top-1/2 -translate-y-1/2 inline-flex items-center justify-center w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-white/90 text-gray-900 hover:bg-white shadow"
          >
            <i class="fa-solid fa-chevron-left"></i>
          </button>
          <button
            type="button"
            @click.stop="next()"
            class="absolute right-2 sm:right-4 top-1/2 -translate-y-1/2 inline-flex items-center justify-center w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-white/90 text-gray-900 hover:bg-white shadow"
          >
            <i class="fa-solid fa-chevron-right"></i>
          </button>

          {{-- counter --}}
          <div class="absolute right-3 bottom-3 inline-flex items-center gap-2 rounded-full bg-black/80 text-white px-3 py-1.5 text-xs font-semibold">
            <i class="fa-regular fa-images text-[#E6E7E8]"></i>
            <span x-text="(slide+1) + ' / ' + currentVenue.images.length"></span>
          </div>
        </div>

        {{-- Thumbs --}}
        <div
          class="mt-4
                 grid grid-cols-4 sm:grid-cols-5 gap-2 max-h-[160px] overflow-y-auto
                 md:max-h-none md:grid-cols-none md:flex md:items-stretch md:overflow-y-visible md:overflow-x-auto"
          x-ref="thumbRail"
        >
          <template x-for="(src, idx) in currentVenue.images" :key="idx">
            <button
              type="button"
              class="thumb relative rounded-lg overflow-hidden border border-white/20 bg-white/5
                     md:flex-[0_0_96px] lg:flex-[0_0_112px]"
              :class="slide === idx ? 'on' : ''"
              @click="go(idx)"
            >
              <img :src="src" alt="" class="w-full h-full object-cover">
            </button>
          </template>
        </div>

        <p class="mt-3 text-center text-[11px] text-white/60">
          Use the arrows or thumbnails to switch photos. Tap outside the gallery or press Esc to close.
        </p>
      </div>
    </div>
  </div>

  {{-- Alpine data for villas & pool gallery --}}
  <script>
    document.addEventListener('alpine:init', () => {
      Alpine.data('meetingsGallery', () => ({
        open: false,
        activeIndex: 0,
        slide: 0,

        videoMangroveOpen: false,
        videoAquaOpen: false,

        venues: [
          {
            id: 'master-villas',
            name: "Villas: Master's Bedroom",
            tag: 'Villa',
            capacity: '2 guests (1 queen-sized bed)',
            area: '11.14–11.31 sqm',
            location: 'Barangay Taguihon, Baclayon, Bohol, Philippines 6301',
            desc: `Perfect for small groups and families, our Deluxe Rooms’ interiors strike the balance between classic and modern style. Living and dining area offers the ultimate relaxation with day bed, TV, personal refrigerator, and coffee and tea-making facilities. 11.14 to 11.31-square meter rooms with one queen-sized bed that can accommodate 2 guests.`,
            images: [
              @js(asset('images/Lantawan/masters1.webp')),
              @js(asset('images/Lantawan/masters2.webp')),
              @js(asset('images/Lantawan/masters3.webp')),
              @js(asset('images/Lantawan/masters4.webp')),
              @js(asset('images/Lantawan/masters6.webp')),
              @js(asset('images/Lantawan/masters7.webp')),
            ],
          },
          {
            id: 'second-villas',
            name: 'Villas: Second Bedroom',
            tag: 'Villa',
            capacity: 'Up to 4 guests (2 queen-sized beds)',
            area: '15.33–19.36 sqm',
            location: 'Barangay Taguihon, Baclayon, Bohol, Philippines 6301',
            desc: `Living and dining area offers the ultimate relaxation with day bed, TV, personal refrigerator, and coffee and tea-making facilities. 15.33 to 19.36-square meter rooms with two queen-sized beds that can accommodate up to 4 guests.`,
            images: [
              @js(asset('images/Lantawan/second1.webp')),
              @js(asset('images/Lantawan/second2.webp')),
              @js(asset('images/Lantawan/second3.webp')),
              @js(asset('images/Lantawan/second4.webp')),
              @js(asset('images/Lantawan/second5.webp')),
              @js(asset('images/Lantawan/second6.webp')),
              @js(asset('images/Lantawan/second7.webp')),
              @js(asset('images/Lantawan/second8.webp')),
            ],
          },
          {
            id: 'infinity-pool',
            name: 'Infinity Pool',
            tag: 'Outdoor Pool',
            area: '72.76 sqm · 4–6 ft deep',
            schedule: '8:00 AM to 8:00 PM',
            location: 'Barangay Taguihon, Baclayon, Bohol, Philippines 6301',
            desc: `Astoria Bohol Lantawan's infinity pool offers the best view of Mindanao Sea. It is 72.76 square meters in total, ranging from 4 to 6 feet. Pool is open from 8:00 AM to 8:00 PM.`,
            images: [
              @js(asset('images/Lantawan/infinity1.webp')),
              @js(asset('images/Lantawan/infinity2.webp')),
              @js(asset('images/Lantawan/infinity3.webp')),
              @js(asset('images/Lantawan/infinity4.webp')),
              @js(asset('images/Lantawan/infinity5.webp')),
            ],
          },
        ],

        get currentVenue() {
          return this.venues[this.activeIndex] || { images: [] };
        },

        openVenue(idx) {
          this.activeIndex = idx;
          this.slide = 0;
          this.open = true;
          this.lockScroll();
          this.ensureThumb();
        },
        close() {
          this.open = false;
          this.unlockScroll();
        },

        next() {
          const imgs = this.currentVenue.images || [];
          if (!imgs.length) return;
          this.slide = (this.slide + 1) % imgs.length;
          this.ensureThumb();
        },
        prev() {
          const imgs = this.currentVenue.images || [];
          if (!imgs.length) return;
          this.slide = (this.slide - 1 + imgs.length) % imgs.length;
          this.ensureThumb();
        },
        go(i) {
          const imgs = this.currentVenue.images || [];
          if (!imgs.length) return;
          this.slide = i;
          this.ensureThumb();
        },

        openMangroveVideo() {
          this.videoMangroveOpen = true;
          this.lockScroll();
        },
        openAquaVideo() {
          this.videoAquaOpen = true;
          this.lockScroll();
        },

        closeMangroveVideo() {
          this.videoMangroveOpen = false;
          this.stopIframe('mangroveFrame');
          this.unlockScroll();
        },
        closeAquaVideo() {
          this.videoAquaOpen = false;
          this.stopIframe('aquaFrame');
          this.unlockScroll();
        },

        stopIframe(refName) {
          this.$nextTick(() => {
            const iframe = this.$refs[refName];
            if (iframe && iframe.tagName === 'IFRAME') {
              const src = iframe.getAttribute('src');
              iframe.setAttribute('src', src); // reloads & stops playback
            }
          });
        },

        lockScroll() {
          document.body.classList.add('no-scroll');
        },
        unlockScroll() {
          document.body.classList.remove('no-scroll');
        },
        ensureThumb() {
          this.$nextTick(() => {
            const rail = this.$refs.thumbRail;
            if (!rail) return;
            const active = rail.querySelector('.thumb.on');
            if (active) {
              active.scrollIntoView({ inline: 'center', block: 'nearest', behavior: 'smooth' });
            }
          });
        },
      }));
    });
  </script>
</section>

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
