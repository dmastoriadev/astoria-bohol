@extends('layouts.app')

@section('title', 'About Us | Astoria Bohol')

@section('content')
{{-- === Angled Split — Desktop 55°; Mobile: 3 rows (text / vector / image) for stable vector position === --}}
<section
  id="about-hero-angled-left-55"
  class="relative w-full overflow-hidden text-[#25282a] bg-white"
  aria-label="About Astoria Hotels and Resorts hero banner"
  x-data="{ loaded:false }"
  x-init="requestAnimationFrame(()=>{ loaded=true })"
  :class="loaded ? 'is-in' : ''"
>
  {{-- DESKTOP BG (angled image) --}}
  <div class="absolute inset-0 right-clip hidden lg:block">
    <img
      src="{{ asset('images/About/about-header.webp') }}"
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
         SNEAK PEEK INTO NATURE
        </p>

        {{-- HEADER (teal) --}}
        <h1 class="fx fx-right font-semibold leading-tight tracking-tight text-3xl sm:text-4xl md:text-5xl mt-2" style="--d:100ms">
          <span class="bg-clip-text text-transparent bg-gradient-to-r from-[#CF4520] via-[#CF4520] to-[#CF4520]/85">
            ABOUT US
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
         Hidden within heritage, a wondrous haven awaits



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
        src="{{ asset('images/About/about-header.webp') }}"
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
         SNEAK PEEK INTO NATURE



        </p>

        {{-- HEADER (teal) --}}
        <h1 class="fx fx-right font-semibold leading-tight tracking-tight text-5xl lg:text-6xl mt-2" style="--d:360ms">
          <span class="bg-clip-text text-transparent bg-gradient-to-r from-[#CF4520] via-[#CF4520] to-[#CF4520]/80">
            ABOUT US
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
        Hidden within heritage, a wondrous haven awaits



        </p>
      </div>
    </div>

    <span aria-hidden="true" class="pointer-events-none absolute inset-0 ring-1 ring-inset ring-white/10"></span>
  </div>

  <style>
    /* Default: no clipping for non-desktop */
    #about-hero-angled-left-55 .left-clip,
    #about-hero-angled-left-55 .right-clip {
      clip-path: none;
      -webkit-clip-path: none;
    }

    /* Desktop (lg+): 55° seam */
    @media (min-width: 1024px){
      #about-hero-angled-left-55 .left-clip{
        clip-path: polygon(0% 0%, 70% 0%, 30% 100%, 0% 100%);
        -webkit-clip-path: polygon(0% 0%, 70% 0%, 30% 100%, 0% 100%);
      }
      #about-hero-angled-left-55 .right-clip{
        clip-path: polygon(70% 0%, 100% 0%, 100% 100%, 30% 100%);
        -webkit-clip-path: polygon(70% 0%, 100% 0%, 100% 100%, 30% 100%);
      }

    #about-hero-angled-left-55 .right-clip > img.bg-shift{
        left: 22vw !important;
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
      #about-hero-angled-left-55 .tablet-stack {
        display: grid !important;
        min-height: 900px !important;
        padding-top: 32px !important;
      }
      #about-hero-angled-left-55 .desktop-angled,
      #about-hero-angled-left-55 .right-clip {
        display: none !important;
      }
      #about-hero-angled-left-55 .left-clip,
      #about-hero-angled-left-55 .right-clip {
        clip-path: none !important;
        -webkit-clip-path: none !important;
      }
    }
    @media (min-width:1024px) and (max-width:1368px) and (aspect-ratio: 3/4),
           (min-width:1194px) and (max-width:1368px) and (aspect-ratio: 4/3) {
      #about-hero-angled-left-55 .tablet-stack {
        display: grid !important;
        min-height: 900px !important;
        padding-top: 32px !important;
      }
      #about-hero-angled-left-55 .desktop-angled,
      #about-hero-angled-left-55 .right-clip {
        display: none !important;
      }
      #about-hero-angled-left-55 .left-clip,
      #about-hero-angled-left-55 .right-clip {
        clip-path: none !important;
        -webkit-clip-path: none !important;
      }
    }

    /* Entrance FX for text */
    #about-hero-angled-left-55 .fx{
      opacity: .001;
      transform: translateX(-14px);
      will-change: transform, opacity;
      transition:
        opacity 520ms ease,
        transform 640ms cubic-bezier(.22,1,.36,1);
      transition-delay: var(--d, 0ms);
    }
    #about-hero-angled-left-55.is-in .fx{
      opacity: 1;
      transform: translateX(0);
    }

    /* Slide-in FX for vectors (top + bottom) – from left to right, slower */
    #about-hero-angled-left-55 .vector-top-fx,
    #about-hero-angled-left-55 .vector-btm-mobile-fx,
    #about-hero-angled-left-55 .vector-btm-desktop-fx{
      opacity: 0;
      transform: translateX(-32px);
      will-change: transform, opacity;
      transition:
        opacity 700ms ease,
        transform 950ms cubic-bezier(.16,1,.3,1);
    }

    #about-hero-angled-left-55.is-in .vector-top-fx{
      opacity: 1;
      transform: translateX(0);
      transition-delay: 260ms;
    }

    #about-hero-angled-left-55.is-in .vector-btm-mobile-fx{
      opacity: 1;
      transform: translateX(0);
      transition-delay: 320ms;
    }

    #about-hero-angled-left-55.is-in .vector-btm-desktop-fx{
      opacity: 1;
      transform: translateX(0);
      transition-delay: 320ms;
    }

    /* Extra safety: very small phones (optional tweak if needed) */
    @media (max-width: 400px){
      #about-hero-angled-left-55 .vector-btm-mobile-fx img{
        width: 6rem;
      }
    }
    @media (max-width: 360px){
      #about-hero-angled-left-55 .vector-btm-mobile-fx{
        display: none;
      }
    }

    @media (prefers-reduced-motion: reduce){
      #about-hero-angled-left-55 .fx{
        opacity: 1 !important;
        transform: none !important;
        transition: none !important;
      }
      #about-hero-angled-left-55 img{
        transition: none !important;
        transform: none !important;
      }
      #about-hero-angled-left-55 .vector-top-fx,
      #about-hero-angled-left-55 .vector-btm-mobile-fx,
      #about-hero-angled-left-55 .vector-btm-desktop-fx{
        opacity: 1 !important;
        transform: none !important;
        transition: none !important;
      }
    }
  </style>
