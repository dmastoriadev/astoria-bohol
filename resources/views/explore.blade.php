@extends('layouts.app') 

@section('title', 'Explore | Astoria Bohol')

@push('head')
  {{-- Font Awesome (no SRI to avoid mismatch) --}}
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
@endpush

@section('content')

<section
  id="explore-hero-angled-left-55"
  class="relative w-full overflow-hidden text-[#25282a] bg-white"
  aria-label="About Astoria Hotels and Resorts hero banner"
  x-data="{ loaded:false }"
  x-init="requestAnimationFrame(()=>{ loaded=true })"
  :class="loaded ? 'is-in' : ''"
>
  {{-- DESKTOP BG (angled image) --}}
  <div class="absolute inset-0 right-clip hidden lg:block">
    <img
      src="{{ asset('images/explore-header.webp') }}"
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
         WELCOME TO OLD WORLD CULTURE

        </p>

        {{-- HEADER (teal) --}}
        <h1 class="fx fx-right font-semibold leading-tight tracking-tight text-3xl sm:text-4xl md:text-5xl mt-2" style="--d:100ms">
          <span class="bg-clip-text text-transparent bg-gradient-to-r from-[#CF4520] via-[#CF4520] to-[#CF4520]/85">
        EXPLORE



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
        Experience Bohol’s natural heritage through different fun activities





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
        src="{{ asset('images/explore-header.webp') }}"
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
       WELCOME TO OLD WORLD CULTURE



        </p>

        {{-- HEADER (teal) --}}
        <h1 class="fx fx-right font-semibold leading-tight tracking-tight text-5xl lg:text-6xl mt-2" style="--d:360ms">
          <span class="bg-clip-text text-transparent bg-gradient-to-r from-[#CF4520] via-[#CF4520] to-[#CF4520]/80">
         EXPLORE



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
        Experience Bohol’s natural heritage through different fun activities




        </p>
      </div>
    </div>

    <span aria-hidden="true" class="pointer-events-none absolute inset-0 ring-1 ring-inset ring-white/10"></span>
  </div>

  <style>
    /* Default: no clipping for non-desktop */
    #explore-hero-angled-left-55 .left-clip,
    #explore-hero-angled-left-55 .right-clip {
      clip-path: none;
      -webkit-clip-path: none;
    }

    /* Desktop (lg+): 55° seam */
    @media (min-width: 1024px){
      #explore-hero-angled-left-55 .left-clip{
        clip-path: polygon(0% 0%, 70% 0%, 30% 100%, 0% 100%);
        -webkit-clip-path: polygon(0% 0%, 70% 0%, 30% 100%, 0% 100%);
      }
      #explore-hero-angled-left-55 .right-clip{
        clip-path: polygon(70% 0%, 100% 0%, 100% 100%, 30% 100%);
        -webkit-clip-path: polygon(70% 0%, 100% 0%, 100% 100%, 30% 100%);
      }

    #explore-hero-angled-left-55 .right-clip > img.bg-shift{
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
      #explore-hero-angled-left-55 .tablet-stack {
        display: grid !important;
        min-height: 900px !important;
        padding-top: 32px !important;
      }
      #explore-hero-angled-left-55 .desktop-angled,
      #explore-hero-angled-left-55 .right-clip {
        display: none !important;
      }
      #explore-hero-angled-left-55 .left-clip,
      #explore-hero-angled-left-55 .right-clip {
        clip-path: none !important;
        -webkit-clip-path: none !important;
      }
    }
    @media (min-width:1024px) and (max-width:1368px) and (aspect-ratio: 3/4),
           (min-width:1194px) and (max-width:1368px) and (aspect-ratio: 4/3) {
      #explore-hero-angled-left-55 .tablet-stack {
        display: grid !important;
        min-height: 900px !important;
        padding-top: 32px !important;
      }
      #explore-hero-angled-left-55 .desktop-angled,
      #explore-hero-angled-left-55 .right-clip {
        display: none !important;
      }
      #explore-hero-angled-left-55 .left-clip,
      #explore-hero-angled-left-55 .right-clip {
        clip-path: none !important;
        -webkit-clip-path: none !important;
      }
    }

    /* Entrance FX for text */
    #explore-hero-angled-left-55 .fx{
      opacity: .001;
      transform: translateX(-14px);
      will-change: transform, opacity;
      transition:
        opacity 520ms ease,
        transform 640ms cubic-bezier(.22,1,.36,1);
      transition-delay: var(--d, 0ms);
    }
    #explore-hero-angled-left-55.is-in .fx{
      opacity: 1;
      transform: translateX(0);
    }

    /* Slide-in FX for vectors (top + bottom) – from left to right, slower */
    #explore-hero-angled-left-55 .vector-top-fx,
    #explore-hero-angled-left-55 .vector-btm-mobile-fx,
    #explore-hero-angled-left-55 .vector-btm-desktop-fx{
      opacity: 0;
      transform: translateX(-32px);
      will-change: transform, opacity;
      transition:
        opacity 700ms ease,
        transform 950ms cubic-bezier(.16,1,.3,1);
    }

    #explore-hero-angled-left-55.is-in .vector-top-fx{
      opacity: 1;
      transform: translateX(0);
      transition-delay: 260ms;
    }

    #explore-hero-angled-left-55.is-in .vector-btm-mobile-fx{
      opacity: 1;
      transform: translateX(0);
      transition-delay: 320ms;
    }

    #explore-hero-angled-left-55.is-in .vector-btm-desktop-fx{
      opacity: 1;
      transform: translateX(0);
      transition-delay: 320ms;
    }

    /* Extra safety: very small phones (optional tweak if needed) */
    @media (max-width: 400px){
      #explore-hero-angled-left-55 .vector-btm-mobile-fx img{
        width: 6rem;
      }
    }
    @media (max-width: 360px){
      #explore-hero-angled-left-55 .vector-btm-mobile-fx{
        display: none;
      }
    }

    @media (prefers-reduced-motion: reduce){
      #explore-hero-angled-left-55 .fx{
        opacity: 1 !important;
        transform: none !important;
        transition: none !important;
      }
      #explore-hero-angled-left-55 img{
        transition: none !important;
        transform: none !important;
      }
      #explore-hero-angled-left-55 .vector-top-fx,
      #explore-hero-angled-left-55 .vector-btm-mobile-fx,
      #explore-hero-angled-left-55 .vector-btm-desktop-fx{
        opacity: 1 !important;
        transform: none !important;
        transition: none !important;
      }
    }
  </style>
