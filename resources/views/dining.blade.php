@extends('layouts.app') 

@section('title', 'Food and Beverages | Astoria Bohol')

@push('head')
  {{-- Font Awesome (no SRI to avoid mismatch) --}}
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
@endpush

@section('content')

<section
  id="dining-hero-angled-left-55"
  class="relative w-full overflow-hidden text-[#25282a] bg-white"
  aria-label="About Astoria Hotels and Resorts hero banner"
  x-data="{ loaded:false }"
  x-init="requestAnimationFrame(()=>{ loaded=true })"
  :class="loaded ? 'is-in' : ''"
>
  {{-- DESKTOP BG (angled image) --}}
  <div class="absolute inset-0 right-clip hidden lg:block">
    <img
      src="{{ asset('images/Dining/dining-header.webp') }}"
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
         LIMITLESS POSSIBILITIES WORTH DISCOVERING



        </p>

        {{-- HEADER (teal) --}}
        <h1 class="fx fx-right font-semibold leading-tight tracking-tight text-3xl sm:text-4xl md:text-5xl mt-2" style="--d:100ms">
          <span class="bg-clip-text text-transparent bg-gradient-to-r from-[#CF4520] via-[#CF4520] to-[#CF4520]/85">
          FOOD & BEVERAGE


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
        Indulge in both local and international gourmet cuisines crafted by our world-class chefs for an exceptional dining experience.



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
        src="{{ asset('images/Dining/dining-header.webp') }}"
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
        LIMITLESS POSSIBILITIES WORTH DISCOVERING


        </p>

        {{-- HEADER (teal) --}}
        <h1 class="fx fx-right font-semibold leading-tight tracking-tight text-5xl lg:text-6xl mt-2" style="--d:360ms">
          <span class="bg-clip-text text-transparent bg-gradient-to-r from-[#CF4520] via-[#CF4520] to-[#CF4520]/80">
          FOOD & BEVERAGE


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
       Indulge in both local and international gourmet cuisines crafted by our world-class chefs for an exceptional dining experience.



        </p>
      </div>
    </div>

    <span aria-hidden="true" class="pointer-events-none absolute inset-0 ring-1 ring-inset ring-white/10"></span>
  </div>

  <style>
    /* Default: no clipping for non-desktop */
    #dining-hero-angled-left-55 .left-clip,
    #dining-hero-angled-left-55 .right-clip {
      clip-path: none;
      -webkit-clip-path: none;
    }

    /* Desktop (lg+): 55° seam */
    @media (min-width: 1024px){
      #dining-hero-angled-left-55 .left-clip{
        clip-path: polygon(0% 0%, 70% 0%, 30% 100%, 0% 100%);
        -webkit-clip-path: polygon(0% 0%, 70% 0%, 30% 100%, 0% 100%);
      }
      #dining-hero-angled-left-55 .right-clip{
        clip-path: polygon(70% 0%, 100% 0%, 100% 100%, 30% 100%);
        -webkit-clip-path: polygon(70% 0%, 100% 0%, 100% 100%, 30% 100%);
      }

    #dining-hero-angled-left-55 .right-clip > img.bg-shift{
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
      #dining-hero-angled-left-55 .tablet-stack {
        display: grid !important;
        min-height: 900px !important;
        padding-top: 32px !important;
      }
      #dining-hero-angled-left-55 .desktop-angled,
      #dining-hero-angled-left-55 .right-clip {
        display: none !important;
      }
      #dining-hero-angled-left-55 .left-clip,
      #dining-hero-angled-left-55 .right-clip {
        clip-path: none !important;
        -webkit-clip-path: none !important;
      }
    }
    @media (min-width:1024px) and (max-width:1368px) and (aspect-ratio: 3/4),
           (min-width:1194px) and (max-width:1368px) and (aspect-ratio: 4/3) {
      #dining-hero-angled-left-55 .tablet-stack {
        display: grid !important;
        min-height: 900px !important;
        padding-top: 32px !important;
      }
      #dining-hero-angled-left-55 .desktop-angled,
      #dining-hero-angled-left-55 .right-clip {
        display: none !important;
      }
      #dining-hero-angled-left-55 .left-clip,
      #dining-hero-angled-left-55 .right-clip {
        clip-path: none !important;
        -webkit-clip-path: none !important;
      }
    }

    /* Entrance FX for text */
    #dining-hero-angled-left-55 .fx{
      opacity: .001;
      transform: translateX(-14px);
      will-change: transform, opacity;
      transition:
        opacity 520ms ease,
        transform 640ms cubic-bezier(.22,1,.36,1);
      transition-delay: var(--d, 0ms);
    }
    #dining-hero-angled-left-55.is-in .fx{
      opacity: 1;
      transform: translateX(0);
    }

    /* Slide-in FX for vectors (top + bottom) – from left to right, slower */
    #dining-hero-angled-left-55 .vector-top-fx,
    #dining-hero-angled-left-55 .vector-btm-mobile-fx,
    #dining-hero-angled-left-55 .vector-btm-desktop-fx{
      opacity: 0;
      transform: translateX(-32px);
      will-change: transform, opacity;
      transition:
        opacity 700ms ease,
        transform 950ms cubic-bezier(.16,1,.3,1);
    }

    #dining-hero-angled-left-55.is-in .vector-top-fx{
      opacity: 1;
      transform: translateX(0);
      transition-delay: 260ms;
    }

    #dining-hero-angled-left-55.is-in .vector-btm-mobile-fx{
      opacity: 1;
      transform: translateX(0);
      transition-delay: 320ms;
    }

    #dining-hero-angled-left-55.is-in .vector-btm-desktop-fx{
      opacity: 1;
      transform: translateX(0);
      transition-delay: 320ms;
    }

    /* Extra safety: very small phones (optional tweak if needed) */
    @media (max-width: 400px){
      #dining-hero-angled-left-55 .vector-btm-mobile-fx img{
        width: 6rem;
      }
    }
    @media (max-width: 360px){
      #dining-hero-angled-left-55 .vector-btm-mobile-fx{
        display: none;
      }
    }

    @media (prefers-reduced-motion: reduce){
      #dining-hero-angled-left-55 .fx{
        opacity: 1 !important;
        transform: none !important;
        transition: none !important;
      }
      #dining-hero-angled-left-55 img{
        transition: none !important;
        transform: none !important;
      }
      #dining-hero-angled-left-55 .vector-top-fx,
      #dining-hero-angled-left-55 .vector-btm-mobile-fx,
      #dining-hero-angled-left-55 .vector-btm-desktop-fx{
        opacity: 1 !important;
        transform: none !important;
        transition: none !important;
      }
    }
  </style>