</section>



{{-- ===== Homegrown Hospitality (strict 50/50; LEFT = all content; RIGHT = edge-to-edge image) ===== --}}
<section id="homegrown-hospitality" class="relative w-full overflow-hidden bg-white text-[#25282a]" aria-labelledby="hgh-title">
  {{-- soft accents --}}
  <span aria-hidden="true" class="pointer-events-none absolute -left-20 -top-20 h-64 w-64 rounded-full bg-[#3F2021]/10 blur-3xl"></span>
  <span aria-hidden="true" class="pointer-events-none absolute right-[-3rem] bottom-[-3rem] h-72 w-72 rounded-full bg-[#3F2021]/10 blur-3xl"></span>

  <div class="relative mx-auto max-w-[1600px] px-4 sm:px-6 lg:px-8 py-16 md:py-20">
    {{-- 50/50: Left = ALL CONTENT (incl. headers); Right = PURE IMAGE (covers cell) --}}
    <div class="grid md:grid-cols-2 gap-0 md:items-center">
      {{-- LEFT: eyebrow + heading + divider + description + rest of copy (center vertically on desktop) --}}
      <div class="pr-0 md:pr-8 lg:pr-12 ev-reveal md:flex md:flex-col md:justify-center" data-ev-reveal style="--ev-delay:60ms;">
        <p class="text-sm md:text-base font-semibold tracking-[.28em] uppercase" style="color:#3F2021;">
        ESCAPE TO AN IDYLLIC PARADISE


        <h2 id="hgh-title" class="mt-2 text-3xl sm:text-4xl md:text-5xl font-semibold leading-[1.05] tracking-tight">
          <span class="bg-clip-text text-transparent bg-gradient-to-r bg-[#CF4520]">
         EXPERIENCE THE BEAUTY OF HERITAGE


          </span>
        </h2>
        <div class="mt-4 h-1 w-20 rounded-full" style="background-color:#3F2021;"></div>

        <p class="mt-6 text-base sm:text-lg md:text-xl text-[#25282a]/90">
        With its quaintness and charm, Astoria Bohol provides a retreat to those who seek privacy and exclusivity. Our safe haven in the heart of Baclayon pays homage to the province’s local culture. With its stately mansion façade, the neutral-colored walls and interiors reflect the rich traditions and history of Bohol.
        </p>

      </div>

      {{-- RIGHT: image ONLY; add margin-top on mobile to separate from text, zero on tablet/desktop --}}
      <figure class="relative mt-5 md:mt-0 min-h-[260px] sm:min-h-[360px] md:min-h-[520px] lg:min-h-[600px] xl:min-h-[680px] ev-reveal" data-ev-reveal style="--ev-delay:140ms;">
        <img
          src="{{ asset('images/About/experience.webp') }}"
          class="absolute inset-0 w-full h-full object-cover select-none pointer-events-none"
          loading="lazy" decoding="async"
        />
      </figure>
    </div>
  </div>