</section>




{{-- ===== Title block (2-8-2) ===== --}}
<div class="mx-auto max-w-[1600px] px-4 sm:px-6 lg:px-8 pt-10 lg:pt-20 lg:pb-15 md:pt-10">
  <div class="grid grid-cols-12">
    <div class="hidden lg:block lg:col-span-2"></div>

    <div class="col-span-12 lg:col-span-8 text-center">
        <p class="fx fx-right uppercase tracking-[.28em] text-xs md:text-sm font-semibold text-[#CF4520]" style="--d:40ms">
      CULTURAL TROVES, ISLAND WONDERS



      </p>

      <div class="mt-2">
        <h2 class="fx fx-right text-3xl md:text-5xl font-semibold" style="--d:120ms">
          <span class="bg-clip-text text-transparent bg-[#CF4520]">
         DISCOVER BOHOL




          </span>
      </div>

      <p class="fx fx-right mt-3  md:mt-4 text-sm md:text-base lg:text-lg font-medium text-[#25282a]/90" style="--d:200ms">
      In Astoria Bohol, you will never run out of things to do. With recreation such as water bikes, kayaking, stand up paddle boarding, bamboo and fat bikes, your whole family can enjoy the exclusivity that our resort offers. Choose how you want to relax today!


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
   TOURS — Static (no gallery modal)