</section>



{{-- ===== Title block (2-8-2) ===== --}}
<div class="mx-auto max-w-[1600px] px-4 sm:px-6 lg:px-8 pt-24 md:pt-28">
  <div class="grid grid-cols-12">
    <div class="hidden lg:block lg:col-span-2"></div>

    <div class="col-span-12 lg:col-span-8 text-center">
      <p
        class="fx fx-right uppercase tracking-[.28em] text-xs md:text-sm font-semibold text-[#3F2021]"
        style="--d:40ms"
      >
        RELISH AN AUTHENTIC TASTE OF LOCAL CUISINE

      </p>

      <div class="mt-2">
        <h2
          class="fx fx-right text-3xl md:text-5xl font-semibold"
          style="--d:120ms"
        >
          <span class="bg-clip-text text-transparent bg-[#CF4520]">
           RESORT DINING

          </span>
        </h2>
      </div>

      <p
        class="fx fx-right mt-3 md:mt-4 text-sm md:text-base lg:text-lg font-medium text-[#25282a]/90"
        style="--d:200ms"
      >
       Savor a world-class gastronomical experience through Baclayon's traditional specialties with a twist!

      </p>

      {{-- Menu CTA with delayed animation --}}
      <div
        class="fx fx-right mt-5 flex justify-center"
        style="--d:260ms"
      >
        <a
          href="https://foodmenu.astoriahotelsandresorts.com/astoria-bohol-menu/"
          target="_blank"
          rel="noopener"
          class="inline-flex items-center gap-2 rounded-full bg-[#CF4520] px-6 md:px-8 py-2.5 md:py-3
                 text-[11px] md:text-xs font-semibold tracking-[.24em] uppercase text-white
                 shadow hover:brightness-110 focus:outline-none focus-visible:ring-4 focus-visible:ring-[#04b2e2]/40"
        >
          <i class="fa-solid fa-utensils text-sm md:text-base"></i>
          <span>View Menu</span>
        </a>
      </div>

      <div
        class="fx fx-right  mx-auto mt-6 h-1 w-16 rounded-full"
        style="--d:320ms; background-color:#3F2021;"
        aria-hidden="true"
      ></div>
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
   Dining Section + Gallery Modal
   Requirements:
   - click image -> black overlay modal
   - ESC + click overlay to close
   - left/right nav
   - thumbs grid reacts when slide changes
   - very responsive
   - uses Font Awesome for info icons
   - FIX: avoid page jumping to header when opening modal
========================= --}}
<section id="dining-venues" class="relative bg-white py-16 md:py-20">
  <style>
    /* prevent background scroll ONLY when gallery is open
       NOTE: apply to body only to avoid jump-to-top */
    body.no-scroll {
      overflow: hidden !important;
    }

    #dining-venues .thumb.on {
      outline: 2px solid #CF4520;
      outline-offset: 2px;
    }
  </style>

  <div class="max-w-[1000px] mx-auto px-4 sm:px-6 lg:px-8" x-data="diningGallery()">
    {{-- Dining cards --}}
    <div class="mt-10 grid gap-8 md:gap-10 md:grid-cols-1 xl:grid-cols-1">
      <template x-for="(v, i) in venues" :key="v.id">
        <article class="flex flex-col rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
          {{-- Image / hero --}}
          <button
            type="button"
            class="relative group w-full overflow-hidden"
            @click="openVenue(i)"
          >
            <div class="aspect-[16/10] w-full">
              <img
                :src="v.images[0]"
                :alt="v.name"
                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                loading="lazy"
              >
            </div>

            <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/35 via-transparent to-transparent"></div>

            <div class="absolute left-3 bottom-3 flex items-center gap-2 rounded-full bg-black/70 text-white px-3 py-1.5 text-xs font-semibold">
              <i class="fa-regular fa-images text-[#CF4520]"></i>
              <span x-text="v.images.length + ' photos'"></span>
            </div>
          </button>

          {{-- Content --}}
          <div class="flex flex-col flex-1 p-5 md:p-6">
            <div class="flex items-start justify-between gap-3">
              <h3 class="text-xl md:text-2xl lg:text-3xl font-semibold leading-tight tracking-tight text-[#0b0f14]" x-text="v.name"></h3>
              <span
                class="inline-flex items-center gap-1.5 rounded-full bg-[#CF4520]/10 text-[#CF4520] px-3 py-1 text-[11px] font-semibold border border-[#CF4520]/30"
                x-text="v.tag"
              ></span>
            </div>

            <p class="mt-3 text-sm md:text-[15px] lg:text-lg font-medium text-gray-700" x-text="v.desc"></p>

            {{-- Info chips --}}
            <div class="mt-4 flex flex-wrap gap-2 text-xs md:text-sm font-semibold text-gray-800">
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

              <template x-if="v.hours && v.hours.length">
                <span class="inline-flex items-start gap-2 rounded-xl border border-gray-200 bg-gray-50 px-3 py-1.5">
                  <i class="fa-regular fa-clock mt-[2px] text-[#CF4520]"></i>
                  <span class="flex flex-col">
                    <template x-for="(h, hi) in v.hours" :key="hi">
                      <span x-text="h"></span>
                    </template>
                  </span>
                </span>
              </template>

              <template x-if="v.phones">
                <span class="inline-flex items-start gap-2 rounded-xl border border-gray-200 bg-gray-50 px-3 py-1.5">
                  <i class="fa-solid fa-phone mt-[2px] text-[#CF4520]"></i>
                  <span x-text="v.phones"></span>
                </span>
              </template>

              <template x-if="v.location">
                <span class="inline-flex items-start gap-2 rounded-xl border border-gray-200 bg-gray-50 px-3 py-1.5">
                  <i class="fa-solid fa-location-dot mt-[2px] text-[#CF4520]"></i>
                  <span x-text="v.location"></span>
                </span>
              </template>
            </div>

            <div class="mt-5 flex flex-wrap items-center gap-3">
              <button
                type="button"
                class="inline-flex items-center gap-2 rounded-xl bg-[#CF4520] px-4 py-2 text-sm font-semibold text-white hover:bg-[#3F2021] transition"
                @click="openVenue(i)"
              >
                View Gallery
                <i class="fa-solid fa-arrow-right"></i>
              </button>
            </div>
          </div>
        </article>
      </template>
    </div>

    {{-- Gallery Modal (single, shared for all venues) --}}
    <div
      x-show="open"
      x-cloak
      x-transition.opacity
      @click.self="close()"
      @keydown.escape.window="close()"
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
        {{-- Venue title + badge --}}
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4 text-white">
          <div>
            <h3 class="text-lg sm:text-xl md:text-2xl font-semibold leading-tight" x-text="currentVenue.name"></h3>
            <p class="mt-1 text-xs sm:text-sm text-white/70">
              Dining Gallery
            </p>
          </div>
          <span
            class="inline-flex items-center gap-1.5 rounded-full bg-white/10 text-white px-3 py-1 text-[11px] font-semibold border border-white/30"
            x-text="currentVenue.tag"
          ></span>
        </div>

        {{-- Main image + nav --}}
        <div class="relative rounded-2xl overflow-hidden border border-white/10 bg-black/50">
          <div class="flex items-center justify-center bg-black">
            <img
              :src="currentVenue.images[slide]"
              :alt="currentVenue.name + ' photo ' + (slide+1)"
              class="max-h-[calc(100svh-260px)] w-full object-contain"
            >
          </div>

          {{-- overlay gradient --}}
          <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent"></div>

          {{-- nav buttons --}}
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

          {{-- counter pill --}}
          <div class="absolute right-3 bottom-3 inline-flex items-center gap-2 rounded-full bg-black/75 text-white px-3 py-1.5 text-xs font-semibold">
            <i class="fa-regular fa-images text-[#EFDE0F]"></i>
            <span x-text="(slide+1) + ' / ' + currentVenue.images.length"></span>
          </div>
        </div>

        {{-- Thumbnails grid (reacts when slide changes) --}}
        <div
          class="mt-4 grid grid-cols-4 sm:grid-cols-5 md:grid-cols-6 lg:grid-cols-8 gap-2 max-h-[160px] overflow-y-auto"
          x-ref="thumbRail"
        >
          <template x-for="(src, idx) in currentVenue.images" :key="idx">
            <button
              type="button"
              class="thumb relative rounded-lg overflow-hidden border border-white/20 bg-white/5"
              :class="slide === idx ? 'on' : ''"
              @click="go(idx)"
            >
              <img
                :src="src"
                alt=""
                class="w-full h-full object-cover"
              >
            </button>
          </template>
        </div>

        <p class="mt-3 text-center text-[11px] text-white/60">
          Use the arrows or thumbnails to switch photos. Tap outside the gallery or press Esc to close.
        </p>
      </div>
    </div>
  </div>

  {{-- Alpine data for dining gallery --}}
  <script>
    document.addEventListener('alpine:init', () => {
      Alpine.data('diningGallery', () => ({
        open: false,
        activeIndex: 0,
        slide: 0,
        scrollY: 0, // optional if you later want advanced scroll restore
        venues: [
          {
            id: 'pamana',
            name: 'Pamana',
            tag: 'All-Day Dining',
            desc: 'In honor of traditional cooking, a wood-fired brick oven, or what we call “pugon” in Filipino, is used to cook sumptuous meals. With the beautiful view of Baclayon’s shoreline and local and international cuisine to choose from, you will truly have a memorable stay in our resort.',
            capacity: 'Up to 30 pax',
            hours: [
              'Breakfast buffet: 6:30 AM to 10:00 AM',
              'À la carte: 11:00 AM to 10:00 PM',
            ],
            phones: 'Landline: (+63 38) 411-4695, Mobile: (+63) 968-855-9851',
            images: [
              @js(asset('images/Dining/pamana1.webp')),
               @js(asset('images/Dining/pamana2.webp')),
                @js(asset('images/Dining/pamana3.webp')),
                 @js(asset('images/Dining/pamana4.webp')),
                  @js(asset('images/Dining/pamana5.webp')),
                   @js(asset('images/Dining/pamana6.webp')),
                    @js(asset('images/Dining/pamana7.webp')),
                     @js(asset('images/Dining/pamana8.webp')),
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

        lockScroll() {
          // Only lock body to avoid jump-to-top
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