</section>

{{-- Fade-in entrance (lightweight) --}}
<style>
  .ev-reveal{
    opacity:0;
    transform: translateZ(0);
    transition: opacity 600ms ease;
    transition-delay: var(--ev-delay, 0ms);
    will-change: opacity;
  }
  .ev-reveal.is-in{ opacity:1; }
  @media (prefers-reduced-motion: reduce){
    .ev-reveal{ transition:none; opacity:1; }
  }
</style>
<script>
  (function () {
    const scope = document.currentScript.closest('section') || document;
    const els = scope.querySelectorAll('[data-ev-reveal]');
    if (!('IntersectionObserver' in window)) { els.forEach(el => el.classList.add('is-in')); return; }
    const io = new IntersectionObserver((entries, obs) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) { entry.target.classList.add('is-in'); obs.unobserve(entry.target); }
      });
    }, { threshold: 0.15, rootMargin: '0px 0px -10% 0px' });
    els.forEach(el => io.observe(el));
  })();
</script>


{{-- ===== Our Astoria Promise (full-screen, cover background, FULL-WIDTH CONTENT) ===== --}}
<section id="astoria-ethos"
         class="relative w-full min-h-[100svh] overflow-hidden text-white"
         aria-labelledby="ethos-title">

  {{-- Background image + subtle overlay --}}
  <div class="absolute inset-0 -z-10">
    <div class="absolute inset-0 bg-center bg-cover will-change-transform transition-transform duration-[900ms] ease-[cubic-bezier(.22,1,.36,1)] transform-gpu"
         style="background-image:url('{{ asset('images/About/promise.webp') }}');"></div>
    <div class="absolute inset-0 bg-gradient-to-b from-black/40 via-black/20 to-black/60"></div>
  </div>

  {{-- Content (FULL WIDTH, vertically centered; padding only at edges) --}}
  <div class="relative flex items-center min-h-[100svh]">
    <div class="w-[1500px] max-w-none mx-auto px-4 sm:px-6 lg:px-8">
      <div class="ev-reveal" data-ev-reveal style="--ev-delay: 80ms;">
        <h2 id="ethos-title"
            class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-semibold leading-[1.05] tracking-tight">
         OUR ASTORIA PROMISE
        </h2>

        <div class="mt-4 h-1.5 w-[clamp(140px,22vw,120px)] rounded-full" style="background-color:#CF4520;"></div>

        <p class="mt-6 text-base sm:text-lg md:text-xl text-white/90">
          Enjoy a wealth of modern luxuries when you stay at Astoria Plaza. Our hotel rooms in Ortigas have the perfect mix of formal and luxury amenities for a productive and comfortable stay. Work remotely at the business center, stay in shape at our gym, or unwind at our swimming pool. 

        </p>

        <p class="mt-5 text-base sm:text-lg md:text-xl text-white/90">
          We promise that booking a room with us means reveling in a world-class service experience and ultimate comfort, leisure, and homegrown hospitality like no other.
        </p>
      </div>
    </div>
  </div>
</section>

{{-- Lightweight fade-in helper (scoped) --}}
<style>
  #astoria-ethos .ev-reveal{
    opacity:0; transform: translateZ(0);
    transition: opacity 600ms ease; transition-delay: var(--ev-delay, 0ms);
    will-change: opacity;
  }
  #astoria-ethos .ev-reveal.is-in{ opacity:1; }
  @media (prefers-reduced-motion: reduce){
    #astoria-ethos .ev-reveal{ transition:none; opacity:1; }
  }
