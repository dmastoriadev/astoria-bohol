@extends('layouts.app') 

@section('title', 'Amenities | Astoria Bohol')

@push('head')
  {{-- Font Awesome (no SRI to avoid mismatch) --}}
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
@endpush

@section('content')

<section
  id="amenities-hero-angled-left-55"
  class="relative w-full overflow-hidden text-[#25282a] bg-white"
  aria-label="About Astoria Hotels and Resorts hero banner"
  x-data="{ loaded:false }"
  x-init="requestAnimationFrame(()=>{ loaded=true })"
  :class="loaded ? 'is-in' : ''"
>
  {{-- DESKTOP BG (angled image) --}}
  <div class="absolute inset-0 right-clip hidden lg:block">
    <img
      src="{{ asset('images/Amenities/amenities.webp') }}"
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
         STEP INTO THE BOHOL EXPERIENCE



        </p>

        {{-- HEADER (teal) --}}
        <h1 class="fx fx-right font-semibold leading-tight tracking-tight text-3xl sm:text-4xl md:text-5xl mt-2" style="--d:100ms">
          <span class="bg-clip-text text-transparent bg-gradient-to-r from-[#CF4520] via-[#CF4520] to-[#CF4520]/85">
           AMENITIES


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
         Revel in incomparable luxury and grandeur




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
        src="{{ asset('images/Amenities/amenities.webp') }}"
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
         STEP INTO THE BOHOL EXPERIENCE


        </p>

        {{-- HEADER (teal) --}}
        <h1 class="fx fx-right font-semibold leading-tight tracking-tight text-5xl lg:text-6xl mt-2" style="--d:360ms">
          <span class="bg-clip-text text-transparent bg-gradient-to-r from-[#CF4520] via-[#CF4520] to-[#CF4520]/80">
           AMENITIES


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
       Revel in incomparable luxury and grandeur



        </p>
      </div>
    </div>

    <span aria-hidden="true" class="pointer-events-none absolute inset-0 ring-1 ring-inset ring-white/10"></span>
  </div>

  <style>
    /* Default: no clipping for non-desktop */
    #amenities-hero-angled-left-55 .left-clip,
    #amenities-hero-angled-left-55 .right-clip {
      clip-path: none;
      -webkit-clip-path: none;
    }

    /* Desktop (lg+): 55° seam */
    @media (min-width: 1024px){
      #amenities-hero-angled-left-55 .left-clip{
        clip-path: polygon(0% 0%, 70% 0%, 30% 100%, 0% 100%);
        -webkit-clip-path: polygon(0% 0%, 70% 0%, 30% 100%, 0% 100%);
      }
      #amenities-hero-angled-left-55 .right-clip{
        clip-path: polygon(70% 0%, 100% 0%, 100% 100%, 30% 100%);
        -webkit-clip-path: polygon(70% 0%, 100% 0%, 100% 100%, 30% 100%);
      }

    #amenities-hero-angled-left-55 .right-clip > img.bg-shift{
        left: 13vw !important;
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
      #amenities-hero-angled-left-55 .tablet-stack {
        display: grid !important;
        min-height: 900px !important;
        padding-top: 32px !important;
      }
      #amenities-hero-angled-left-55 .desktop-angled,
      #amenities-hero-angled-left-55 .right-clip {
        display: none !important;
      }
      #amenities-hero-angled-left-55 .left-clip,
      #amenities-hero-angled-left-55 .right-clip {
        clip-path: none !important;
        -webkit-clip-path: none !important;
      }
    }
    @media (min-width:1024px) and (max-width:1368px) and (aspect-ratio: 3/4),
           (min-width:1194px) and (max-width:1368px) and (aspect-ratio: 4/3) {
      #amenities-hero-angled-left-55 .tablet-stack {
        display: grid !important;
        min-height: 900px !important;
        padding-top: 32px !important;
      }
      #amenities-hero-angled-left-55 .desktop-angled,
      #amenities-hero-angled-left-55 .right-clip {
        display: none !important;
      }
      #amenities-hero-angled-left-55 .left-clip,
      #amenities-hero-angled-left-55 .right-clip {
        clip-path: none !important;
        -webkit-clip-path: none !important;
      }
    }

    /* Entrance FX for text */
    #amenities-hero-angled-left-55 .fx{
      opacity: .001;
      transform: translateX(-14px);
      will-change: transform, opacity;
      transition:
        opacity 520ms ease,
        transform 640ms cubic-bezier(.22,1,.36,1);
      transition-delay: var(--d, 0ms);
    }
    #amenities-hero-angled-left-55.is-in .fx{
      opacity: 1;
      transform: translateX(0);
    }

    /* Slide-in FX for vectors (top + bottom) – from left to right, slower */
    #amenities-hero-angled-left-55 .vector-top-fx,
    #amenities-hero-angled-left-55 .vector-btm-mobile-fx,
    #amenities-hero-angled-left-55 .vector-btm-desktop-fx{
      opacity: 0;
      transform: translateX(-32px);
      will-change: transform, opacity;
      transition:
        opacity 700ms ease,
        transform 950ms cubic-bezier(.16,1,.3,1);
    }

    #amenities-hero-angled-left-55.is-in .vector-top-fx{
      opacity: 1;
      transform: translateX(0);
      transition-delay: 260ms;
    }

    #amenities-hero-angled-left-55.is-in .vector-btm-mobile-fx{
      opacity: 1;
      transform: translateX(0);
      transition-delay: 320ms;
    }

    #amenities-hero-angled-left-55.is-in .vector-btm-desktop-fx{
      opacity: 1;
      transform: translateX(0);
      transition-delay: 320ms;
    }

    /* Extra safety: very small phones (optional tweak if needed) */
    @media (max-width: 400px){
      #amenities-hero-angled-left-55 .vector-btm-mobile-fx img{
        width: 6rem;
      }
    }
    @media (max-width: 360px){
      #amenities-hero-angled-left-55 .vector-btm-mobile-fx{
        display: none;
      }
    }

    @media (prefers-reduced-motion: reduce){
      #amenities-hero-angled-left-55 .fx{
        opacity: 1 !important;
        transform: none !important;
        transition: none !important;
      }
      #amenities-hero-angled-left-55 img{
        transition: none !important;
        transform: none !important;
      }
      #amenities-hero-angled-left-55 .vector-top-fx,
      #amenities-hero-angled-left-55 .vector-btm-mobile-fx,
      #amenities-hero-angled-left-55 .vector-btm-desktop-fx{
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
       A VACATION MADE MEMORABLE IN THE COMPANY OF OUR AMENITIES

      </p>

      <div class="mt-2">
        <h2
          class="fx fx-right text-3xl md:text-5xl font-semibold"
          style="--d:120ms"
        >
          <span class="bg-clip-text text-transparent bg-gradient-to-r from-[#CF4520] via-[#CF4520] to-[#CF4520]/85">
        
        AMENITIES


          </span>
        </h2>
      </div>

      <p
        class="fx fx-right mt-3 md:mt-4 text-sm md:text-base text-[#25282a]/90"
        style="--d:200ms"
      >
       Astoria Bohol is committed to ensuring that you experience the most relaxing vacation in a tropical paradise. Escape the hustle and bustle of urban living and relax by enjoying the amenities we offer.

      </p>

      
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

  @php
    $thumbCls   = 'h-[56px] w-[140px] xs:h-[64px] xs:w-[160px] sm:h-[72px] sm:w-[180px] md:h-[80px] md:w-[200px] object-cover';
    $mGridThumb = 'h-12 sm:h-16 md:h-20 w-full object-cover';
    $liIcon     = 'w-5 shrink-0 text-[#CF4520] text-base text-center';
    $liIconAlt  = 'w-5 shrink-0 text-[#63666A] text-base text-center';
    $ulCls      = 'mt-5 space-y-2 text-[15px] lg:text-base font-semibold';
    $phoneCls   = 'underline decoration-[#CF4520]/40 underline-offset-4 hover:decoration-[#CF4520]';
  @endphp

  {{-- ==================  POOL  ================== --}}
  <div class="mx-auto max-w-[1600px] px-4 sm:px-6 lg:px-8 mt-10 md:mt-14">
    <div class="grid grid-cols-12 gap-8 items-center">
      {{-- Gallery (L) --}}
      <div class="col-span-12 lg:col-span-7">
        <div
          x-data="amenitySlider({
            slides: [
              { src: @js(asset('images/Amenities/infinity1.webp')), alt:'Astoria Bohol Infinity Pool' },
               { src: @js(asset('images/Amenities/infinity2.webp')), alt:'Astoria Bohol Infinity Pool' },
                { src: @js(asset('images/Amenities/infinity3.webp')), alt:'Astoria Bohol Infinity Pool' },
                 { src: @js(asset('images/Amenities/infinity4.webp')), alt:'Astoria Bohol Infinity Pool' },
                  { src: @js(asset('images/Amenities/infinity5.webp')), alt:'Astoria Bohol Infinity Pool' },
                   { src: @js(asset('images/Amenities/infinity6.webp')), alt:'Astoria Bohol Infinity Pool' },
                    { src: @js(asset('images/Amenities/infinity7.webp')), alt:'Astoria Bohol Infinity Pool' },
                     { src: @js(asset('images/Amenities/infinity8.webp')), alt:'Astoria Bohol Infinity Pool' },

            ]
          })"
          class="relative rounded-2xl overflow-hidden border border-black/10 bg-white shadow-sm"
        >
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
                     focus:outline-none focus-visible:ring-4 focus-visible:ring-[#CF45205]/40">
              <i class="fa-solid fa-chevron-right text-lg"></i>
            </button>

            {{-- index pill --}}
            <div class="absolute left-3 bottom-3 inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-sm font-semibold bg-white/90 text-gray-800 shadow">
              <i class="fa-regular fa-image text-base"></i>
              <span x-text="(idx+1) + ' / ' + slides.length"></span>
            </div>
          </div>

          {{-- Thumbnails --}}
          <div class="bg-white/90 border-t border-black/10 px-3 py-2 overflow-x-auto">
            <div class="flex items-center gap-2">
              <template x-for="(s,i) in slides" :key="i">
                <button type="button" @click="set(i)"
                        class="group relative rounded-xl overflow-hidden border"
                        :class="i===idx ? 'border-[#CF4520]' : 'border-transparent hover:border-gray-200'">
                  <span class="absolute inset-0 ring-2 rounded-xl transition"
                        :class="i===idx ? 'ring-#CF4520]' : 'ring-transparent group-hover:ring-black/5'"></span>
                  <img :src="s.src" :alt="s.alt" class="{{ $thumbCls }}" loading="lazy">
                </button>
              </template>
            </div>
          </div>

          {{-- Lightbox --}}
          <div
            x-show="lightbox"
            x-transition.opacity
            x-cloak
            class="fixed inset-0 z-[9999] bg-black/80 backdrop-blur-sm flex flex-col items-center justify-center p-4"
            @click.self="lightbox=false"
            @keydown.escape.window="lightbox=false"
            role="dialog" aria-modal="true" aria-label="Image lightbox"
          >
            <div class="relative w-full max-w-6xl" @click.stop>
              <img :src="slides[idx].src" :alt="slides[idx].alt" class="w-full h-auto rounded-xl shadow-2xl">
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
                             focus:outline:none focus-visible:ring-4 focus-visible:ring-[#CF4520]/40"
                      aria-label="Next image">
                <i class="fa-solid fa-chevron-right text-base sm:text-lg"></i>
              </button>
              <button @click="lightbox=false"
                      class="absolute -top-4 -right-4 w-11 h-11 grid place-items-center rounded-full
                             bg-white/95 text-gray-900 shadow hover:bg-white ring-1 ring-black/10
                             focus:outline-none focus-visible:ring-4 focus-visible:ring-[#CF4520]/40"
                      aria-label="Close">
                <i class="fa-solid fa-xmark text-lg"></i>
              </button>
            </div>

            <div class="mt-4 w-full max-w-6xl" @click.stop>
              <div class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 gap-2">
                <template x-for="(s,i) in slides" :key="'lb1-'+i">
                  <button @click.stop="set(i)"
                          class="relative rounded-lg overflow-hidden border"
                          :class="i===idx ? 'border-white/70' : 'border-white/30 hover:border-white/50'">
                    <img :src="s.src" :alt="s.alt" class="{{ $mGridThumb }}">
                    <span class="absolute inset-0 ring-2 rounded-lg" :class="i===idx ? 'ring-white' : 'ring-transparent'"></span>
                  </button>
                </template>
              </div>
            </div>
          </div>
          {{-- /Lightbox --}}
        </div>
      </div>

      {{-- Copy (R) --}}
      <div class="col-span-12 lg:col-span-5">
        <div class="flex items-end gap-4 mt-1">
          <h3 class="fx fx-right text-3xl sm:text-4xl md:text-5xl lg:text-4xl font-semibold tracking-tight text-[#CF4520]" style="--d:120ms">
           INFINITY POOL


          </h3>
          <span class="fx fx-right h-1 w-24 lg:w-28 rounded-full bg-[#63666A] mb-2 hidden sm:block" style="--d:200ms"></span>
        </div>

        <p class="mt-4 text-[15px] leading-7 lg:text-lg lg:leading-8 text-[#25282a]/90">
         Astoria Bohol’s infinity edge lap pool is designed to make it seem as if it connects to the vast Mindanao Sea. You may opt to stay on the sunken loungers during the day, before our 24-meter long and 7-meter wide pool transforms into a spectacle of rainbow-colored lights at night. You do not have to worry about the little ones, because a kiddie pool is available for them to enjoy, too!
        </p>

        {{-- One-liner attributes + contacts --}}
        <ul class="{{ $ulCls }}">
          {{-- Kiddie pool --}}
          <li class="flex items-center gap-2">
            <i class="fa-solid fa-child-reaching {{ $liIcon }}"></i>
            <span>Kiddie pool</span>
          </li>

          {{-- Poolside loungers --}}
          <li class="flex items-center gap-2">
            <i class="fa-solid fa-umbrella-beach {{ $liIcon }}"></i>
            <span>Poolside loungers</span>
          </li>

          {{-- Pool length --}}
          <li class="flex items-center gap-2">
            <i class="fa-solid fa-ruler-horizontal {{ $liIcon }}"></i>
            <span>24-meter long swimming pool</span>
          </li>

          {{-- Shower facilities --}}
          <li class="flex items-center gap-2">
            <i class="fa-solid fa-shower {{ $liIcon }}"></i>
            <span>Shower facilities</span>
          </li>

          {{-- Operating hours --}}
          <li class="flex items-center gap-2">
            <i class="fa-regular fa-clock {{ $liIcon }}"></i>
            <span>6:00 AM – 10:00 PM</span>
          </li>
        </ul>

      </div>
    </div>
  </div>

  {{-- ================== Amenity #2: LIBRARY ================== --}}
  <div class="mx-auto max-w-[1600px] px-4 sm:px-6 lg:px-8 mt-14 mb-20 md:mt-20">
    <div class="grid grid-cols-12 gap-8 items-center">
      {{-- Copy (L) --}}
      <div class="col-span-12 lg:col-span-5 order-2 lg:order-1">
        <div class="flex items-end gap-4 mt-1">
          <h3 class="fx fx-right text-3xl sm:text-4xl md:text-5xl lg:text-4xl font-semibold tracking-tight text-[#CF4520]" style="--d:120ms">
            LIBRARY
          </h3>
          <span class="fx fx-right h-1 w-24 lg:w-28 rounded-full bg-[#63666A] mb-2 hidden sm:block" style="--d:200ms"></span>
        </div>

        <p class="mt-4 text-[15px] leading-7 lg:text-lg lg:leading-8 text-[#25282a]/90">
          Be our honored guest and explore worlds within worlds in the palm of your hand the classic way through our collection of books. Have a cozy cup of coffee and seat yourself comfortably in our lovely library furnished with beautiful dark wood seats.

        </p>

        <ul class="{{ $ulCls }}">
          {{-- Operating hours --}}
          <li class="flex items-center gap-2">
            <i class="fa-regular fa-clock {{ $liIcon }}"></i>
            <span>6:00 AM – 10:00 PM</span>
          </li>
        </ul>

      </div>

      {{-- Gallery (R) --}}
      <div class="col-span-12 lg:col-span-7 order-1 lg:order-2">
        <div
          x-data="amenitySlider({
            slides: [
              { src: @js(asset('images/Amenities/library1.webp')), alt:'Astoria Bohol Library' },
               { src: @js(asset('images/Amenities/library2.webp')), alt:'Astoria Bohol Library' },
                { src: @js(asset('images/Amenities/library3.webp')), alt:'Astoria Bohol Library' },
                 { src: @js(asset('images/Amenities/library4.webp')), alt:'Astoria Bohol Library' },
                  { src: @js(asset('images/Amenities/library5.webp')), alt:'Astoria Bohol Library' },
            ]
          })"
          class="relative rounded-2xl overflow-hidden border border-black/10 bg-white shadow-sm"
        >
          <div class="relative aspect-[4/3] md:aspect-[16/10] w-full group">
            <img
              :src="slides[idx].src"
              :alt="slides[idx].alt"
              class="absolute inset-0 h-full w-full object-cover will-change-transform transition-transform duration-[650ms] ease-[cubic-bezier(.22,1,.36,1)] group-hover:scale-[1.03] cursor-zoom-in"
              @click="lightbox = true"
              loading="lazy" decoding="async"
            />
            <button type="button" @click.stop="prev()" aria-label="Previous image"
              class="absolute left-3 top-1/2 -translate-y-1/2 w-12 h-12 grid place-items-center rounded-full bg-white/90 text-gray-800 hover:bg-white shadow-md ring-1 ring-black/10 focus:outline-none focus-visible:ring-4 focus-visible:ring-[#CF4520]/40">
              <i class="fa-solid fa-chevron-left text-lg"></i>
            </button>
            <button type="button" @click.stop="next()" aria-label="Next image"
              class="absolute right-3 top-1/2 -translate-y-1/2 w-12 h-12 grid place-items-center rounded-full bg-white/90 text-gray-800 hover:bg-white shadow-md ring-1 ring-black/10 focus:outline-none focus-visible:ring-4 focus-visible:ring-[#CF4520]/40">
              <i class="fa-solid fa-chevron-right text-lg"></i>
            </button>
            <div class="absolute left-3 bottom-3 inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-sm font-semibold bg-white/90 text-gray-800 shadow">
              <i class="fa-regular fa-image text-base"></i>
              <span x-text="(idx+1) + ' / ' + slides.length"></span>
            </div>
          </div>

          <div class="bg-white/90 border-t border-black/10 px-3 py-2 overflow-x-auto">
            <div class="flex items-center gap-2">
              <template x-for="(s,i) in slides" :key="i">
                <button type="button" @click="set(i)" class="group relative rounded-xl overflow-hidden border"
                        :class="i===idx ? 'border-[#CF4520]' : 'border-transparent hover:border-gray-200'">
                  <span class="absolute inset-0 ring-2 rounded-xl transition"
                        :class="i===idx ? 'ring-[#CF4520]' : 'ring-transparent group-hover:ring-black/5'"></span>
                  <img :src="s.src" :alt="s.alt" class="{{ $thumbCls }}" loading="lazy">
                </button>
              </template>
            </div>
          </div>

          {{-- Lightbox --}}
          <div
            x-show="lightbox"
            x-transition.opacity
            x-cloak
            class="fixed inset-0 z-[9999] bg-black/80 backdrop-blur-sm flex flex-col items-center justify-center p-4"
            @click.self="lightbox=false"
            @keydown.escape.window="lightbox=false"
            role="dialog" aria-modal="true" aria-label="Image lightbox"
          >
            <div class="relative w-full max-w-6xl" @click.stop>
              <img :src="slides[idx].src" :alt="slides[idx].alt" class="w-full h-auto rounded-xl shadow-2xl">
              <button @click.stop="prev()" class="absolute left-2 top-1/2 -translate-y-1/2 w-10 h-10 sm:w-11 sm:h-11 grid place-items-center rounded-full bg-white/90 text-gray-800 hover:bg-white shadow ring-1 ring-black/10 focus:outline-none focus-visible:ring-4 focus-visible:ring-[#CF4520]/40" aria-label="Previous image">
                <i class="fa-solid fa-chevron-left text-base sm:text-lg"></i>
              </button>
              <button @click.stop="next()" class="absolute right-2 top-1/2 -translate-y-1/2 w-10 h-10 sm:w-11 sm:h-11 grid place-items-center rounded-full bg-white/90 text-gray-800 hover:bg-white shadow ring-1 ring-black/10 focus:outline-none focus-visible:ring-4 focus-visible:ring-[#CF4520]/40" aria-label="Next image">
                <i class="fa-solid fa-chevron-right text-base sm:text-lg"></i>
              </button>
              <button @click="lightbox=false" class="absolute -top-4 -right-4 w-11 h-11 grid place-items-center rounded-full bg-white/95 text-gray-900 shadow hover:bg-white ring-1 ring-black/10 focus:outline-none focus-visible:ring-4 focus-visible:ring-[#CF4520]/40" aria-label="Close">
                <i class="fa-solid fa-xmark text-lg"></i>
              </button>
            </div>
            <div class="mt-4 w-full max-w-6xl" @click.stop>
              <div class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 gap-2">
                <template x-for="(s,i) in slides" :key="'lb2-'+i">
                  <button @click.stop="set(i)" class="relative rounded-lg overflow-hidden border"
                          :class="i===idx ? 'border-white/70' : 'border-white/30 hover:border-white/50'">
                    <img :src="s.src" :alt="s.alt" class="{{ $mGridThumb }}">
                    <span class="absolute inset-0 ring-2 rounded-lg" :class="i===idx ? 'ring-white' : 'ring-transparent'"></span>
                  </button>
                </template>
              </div>
            </div>
          </div>
          {{-- /Lightbox --}}
        </div>
      </div>
    </div>
  </div>


  {{-- ==================  FITNESS TRAIL  ================== --}}
  <div class="mx-auto max-w-[1600px] px-4 sm:px-6 lg:px-8 mt-10 mb-20 md:mt-14">
    <div class="grid grid-cols-12 gap-8 items-center">
      {{-- Gallery (L) --}}
      <div class="col-span-12 lg:col-span-7">
        <div
          x-data="amenitySlider({
            slides: [
              { src: @js(asset('images/Amenities/fitness.webp')), alt:'Astoria Bohol fitness trail' },

            ]
          })"
          class="relative rounded-2xl overflow-hidden border border-black/10 bg-white shadow-sm"
        >
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
                     focus:outline-none focus-visible:ring-4 focus-visible:ring-[#CF45205]/40">
              <i class="fa-solid fa-chevron-right text-lg"></i>
            </button>

            {{-- index pill --}}
            <div class="absolute left-3 bottom-3 inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-sm font-semibold bg-white/90 text-gray-800 shadow">
              <i class="fa-regular fa-image text-base"></i>
              <span x-text="(idx+1) + ' / ' + slides.length"></span>
            </div>
          </div>

          {{-- Thumbnails --}}
          <div class="bg-white/90 border-t border-black/10 px-3 py-2 overflow-x-auto">
            <div class="flex items-center gap-2">
              <template x-for="(s,i) in slides" :key="i">
                <button type="button" @click="set(i)"
                        class="group relative rounded-xl overflow-hidden border"
                        :class="i===idx ? 'border-[#CF4520]' : 'border-transparent hover:border-gray-200'">
                  <span class="absolute inset-0 ring-2 rounded-xl transition"
                        :class="i===idx ? 'ring-#CF4520]' : 'ring-transparent group-hover:ring-black/5'"></span>
                  <img :src="s.src" :alt="s.alt" class="{{ $thumbCls }}" loading="lazy">
                </button>
              </template>
            </div>
          </div>

          {{-- Lightbox --}}
          <div
            x-show="lightbox"
            x-transition.opacity
            x-cloak
            class="fixed inset-0 z-[9999] bg-black/80 backdrop-blur-sm flex flex-col items-center justify-center p-4"
            @click.self="lightbox=false"
            @keydown.escape.window="lightbox=false"
            role="dialog" aria-modal="true" aria-label="Image lightbox"
          >
            <div class="relative w-full max-w-6xl" @click.stop>
              <img :src="slides[idx].src" :alt="slides[idx].alt" class="w-full h-auto rounded-xl shadow-2xl">
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
                             focus:outline:none focus-visible:ring-4 focus-visible:ring-[#CF4520]/40"
                      aria-label="Next image">
                <i class="fa-solid fa-chevron-right text-base sm:text-lg"></i>
              </button>
              <button @click="lightbox=false"
                      class="absolute -top-4 -right-4 w-11 h-11 grid place-items-center rounded-full
                             bg-white/95 text-gray-900 shadow hover:bg-white ring-1 ring-black/10
                             focus:outline-none focus-visible:ring-4 focus-visible:ring-[#CF4520]/40"
                      aria-label="Close">
                <i class="fa-solid fa-xmark text-lg"></i>
              </button>
            </div>

            <div class="mt-4 w-full max-w-6xl" @click.stop>
              <div class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 gap-2">
                <template x-for="(s,i) in slides" :key="'lb1-'+i">
                  <button @click.stop="set(i)"
                          class="relative rounded-lg overflow-hidden border"
                          :class="i===idx ? 'border-white/70' : 'border-white/30 hover:border-white/50'">
                    <img :src="s.src" :alt="s.alt" class="{{ $mGridThumb }}">
                    <span class="absolute inset-0 ring-2 rounded-lg" :class="i===idx ? 'ring-white' : 'ring-transparent'"></span>
                  </button>
                </template>
              </div>
            </div>
          </div>
          {{-- /Lightbox --}}
        </div>
      </div>

      {{-- Copy (R) --}}
      <div class="col-span-12 lg:col-span-5">
        <div class="flex items-end gap-4 mt-1">
          <h3 class="fx fx-right text-3xl sm:text-4xl md:text-5xl lg:text-4xl font-semibold tracking-tight text-[#CF4520]" style="--d:120ms">
           FITNESS TRAIL



          </h3>
          <span class="fx fx-right h-1 w-24 lg:w-28 rounded-full bg-[#63666A] mb-2 hidden sm:block" style="--d:200ms"></span>
        </div>

        <p class="mt-4 text-[15px] leading-7 lg:text-lg lg:leading-8 text-[#25282a]/90">
         Keep up with your exercise goals and get some much needed vitamin D while hiking down our fitness trail in the early mornings or afternoons. With 10 stations, you are sure never to be bored as you take the scenic 10-minute walk.

        </p>

        {{-- One-liner attributes + contacts --}}
        <ul class="{{ $ulCls }}">
      
          {{-- Operating hours --}}
          <li class="flex items-center gap-2">
            <i class="fa-regular fa-clock {{ $liIcon }}"></i>
            <span>6:00 AM – 5:00 PM</span>
          </li>
        </ul>

      </div>
    </div>
  </div>

  


  {{-- local animation styles --}}
  <style>
    #amenities-fun-fitness .fx{
      opacity:.001; transform:translateX(-14px);
      will-change:transform,opacity;
      transition: opacity 520ms ease, transform 640ms cubic-bezier(.22,1,.36,1);
      transition-delay: var(--d, 0ms);
    }
    #amenities-fun-fitness.is-in .fx{ opacity:1; transform:translateX(0); }
    @media (prefers-reduced-motion: reduce){
      #amenities-fun-fitness .fx{ opacity:1 !important; transform:none !important; transition:none !important; }
    }
  </style>
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