========================= --}}
<section id="bohol-tours" class="relative bg-white py-10 md:py-14 text-[#25282a]">
  <style>
    #bohol-tours .accent { color:#CF4520; }
    #bohol-tours .ring-accent { box-shadow: 0 0 0 1px rgba(207,69,32,.25) inset; }
    #bohol-tours .chip { background:rgba(207,69,32,.08); border:1px solid rgba(207,69,32,.22); }
  </style>

  @php
    $contactHref = \Illuminate\Support\Facades\Route::has('contact')
      ? route('contact')
      : url('/contact');

    $tours = [
      [
        'id' => 'island-hopping',
        'title' => 'ISLAND HOPPING & DOLPHIN WATCHING',
        'desc'  => 'Take a boat ride and island hop to Balicasag Island and Isola di Francesco or the Virgin Island in Bohol. Have a whale of a time going dolphin watching and revel in the beauty of the sleek sea creatures.',
        'highlights' => [
          ['icon' => 'fa-umbrella-beach', 'label' => 'Balicasag Island'],
          ['icon' => 'fa-water',          'label' => 'Isola di Francesco / Virgin Island'],
          ['icon' => 'fa-binoculars',     'label' => 'Dolphin watching'],
        ],
        'img' => asset('images/Explore/island.webp'),
      ],
      [
        'id' => 'panglao-tour',
        'title' => 'PANGLAO TOUR',
        'desc'  => 'Take a tour around the southern side of Bohol in the picturesque Panglao area. Visit the Bohol Bee Farm to watch how honey is made and bring home a fresh bottle. Take a trip to the beautiful sites of Panglao Tower, Hinagdanan Cave, and Dauis Church.',
        'highlights' => [
          ['icon' => 'fa-seedling',          'label' => 'Bohol Bee Farm'],
          ['icon' => 'fa-tower-observation', 'label' => 'Panglao Tower'],
          ['icon' => 'fa-mountain',          'label' => 'Hinagdanan Cave'],
          ['icon' => 'fa-church',            'label' => 'Dauis Church'],
        ],
        'img' => asset('images/Explore/panglao.webp'),
      ],
      [
        'id' => 'danao-tour',
        'title' => 'DANAO TOUR',
        'desc'  => 'If you are ready for an active Bohol adventure, then this Danao tour is the right one for you. Go sky riding in an open-air cable car to enjoy the cool breeze or take the ziplet ride for a thrill. For the ultimate adrenaline rush, say yes to being swung to a cliffside or dropped in one of the highest canyon swings in Southeast Asia.',
        'highlights' => [
          ['icon' => 'fa-cable-car',      'label' => 'Sky riding (cable car)'],
          ['icon' => 'fa-person-falling', 'label' => 'Canyon swing thrills'],
          ['icon' => 'fa-route',          'label' => 'Ziplet ride adventure'],
        ],
        'img' => asset('images/Explore/danao.webp'),
      ],
      [
        'id' => 'countryside-tour',
        'title' => 'COUNTRYSIDE TOUR',
        'desc'  => "Take a trip down Bohol's cultural memory lane and enrich yourself with learning about local history by visiting some of the province's most famous sites, including the Chocolate Hills, Blood Compact Monument, and Baclayon Church and Museum. Experience the well-loved Loboc River Cruise too, to complete your tour.",
        'highlights' => [
          ['icon' => 'fa-mountain-sun', 'label' => 'Chocolate Hills'],
          ['icon' => 'fa-landmark',     'label' => 'Blood Compact Monument'],
          ['icon' => 'fa-church',       'label' => 'Baclayon Church & Museum'],
          ['icon' => 'fa-water',        'label' => 'Loboc River Cruise'],
        ],
        'img' => asset('images/Explore/country.webp'),
      ],
    ];
  @endphp

  <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">
    {{-- Header --}}
    <header class="flex flex-col md:flex-row md:items-end md:justify-between gap-5">
      <div>
        <p class="text-xs md:text-sm font-semibold tracking-[.28em] uppercase text-[#3F2021]">
          Explore
        </p>
        <h2 class="mt-2 text-2xl md:text-3xl lg:text-4xl font-semibold leading-tight tracking-tight">
          TOUR THE WONDERS OF BOHOL ISLAND
        </h2>
        <p class="mt-2 text-sm md:text-[15px] lg:text-base text-gray-700 max-w-3xl">
          Interested to try the fun activities? Browse the tours below, then inquire to reserve ahead.
        </p>
      </div>

      
    </header>

    {{-- Tour cards --}}
    <div class="mt-8 grid gap-6 lg:gap-8">
      @foreach($tours as $t)
        <article
          id="tour-{{ $t['id'] }}"
          class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden"
        >
          <div class="grid grid-cols-1 lg:grid-cols-12">
            {{-- Image --}}
            <div class="lg:col-span-5 relative">
              <div class="aspect-[16/10] lg:aspect-[16/12] w-full bg-gray-100">
                <img
                  src="{{ $t['img'] }}"
                  alt="{{ $t['title'] }}"
                  class="w-full h-full object-cover"
                  loading="lazy"
                  decoding="async"
                  onerror="this.onerror=null; this.src='{{ asset('images/placeholder/post-wide.jpg') }}';"
                >
              </div>
              <div class="absolute inset-0 pointer-events-none bg-gradient-to-t from-black/35 via-transparent to-transparent"></div>

              <div class="absolute left-4 bottom-4 inline-flex items-center gap-2 rounded-full bg-black/70 text-white px-3 py-1.5 text-[11px] font-semibold">
                <i class="fa-solid fa-location-dot text-[#CF4520]"></i>
                ASTORIA BOHOL TOURS
              </div>
            </div>

            {{-- Content (RIGHT) — vertically centered on lg+ --}}
            <div class="lg:col-span-7 flex">
              <div class="w-full p-5 md:p-6 lg:p-8 flex flex-col justify-center">
                <div class="flex flex-col gap-3">
                  <div class="flex flex-wrap items-start justify-between gap-3">
                    <h3 class="text-lg md:text-xl lg:text-2xl font-semibold tracking-tight leading-tight">
                      {{ $t['title'] }}
                    </h3>
                    <span class="inline-flex items-center gap-2 rounded-full bg-[#CF4520]/10 text-[#CF4520] px-3 py-1 text-[11px] font-bold border border-[#CF4520]/30">
                      <i class="fa-solid fa-compass"></i>
                      TOUR
                    </span>
                  </div>

                  <p class="text-sm md:text-[15px] lg:text-base text-gray-700 font-medium">
                    {{ $t['desc'] }}
                  </p>

                  {{-- Highlights (each has its own FA icon) --}}
                  <div class="mt-1 flex flex-wrap gap-2">
                    @foreach($t['highlights'] as $h)
                      <span class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-gray-50 px-3 py-1.5 text-base md:text-base font-medium text-gray-800">
                        <i class="fa-solid {{ $h['icon'] }} text-[#CF4520]"></i>
                        {{ $h['label'] }}
                      </span>
                    @endforeach
                  </div>

                  {{-- CTAs --}}
                  <div class="mt-4 flex flex-wrap items-center gap-3">
                    <a
                      href="#charges-policy"
                      class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2 text-xs md:text-sm font-semibold text-gray-900 hover:border-[#CF4520]/40 hover:bg-[#CF4520]/5 transition ring-accent"
                    >
                      <i class="fa-solid fa-circle-info text-[#CF4520]"></i>
                      Know More
                    </a>

                    <a
                      href="{{ $contactHref }}"
                      class="inline-flex items-center gap-2 rounded-xl bg-[#CF4520] px-4 py-2 text-xs md:text-sm font-semibold text-white hover:bg-[#3F2021] transition"
                    >
                      <i class="fa-solid fa-paper-plane"></i>
                      INQUIRE NOW
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </article>
      @endforeach
    </div>

    {{-- Charges / Policies --}}
    <section id="charges-policy" class="mt-10 grid gap-6 lg:grid-cols-12">
      <div class="lg:col-span-7 rounded-2xl border border-gray-200 bg-white p-5 md:p-6 shadow-sm">
        <h4 class="text-lg md:text-xl font-semibold tracking-tight">
          <i class="fa-solid fa-receipt text-[#CF4520] mr-2"></i>
          Tour Charges and Cancellation Policy
        </h4>

        <ul class="mt-3 space-y-2 text-sm md:text-[15px] text-gray-700">
          <li class="flex items-start gap-3">
            <i class="fa-solid fa-calendar-day text-[#CF4520] mt-0.5"></i>
            <span><strong>1 to 3-day notice</strong> prior to scheduled tour: Full rate shall be charged.</span>
          </li>
          <li class="flex items-start gap-3">
            <i class="fa-solid fa-calendar-week text-[#CF4520] mt-0.5"></i>
            <span><strong>4 to 7-day notice</strong> prior to scheduled tour: Half of the total rate shall be charged.</span>
          </li>
          <li class="flex items-start gap-3">
            <i class="fa-solid fa-calendar-check text-[#CF4520] mt-0.5"></i>
            <span><strong>8-day notice</strong> prior to scheduled tour or more: Total refund shall apply.</span>
          </li>
        </ul>

        <div class="mt-5 rounded-xl bg-[#CF4520]/5 border border-[#CF4520]/20 p-4">
          <p class="text-sm md:text-[15px] font-semibold text-[#3F2021] flex items-center gap-2">
            <i class="fa-solid fa-circle-exclamation text-[#CF4520]"></i>
            Advance booking is a must!
          </p>
          <p class="mt-1 text-sm md:text-[15px] text-gray-700">
            Due to limited number of land and local tour operators available on the island of Bohol, especially during peak season,
            it is highly recommended to book your preferred tour prior to your arrival. This is to ensure that you will experience
            the best of what the island has to offer during your stay at Astoria Bohol.
          </p>
        </div>
      </div>

      <div class="lg:col-span-5 rounded-2xl border border-gray-200 bg-white p-5 md:p-6 shadow-sm">
        <h4 class="text-lg md:text-xl font-semibold tracking-tight">
          <i class="fa-solid fa-van-shuttle text-[#CF4520] mr-2"></i>
          Transfers &amp; Rentals
        </h4>

        <div class="mt-4 grid gap-4">
          <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
            <p class="text-sm font-bold tracking-wide uppercase text-[#CF4520] flex items-center gap-2">
              <i class="fa-solid fa-car-side"></i>
              Van Rental (Drop-off/Pick-up)
            </p>
            <div class="mt-2 space-y-1 text-sm md:text-[15px] text-gray-700">
              <p class="flex gap-2 items-start">
                <i class="fa-solid fa-ship text-[#CF4520] mt-0.5"></i>
                <span><strong>Tagbilaran City (Seaport):</strong> Php 800/way, good for 6 to 8 persons</span>
              </p>
              <p class="flex gap-2 items-start">
                <i class="fa-solid fa-city text-[#CF4520] mt-0.5"></i>
                <span><strong>Tagbilaran City (Downtown):</strong> Php 500/way, good for 6 to 8 persons</span>
              </p>
              <p class="flex gap-2 items-start">
                <i class="fa-solid fa-hourglass-half text-[#CF4520] mt-0.5"></i>
                <span><strong>Waiting charge:</strong> Php 100/hour</span>
              </p>
            </div>
          </div>

          <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
            <p class="text-sm font-bold tracking-wide uppercase text-[#CF4520] flex items-center gap-2">
              <i class="fa-solid fa-plane-arrival"></i>
              Airport/Pier Transfers (Drop-off/Pick-up)
            </p>
            <p class="mt-1 text-base text-gray-800 flex items-center gap-2">
              <i class="fa-regular fa-clock text-[#CF4520]"></i>
              *with 1 hour waiting time
            </p>
            <div class="mt-2 space-y-1 text-sm md:text-[15px] text-gray-700">
              <p class="flex gap-2 items-start">
                <i class="fa-solid fa-location-dot text-[#CF4520] mt-0.5"></i>
                <span><strong>Panglao:</strong> Php 2,000/way, good for 6 to 8 persons</span>
              </p>
              <p class="flex gap-2 items-start">
                <i class="fa-solid fa-hourglass-half text-[#CF4520] mt-0.5"></i>
                <span><strong>Excess waiting charge:</strong> Php 100/hour</span>
              </p>
            </div>
          </div>
        </div>

        <div class="mt-5">
          <h5 class="text-sm md:text-base font-semibold text-[#3F2021] flex items-center gap-2">
            <i class="fa-solid fa-clipboard-list text-[#CF4520]"></i>
            Transfer Charges and Cancellation Policy
          </h5>
          <ul class="mt-2 space-y-2 text-sm md:text-[15px] text-gray-700">
            <li class="flex items-start gap-3">
              <i class="fa-solid fa-calendar-xmark text-[#CF4520] mt-0.5"></i>
              <span><strong>On the same day:</strong> Full rate shall be charged.</span>
            </li>
            <li class="flex items-start gap-3">
              <i class="fa-solid fa-calendar-check text-[#CF4520] mt-0.5"></i>
              <span><strong>2-day notice</strong> prior to scheduled transfer or more: Total refund shall apply.</span>
            </li>
          </ul>
        </div>
      </div>
    </section>

    {{-- Form CTA --}}
    <section class="mt-10 rounded-2xl overflow-hidden border border-gray-200 bg-white shadow-sm">
      <div class="grid grid-cols-1 lg:grid-cols-12">
        <div class="lg:col-span-5 bg-gradient-to-b from-[#3F2021] to-black text-white p-6 md:p-8">
          <p class="text-xs font-semibold tracking-[.28em] uppercase text-white/70">Ready to plan?</p>
          <h3 class="mt-2 text-2xl md:text-3xl font-semibold leading-tight">
            Interested to try the fun activities?
          </h3>
          <p class="mt-2 text-sm md:text-[15px] text-white/80">
            Fill up the form and our team will get back to you with the next steps.
          </p>

          <div class="mt-5 inline-flex items-center gap-2 rounded-xl bg-white/10 px-4 py-3 border border-white/15">
            <i class="fa-solid fa-paper-plane text-[#CF4520]"></i>
            <span class="text-sm font-semibold">INQUIRE NOW</span>
          </div>
        </div>

        <div class="lg:col-span-7 p-6 md:p-8">
          {{-- HUBSPOT FORM (Astoria Bohol) --}}
          <div class="max-w-2xl">
            <div class="mb-3">
              <p class="text-sm font-semibold text-[#3F2021] flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square text-[#CF4520]"></i>
                Inquiry Form
              </p>
              <p class="text-sm text-gray-600">Please complete the fields below.</p>
            </div>

            <div id="hubspot-abohol-form" class="min-h-[240px]"></div>

            @push('scripts')
            <script charset="utf-8" type="text/javascript" src="//js-na2.hsforms.net/forms/embed/v2.js"></script>
            <script>
                (function initHubspotForm(){
                // Wait until HubSpot script is ready (safe even if script loads slowly)
                if (!(window.hbspt && window.hbspt.forms && typeof window.hbspt.forms.create === 'function')) {
                    return setTimeout(initHubspotForm, 60);
                }

                window.hbspt.forms.create({
                    region: "na2",
                    portalId: "21911373",
                    formId: "eed685a3-d8e9-49dd-a476-bee132f94a9d",
                    target: "#hubspot-abohol-form"
                });
                })();
            </script>
            @endpush


            @push('scripts')
              <script charset="utf-8" type="text/javascript" src="//js.hsforms.net/forms/embed/v2.js"></script>
              <script>
                document.addEventListener('DOMContentLoaded', function () {
                  if (!window.hbspt || !window.hbspt.forms) return;

                  // Replace with your real HubSpot values for Astoria Bohol:
                  hbspt.forms.create({
                    region: "na1",
                    portalId: "REPLACE_WITH_PORTAL_ID",
                    formId: "REPLACE_WITH_FORM_ID",
                    target: "#hubspot-abohol-form"
                  });
                });
              </script>
            @endpush
          </div>
        </div>
      </div>
    </section>
  </div>
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