</style>
<script>
  (function () {
    const sec = document.getElementById('astoria-ethos');
    if (!sec) return;
    const els = sec.querySelectorAll('[data-ev-reveal]');
    if (!('IntersectionObserver' in window)) { els.forEach(el => el.classList.add('is-in')); return; }
    const io = new IntersectionObserver((entries, obs) => {
      entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('is-in'); obs.unobserve(e.target); } });
    }, { threshold: 0.12 });
    els.forEach(el => io.observe(el));
  })();
</script>


{{-- ================== LOCATION — ASTORIA PLAZA (ORTIGAS) ================== --}}
<section
  id="apz-location"
  class="relative w-full bg-white text-[#25282a] py-16 md:py-20"
  aria-labelledby="apz-location-title"
>
  <div class="mx-auto max-w-[1600px] px-4 sm:px-6 lg:px-8">
    <div class="grid gap-10 lg:gap-12 lg:grid-cols-12 items-start">

      {{-- LEFT: TEXT CONTENT --}}
      <div class="lg:col-span-5 space-y-4">
        <p class="text-sm md:text-base font-semibold tracking-[.28em] uppercase" style="color:#3F2021;">
         RESORT

        </p>

        <h2 id="hgh-title" class="mt-2 text-3xl sm:text-4xl md:text-5xl font-semibold leading-[1.05] tracking-tight">
          <span class="bg-clip-text text-transparent bg-gradient-to-r bg-[#CF4520]">
          LOCATION
        </span>
        </h2>

        <p class="mt-2 text-base sm:text-lg leading-relaxed text-[#25282a]/90">
          Rich in tradition, culture, history and a myriad of natural wonders, the province of Bohol has become one of the Philippines’ top vacation destinations. The picturesque town of Baclayon (located just 10 minutes away from Bohol’s capital, Tagbilaran City) is home to many attractions, such as Baclayon Church, which was built by the Jesuits in 1727.
        </p>

        <p class="mt-3 text-base sm:text-lg leading-relaxed text-[#25282a]/90">
         The Baclayon Museum located within the church grounds houses a relic of St. Ignatius de Loyola, antique gold thread-embroidered ecclesiastical vestments, an image of the Blessed Virgin Mary said to be presented by Queen Catherine of Aragon and many other related artifacts. Nearby is a monument of bronze statues of Spanish conquistador Miguel Lopez de Legaspi and Datu (King) Sikatuna sculpted by no less than National Artist Napoleon Abueva. It depicts the famous “blood compact” and is at the actual site.


        </p>

        <div class="mt-4 pt-4 border-t border-gray-200">
          <p class="text-xs sm:text-sm font-semibold tracking-[.20em] uppercase text-[#18206b]">
            ADDRESS
          </p>
          <p class="mt-2 text-sm sm:text-base leading-relaxed">
           Barangay Taguihon, Baclayon, Bohol Philippines 6301
          </p>
        </div>
      </div>

      {{-- RIGHT: GOOGLE MAP EMBED --}}
      <div class="lg:col-span-7">
        <div
          class="w-full h-[320px] sm:h-[380px] md:h-[420px] lg:h-[520px]
                 rounded-2xl overflow-hidden shadow-lg border border-[#e5e7eb]"
          aria-label="Map showing the location of Astoria Plaza in Ortigas, Pasig City"
        >
          <iframe
            src="https://www.google.com/maps?q=Astoria+Bohol&output=embed"
            style="border:0;"
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
            class="w-full h-full"
            allowfullscreen
          ></iframe>
        </div>
      </div>

    </div>
  </div>
</section>



{{-- ==== AHR: How To Get There — Astoria Bohol (50px top padding, no-flash modal & UI) ==== --}}
<style>[x-cloak]{display:none!important}</style>

<section id="apw-how-to-get-there"
         class="relative w-full overflow-visible bg-white text-[#25282a] pt-[50px]"
         aria-labelledby="apw-howto-title"
         x-data="{
           open:false,
           videoId:'e6OPkcwua_w', // Astoria Bohol video
           autoplay:false,
           get src(){
             // Fix YouTube Error 153: use youtube-nocookie and drop enablejsapi
             if(!this.doLoad) return '';
             const base = `https://www.youtube-nocookie.com/embed/${this.videoId}?rel=0&modestbranding=1&playsinline=1`;
             return this.autoplay ? `${base}&autoplay=1` : `${base}`;
           },
           doLoad:false,
           toggleModal(state){
             this.open = state;
             if(state){
               // first open: allow iframe to load
               this.doLoad = true;
               this.autoplay = false;
             }
             this.$nextTick(() => {
               if(!state && this.$refs.player){
                 // fully reset src so YouTube unloads when modal closes
                 this.$refs.player.setAttribute('src','');
               }
             });
           },
           play(){
             // trigger autoplay parameter
             this.autoplay = true;
           }
         }"
         x-init="$nextTick(() => { doLoad = false })">

  {{-- Decorative layer (top-right island & bottom full-width ship). Padding moved to section --}}
  <div aria-hidden="true" class="absolute inset-0 z-0 pointer-events-none select-none">
    {{-- Top-right island --}}
    <img src="{{ asset('images/plan-island-bg.png') }}"
         alt=""
         class="absolute top-0 right-0 translate-x-[8%] -translate-y-[4%]
                w-[90vw] sm:w-[70vw] md:w-[56vw] lg:w-[32vw] xl:w-[30vw]
                lg:-translate-y-[10%] lg:translate-x-[12%]
                max-w-none opacity-100"
         loading="lazy" decoding="async" />
    {{-- Bottom full-width ship --}}
    <img src="{{ asset('images/about-ship_img.png') }}"
         alt=""
         class="absolute left-1/2 -translate-x-1/2 bottom-0
                w-[120vw] sm:w-[110vw] md:w-[105vw] lg:w-[100vw] xl:w-[100vw]
                h-auto max-w-none opacity-95"
         loading="lazy" decoding="async" />
  </div>

  <div class="relative z-10 mx-auto max-w-[1600px] px-4 sm:px-6 lg:px-8 pb-28 md:pb-36 lg:pb-40">
    <div class="grid items-start lg:items-stretch gap-8 lg:gap-12 lg:grid-cols-12">
      {{-- LEFT (4) — square image --}}
      <div class="lg:col-span-4 mt-20 sm:mt-15 lg:mt-0">
        <figure class="mx-auto w-full max-w-[520px]">
          <img src="{{ asset('images/About/How-to-get.webp') }}"
               alt="Astoria Bohol — tropical square image"
               class="w-full h-[680px] aspect-square object-cover rounded-xl shadow-lg"
               loading="lazy" decoding="async" />
        </figure>
      </div>

      {{-- RIGHT (8) — text + options + CTA --}}
      <div class="lg:col-span-8">
        <p class="pt-[30px] text-sm tracking-[.18em] uppercase text-[#25282a]/90">
          VISIT ASTORIA BOHOL
        </p>
        <h2 id="apw-howto-title" class="mt-2 text-3xl sm:text-4xl font-semibold tracking-tight">
          <span class="bg-clip-text text-transparent bg-gradient-to-r from-[#3F2021] via-[#3F2021] to-[#3F2021]/85">
            HOW TO GET THERE
          </span>
        </h2>

        <div class="mt-6 space-y-6">
          {{-- OPTION 1 --}}
          <div>
            <p class="text-base sm:text-lg font-semibold">
              <span class="text-[#CF4520]">OPTION 1:</span> RESORT TRANSFERS
            </p>
            <p class="mt-2 text-base sm:text-lg text-[#25282a]/90">
              Astoria Bohol provides shuttle services for booked guests. Reservations must be made at least 72 hours prior to arrival.
            </p>

            <div class="mt-3 text-base sm:text-lg text-[#25282a]/90">
              <p class="font-semibold">Via Panglao International Airport</p>
              <ul class="mt-1 space-y-1 list-disc list-inside">
                <li>PHP 4,000 roundtrip / per van (for a maximum of 8 guests)</li>
                <li>PHP 2,000 per way / per van (for a maximum of 8 guests)</li>
              </ul>
            </div>
          </div>

          {{-- OPTION 2 --}}
          <div>
            <p class="text-base sm:text-lg font-semibold">
              <span class="text-[#CF4520]">OPTION 2:</span> PUBLIC TRANSPORTATION
            </p>
            <p class="mt-2 text-base sm:text-lg text-[#25282a]/90">
              Tricycles are available upon your arrival at the airport. You can ride in one directly to Astoria Bohol.
              A cheaper alternative is to catch a bus from the airport going to the city terminal. From there,
              you may ride a tricycle to Astoria Bohol.
            </p>

            <div class="mt-3 text-base sm:text-lg text-[#25282a]/90">
              <p class="font-semibold">Landmarks:</p>
              <p class="mt-1">
                You will see a Petron gas station right before the perimeter of the resort. Turn right and you will
                see Astoria Bohol straight ahead.
              </p>
            </div>
          </div>

          {{-- CTA --}}
          <div class="pt-6 border-t border-gray-200/60">
            <p class="text-[12px] sm:text-sm tracking-[.20em] uppercase text-[#CF4520]">
              FIND THE BEST CALMING OASIS
            </p>
            <h3 class="mt-2 text-3xl sm:text-4xl font-semibold tracking-tight">
              <span class="bg-clip-text text-transparent bg-gradient-to-r from-[#3F2021] via-[#3F2021] to-[#3F2021]/85">
                ASTORIA BOHOL
              </span>
            </h3>
            <p class="mt-2 text-sm sm:text-base text-[#25282a]/85">
              Watch the beauty of Astoria Bohol in the New Normal.
            </p>

            <div class="mt-4">
              <button type="button"
                      class="inline-flex items-center gap-2 rounded-full border border-[#3F2021] px-[18px] sm:px-5 py-2.5 text-sm font-semibold text-[#3F2021] transition hover:bg-[#3F2021] hover:text-white"
                      @click="toggleModal(true)"
                      aria-controls="apw-video-modal"
                      :aria-expanded="open.toString()">
                Watch video
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
              </button>
            </div>
          </div>
          {{-- /CTA --}}
        </div>
      </div>
    </div>
  </div>

  {{-- Video Modal (no initial flash, Play & Close buttons) --}}
  <div id="apw-video-modal"
       x-cloak
       x-show="open"
       x-transition.opacity
       style="display:none"
       class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4"
       @keydown.escape.window="toggleModal(false)"
       @click.self="toggleModal(false)">

    <div class="relative w-full max-w-[860px] bg-black rounded-2xl overflow-hidden shadow-2xl">
      <div class="relative pt-[56.25%] bg-black">
        <iframe x-ref="player"
                :src="src"
                title="Astoria Bohol — Video"
                class="absolute inset-0 w-full h-full border-0"
                allow="accelerometer; encrypted-media; gyroscope; picture-in-picture; autoplay"
                referrerpolicy="strict-origin-when-cross-origin"
                allowfullscreen>
        </iframe>

        {{-- Center Play button --}}
        <button x-show="open && !autoplay"
                @click.stop="play()"
                class="absolute inset-0 m-auto h-16 w-16 flex items-center justify-center rounded-full bg-black/50 hover:bg-black/65 transition"
                aria-label="Play video">
          <svg viewBox="0 0 24 24" class="h-8 w-8 text-white" fill="currentColor">
            <path d="M8 5v14l11-7-11-7z"/>
          </svg>
        </button>
      </div>

      <div class="flex items-center justify-between gap-2 px-4 py-3 bg-black/60">
        <button @click="play()"
                class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold text-white hover:bg-white/10"
                :disabled="autoplay"
                :class="{'opacity-50 cursor-default': autoplay}"
                aria-label="Play video">
          <svg viewBox="0 0 24 24" class="h-4 w-4" fill="currentColor">
            <path d="M8 5v14l11-7-11-7z"/>
          </svg>
          <span>Play</span>
        </button>

        <button @click="toggleModal(false)"
                class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold text-white hover:bg-white/10"
                aria-label="Close video">
          <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M6 18L18 6M6 6l12 12"/>
          </svg>
          <span>Close</span>
        </button>
      </div>
    </div>
  </div>
</section>


{{-- ===== OUR AWARDS — 2 cols mobile / 3 cols desktop + Zoom Modal ===== --}}
<section
  id="awards"
  class="relative w-full bg-white text-[#25282a] overflow-hidden"
  aria-labelledby="awards-title"
  x-data="awardsGallery(@js([
    ['src' => asset('images/Awards/1.webp'),                                'alt' => 'Award 1'],
    ['src' => asset('images/Awards/2.webp'),            'alt' => 'Award 2'],
    ['src' => asset('images/Awards/3.webp'),     'alt' => 'Award 3'],
    ['src' => asset('images/Awards/4.webp'),                   'alt' => 'Award 4'],
    ['src' => asset('images/Awards/5.webp'),          'alt' => 'Award 5'],
    ['src' => asset('images/Awards/6.webp'),          'alt' => 'Award 6'],
    ['src' => asset('images/Awards/7.webp'),          'alt' => 'Award 7'],
    ['src' => asset('images/Awards/8.webp'),                   'alt' => 'Award 8'],
    ['src' => asset('images/Awards/9.webp'),                   'alt' => 'Award 9'],
  ]))"
  @keydown.escape.window="open && close()"
>
  <div class="relative mx-auto max-w-[1600px] px-4 sm:px-6 lg:px-8 py-10 sm:py-12 md:py-14">
    {{-- Heading --}}
    <div class="max-w-3xl ev-reveal" data-ev-reveal style="--ev-delay:60ms;">
      <p class="text-[12px] sm:text-sm tracking-[.28em] uppercase text-[#18206b] font-semibold">OUR</p>
      <div class="flex items-end gap-4 mt-1">
        <h2 id="awards-title" class="text-3xl sm:text-4xl md:text-5xl font-semibold tracking-tight text-[#CF4520]">
          AWARDS
        </h2>
        <span class="h-1 w-20 rounded-full bg-[#3F2021] mb-2 hidden sm:block"></span>
      </div>
      <p class="mt-5 text-base sm:text-lg md:text-xl text-[#25282a]/90">
        The recognitions we received. We are in gratitude for your unwavering support and continuous trust.
      </p>
    </div>

    {{-- Grid gallery: 2 cols (mobile) → 3 cols (md+) --}}
    <div class="mt-10 grid grid-cols-2 md:grid-cols-3 gap-4 sm:gap-5 lg:gap-6">
      @foreach ([
    ['src' => asset('images/Awards/1.webp'),                                'alt' => 'Award 1'],
    ['src' => asset('images/Awards/2.webp'),            'alt' => 'Award 2'],
    ['src' => asset('images/Awards/3.webp'),     'alt' => 'Award 3'],
    ['src' => asset('images/Awards/4.webp'),                   'alt' => 'Award 4'],
    ['src' => asset('images/Awards/5.webp'),          'alt' => 'Award 5'],
    ['src' => asset('images/Awards/6.webp'),          'alt' => 'Award 6'],
    ['src' => asset('images/Awards/7.webp'),          'alt' => 'Award 7'],
    ['src' => asset('images/Awards/8.webp'),                   'alt' => 'Award 8'],
    ['src' => asset('images/Awards/9.webp'),                   'alt' => 'Award 9'],
      ] as $idx => $item)
        <figure
          class="ev-reveal"
          data-ev-reveal
          style="--ev-delay:{{ 100 + $idx * 70 }}ms;"
        >
          <button
            type="button"
            class="group w-full h-full"
            @click="show({{ $idx }})"
            aria-label="View {{ $item['alt'] }} full size"
          >
            <div class="awards-card">
              <img
                src="{{ $item['src'] }}"
                alt="{{ $item['alt'] }}"
                loading="lazy"
                decoding="async"
                class="awards-img group-hover:scale-105"
              />
            </div>
          </button>
        </figure>
      @endforeach
    </div>
  </div>

  {{-- Zoom Modal --}}
  <div
    x-cloak
    x-show="open"
    x-transition.opacity
    class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/70 p-3 sm:p-4"
    @click.self="close()"
  >
    <div class="relative w-full max-w-[900px] mx-auto">
      {{-- Close button (white bg) --}}
      <button
        type="button"
        @click="close()"
        class="absolute right-3 top-3 z-10 inline-flex items-center justify-center w-9 h-9 rounded-full bg-white text-gray-900 hover:bg-gray-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#25282a]/40"
        aria-label="Close zoomed award"
      >
        <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
      </button>

      <div class="overflow-hidden rounded-2xl bg-white shadow-2xl">
        {{-- Main image area --}}
        <div class="relative w-full" style="padding-top:min(80vh, 70%);">
          <img
            :src="current.src"
            :alt="current.alt || 'Award'"
            class="absolute inset-0 w-full h-full object-contain"
          />
        </div>

        {{-- Footer: caption + nav (white bg) --}}
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 px-3 sm:px-4 py-3 bg-white">
          <p
            class="flex-1 text-xs sm:text-sm text-[#25282a]/80 text-center sm:text-left truncate"
            x-text="current.alt || 'Award'"
          ></p>

          <div class="flex items-center gap-2">
            <button
              type="button"
              @click.stop="prev()"
              class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-gray-100 text-gray-900 hover:bg-gray-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#25282a]/30"
              aria-label="Previous award"
            >
              <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M15 19l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </button>

            <span class="text-[11px] font-semibold text-[#25282a]/80 min-w-[72px] text-center">
              <span x-text="index + 1"></span>
              /
              <span x-text="items.length"></span>
            </span>

            <button
              type="button"
              @click.stop="next()"
              class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-gray-100 text-gray-900 hover:bg-gray-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#25282a]/30"
              aria-label="Next award"
            >
              <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- Scoped styles for AWARDS --}}
<style>
  /* Reveal */
  .ev-reveal{
    opacity:0;
    transform:translateZ(0);
    transition:opacity .6s ease;
    transition-delay:var(--ev-delay,0ms);
  }
  .ev-reveal.is-in{opacity:1;}
  @media (prefers-reduced-motion: reduce){
    .ev-reveal{opacity:1!important;transition:none!important;}
  }

  /* Prevent background scroll when modal open */
  body.awards-no-scroll{
    overflow:hidden!important;
    touch-action:none!important;
  }

  /* Card: bigger height */
  #awards .awards-card{
    border-radius: 1rem;
    background: #fff;
    box-shadow: 0 3px 10px rgba(0,0,0,.06);
    outline: 1px solid rgba(0,0,0,.05);
    overflow: hidden;
    padding: .85rem;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 150px;
  }
  @media (min-width:640px){
    #awards .awards-card{
      padding: 1rem;
      min-height: 180px;
    }
  }
  @media (min-width:1024px){
    #awards .awards-card{
      padding: 1.25rem;
      min-height: 210px;
    }
  }

  /* Image */
  #awards .awards-img{
    display:block;
    max-width:100%;
    max-height:100%;
    height:auto;
    margin:0 auto;
    border-radius:.75rem;
    transition: transform .45s cubic-bezier(.22,1,.36,1), filter .3s ease;
    will-change: transform;
  }
  #awards .awards-card:hover .awards-img{
    transform:scale(1.04);
  }
</style>

{{-- IntersectionObserver (for reveal) + Alpine gallery helper --}}
<script>
  // Reveal on scroll
  (function () {
    const scope = document.getElementById('awards') || document;
    const els = scope.querySelectorAll('[data-ev-reveal]');
    if (!('IntersectionObserver' in window)) {
      els.forEach(el => el.classList.add('is-in'));
      return;
    }
    const io = new IntersectionObserver((entries, o) => {
      entries.forEach(e => {
        if (e.isIntersecting) {
          e.target.classList.add('is-in');
          o.unobserve(e.target);
        }
      });
    }, { threshold: 0.1, rootMargin: '0px 0px -10% 0px' });
    els.forEach(el => io.observe(el));
  })();

  // Alpine data: awards gallery modal
  document.addEventListener('alpine:init', () => {
    Alpine.data('awardsGallery', (items = []) => ({
      items,
      open: false,
      index: 0,

      get current () {
        return this.items[this.index] || {};
      },

      show(i) {
        if (!this.items.length) return;
        this.index = i;
        this.open = true;
        document.body.classList.add('awards-no-scroll');
      },
      close() {
        this.open = false;
        document.body.classList.remove('awards-no-scroll');
      },
      next() {
        if (!this.items.length) return;
        this.index = (this.index + 1) % this.items.length;
      },
      prev() {
        if (!this.items.length) return;
        this.index = (this.index - 1 + this.items.length) % this.items.length;
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
