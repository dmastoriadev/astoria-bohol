@extends('layouts.app')

@section('title', 'Astoria Bohol | Hotel in Baclayon, Bohol')

@section('content')
{{-- Swiper CSS (once) --}}
<link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />

<!-- Header Slider – remove mobile black flicker (CSS bg fallback), add 500ms fade-in for hero-pane, keep per-slide mobile alignment -->
<style>
  /* Alpine cloak */
  [x-cloak]{ display:none !important; }

  /* --- Render hygiene --- */
  .hero-track{
    will-change: transform;
    backface-visibility: hidden;
    transform: translateZ(0);
    transform-style: preserve-3d;
  }

  /* Each slide paints its own image as a CSS background (fallback) to avoid black frames while the <img> decodes */
  .hero-slide{
    position:relative;
    contain:layout paint;
    background-color:#000;                 /* ultimate fallback */
    background-repeat:no-repeat;
    background-size:cover;
    background-position: var(--bpo, center center);
  }

  .hero-image{
    position:absolute; inset:0; width:100%; height:100%;
    object-fit:cover; transform: translateZ(0);
    -webkit-transform: translateZ(0);
    will-change: opacity, transform;
  }

  /* Global anti-flicker overlay (kept light) */
  .hero-vignette-global{
    position:absolute; inset:0; z-index:5; pointer-events:none;
    background:
      radial-gradient(120% 70% at 50% 80%, rgba(0,0,0,.14) 0%, rgba(0,0,0,.08) 40%, rgba(0,0,0,.03) 70%, transparent 100%),
      linear-gradient(to bottom, rgba(0,0,0,.06), rgba(0,0,0,.10));
    will-change: opacity;
    transform: translateZ(0);
  }

  /* Hero pane — brighter, crisper edges (no blur) */
  .hero-pane{
    background: rgba(20,24,30,.35);
    border: 1px solid rgba(255,255,255,.22);
    box-shadow: 0 10px 30px rgba(0,0,0,.22);
  }

  /* HERO-PANE ENTRANCE: 500ms delayed fade/slide */
  .pane-start{ opacity:0; transform:translateY(8px) scale(.995); }
  .pane-in{
    opacity:1; transform:translateY(0) scale(1);
    transition: opacity .6s cubic-bezier(.2,.8,.2,1), transform .6s cubic-bezier(.2,.8,.2,1);
    transition-delay:.5s; /* 500ms */
  }

  /* Text clarity helpers */
  .tshadow-strong{ text-shadow: 0 2px 12px rgba(0,0,0,.45); }
  .tshadow-soft  { text-shadow: 0 1px 8px  rgba(0,0,0,.35); }

  /* Desktop arrows (rounded buttons) */
  .hero-arrow{
    width:44px; height:44px; display:grid; place-items:center; border-radius:9999px;
    background:rgba(255,255,255,.92);
    box-shadow:0 6px 18px rgba(0,0,0,.18);
    transition:transform .25s ease, background .25s ease;
    -webkit-tap-highlight-color: transparent;
    touch-action: manipulation;
  }
  .hero-arrow:hover{ transform:scale(1.06); background:#fff; }

  /* Mobile arrows: tiny text-only “‹ ›”, no white bg on tap/click */
  .mob-ico{ display:none; }
  .desk-ico{ display:inline-flex; }
  @media (max-width: 640px){
    .hero-arrow{
      width:22px; height:22px; padding:0;
      background:transparent !important; box-shadow:none !important; color:#fff;
      line-height:1; font-size:20px; font-weight:700; border-radius:0;
      outline:none; -webkit-tap-highlight-color: transparent;
    }
    .hero-arrow:hover,
    .hero-arrow:active,
    .hero-arrow:focus,
    .hero-arrow:focus-visible{
      background:transparent !important; box-shadow:none !important; outline:none !important;
    }
    .desk-ico{ display:none; }
    .mob-ico{ display:inline; }
  }

  /* Dots → pill when active (no mobile tap flash) */
  .hero-dot{
    height:10px; width:10px; border-radius:9999px; border:1px solid rgba(255,255,255,.85);
    background:rgba(255,255,255,.72); transition:all .25s ease;
    -webkit-tap-highlight-color: transparent;
  }
  .hero-dot[aria-current="true"]{ width:26px; background:#fff; }

  /* Progress bar shell (JS controls width for precise pause) */
  .hero-progress-track{ height:3px; background:rgba(255,255,255,.22); border-radius:9999px; overflow:hidden; }
  .hero-progress-fill{ height:100%; background:#fff; width:0%; }

  /* Track height */
  .hero-h { height: 86vh; min-height: 520px; }
  @supports (height: 100dvh){ .hero-h { height: 95dvh; } }
  @media (max-width: 1024px){
    .hero-h { height: 82vh; min-height: 500px; }
    @supports (height: 100lvh){ .hero-h { height: 82lvh; } }
  }
  @media (max-width: 640px){
    /* Force 250px height on mobile */
    .hero-h { height: 250px !important; min-height: 550px !important; }

    /* Per-slide mobile crop (via CSS var) */
    .hero-image{ object-position: var(--mo, center center); }
  }

  /* Helpers */
  .tap-transparent{ -webkit-tap-highlight-color: transparent; -webkit-touch-callout: none; }
  .btn-label{ display:inline-block; min-width:3.25rem; }
</style>

<!-- Optionally warm up slide #2 right away -->
<link rel="preload" as="image" href="{{ asset('images/Home/heading-1.webp') }}"/>

<div
  x-data="{
    /* Slides (add one-liner 'mo' to control MOBILE alignment: 'left' | 'center' | 'right') */
    slides: [
      { image: '{{ asset('images/Home/heading-1.webp') }}',  title: 'Astoria Bohol', desc: 'Beachfront bliss and everything in between.', ctaText: 'Explore Properties', ctaLink: '{{ route('about') }}', mo:'center'   },
      { image: '{{ asset('images/Home/heading-2.webp') }}', titleImg: '{{ asset('images/Home/1address.webp') }}', desc: 'Prime location, city retreat.', ctaText: 'View Amenities', ctaLink: '{{ route('about') }}', mo:'center'  },
      { image: '{{ asset('images/Home/heading-3.webp') }}',  titleImg: '{{ asset('images/Home/2ambience.webp') }}', desc: 'Spaces that set the mood.', ctaText: 'Discover More', ctaLink: '{{ route('about') }}', mo:'center' },
      { image: '{{ asset('images/Home/heading-4.webp') }}',  titleImg: '{{ asset('images/Home/3amenities.webp') }}', desc: 'Thoughtful comforts, end to end.', ctaText: 'See Promos', ctaLink: '{{ route('promos') }}',              mo:'center' },
      { image: '{{ asset('images/Home/heading-5.webp') }}',  titleImg: '{{ asset('images/Home/4appetite.webp') }}', desc: 'Flavors that satisfy and surprise.', ctaText: 'See Promos', ctaLink: '{{ route('promos') }}',           mo:'center' },
      { image: '{{ asset('images/Home/heading-6.webp') }}',  titleImg: '{{ asset('images/Home/5attention.webp') }}', desc: 'Service that anticipates your needs.', ctaText: 'See Promos', ctaLink: '{{ route('promos') }}',        mo:'center' },
      { image: '{{ asset('images/Home/heading-7.webp') }}',  titleImg: '{{ asset('images/Home/6appeal.webp') }}',   desc: 'Aesthetic, Alluring, Astoria.', ctaText: 'See Promos', ctaLink: '{{ route('promos') }}',              mo:'center' },
      { image: '{{ asset('images/Home/heading-8.webp') }}',  titleImg: '{{ asset('images/Home/alwaysastoria.webp') }}', desc: '#AlwaysAstoria moments await at every destination.', ctaText: 'See Promos', ctaLink: '{{ route('promos') }}', mo:'center' }
    ],

    /* Track which images are loaded to prevent black flashes */
    loaded: [],

    /* State */
    current: 0, transitionOn: true, dragging: false, dragStartX: 0, dragOffset: 0,

    /* Autoplay + precise, pausable progress */
    delay: 5000,
    paused: false,
    startTs: 0,
    remaining: 5000,
    progress: 0,  // 0..100
    tHandle: null,
    raf: null,

    /* --------- Helpers ---------- */
    markLoaded(i){ this.$nextTick(() => { this.loaded[i] = true; }); },
    preloadIndex(i, thenGo = null){
      i = (i + this.slides.length) % this.slides.length;
      if (this.loaded[i]){ if(thenGo) thenGo(); return; }
      const img = new Image();
      img.src = this.slides[i].image;
      if (img.complete){
        this.loaded[i] = true; if(thenGo) thenGo();
      } else {
        img.onload  = () => { this.loaded[i] = true; if(thenGo) thenGo(); };
        img.onerror = () => { this.loaded[i] = true; if(thenGo) thenGo(); }; // fail open (show CSS bg fallback)
      }
    },
    warmNeighbors(){
      this.preloadIndex(this.current + 1);
      this.preloadIndex(this.current + 2);
    },
    bpo(s){
      const m = window.matchMedia('(max-width: 640px)').matches;
      const pos = s.mo==='left' ? 'left center' : (s.mo==='right' ? 'right center' : 'center center');
      return m ? pos : 'center center';
    },

    /* --------- Cycle control ---------- */
    cancelCycle(){
  if (this.tHandle !== null) {
    clearTimeout(this.tHandle);
    this.tHandle = null;
  }
  if (this.raf) {
    cancelAnimationFrame(this.raf);
    this.raf = null;
  }
},

    startCycle(){
      this.cancelCycle();
      this.startTs = performance.now();

      const total = this.remaining;

      const tick = (now) => {
        const elapsed = now - this.startTs;
        const pct = Math.max(0, Math.min(1, elapsed / total));
        this.progress = pct * 100;

        // If autoplay was cancelled (pause/manual nav), stop the loop
        if (this.tHandle === null) {
          this.raf = null;
          return;
        }

        this.raf = requestAnimationFrame(tick);
      };

      this.raf = requestAnimationFrame(tick);

      this.tHandle = setTimeout(() => {
        // Mark finished & advance exactly once
        this.tHandle = null;
        this.progress = 100;
        this.next('auto');
      }, this.remaining);
    },

    pauseCycle(){
      if(this.paused) return;
      this.paused = true;
      if(this.startTs){
        const elapsed = performance.now() - this.startTs;
        this.remaining = Math.max(0, this.remaining - elapsed);
      }
      this.cancelCycle();
    },
    resumeCycle(){
      if(!this.paused) return;
      this.paused = false;
      if (this.remaining <= 16) { this.next('auto'); }
      else { this.startCycle(); }
    },
    resetForNewSlide(){
      this.cancelCycle();
      this.progress = 0;
      this.remaining = this.delay;
      if(!this.paused){ this.startCycle(); }
      this.warmNeighbors();
    },

    /* --------- Nav with preload guard ---------- */
    _goWhenReady(target){
      if (this.loaded[target]){
        this.transitionOn = true;
        this.current = target;
        this.resetForNewSlide();
      } else {
        this.pauseCycle();
        this.preloadIndex(target, () => {
          this.transitionOn = true;
          this.current = target;
          this.resumeCycle();
          this.resetForNewSlide();
        });
      }
    },
    next(){ this._goWhenReady((this.current + 1) % this.slides.length); },
    prev(){ this._goWhenReady((this.current - 1 + this.slides.length) % this.slides.length); },
    goTo(i){ this._goWhenReady((i + this.slides.length) % this.slides.length); },

    /* Drag / Swipe */
    isInNoDrag(e){ return e.target.closest('[data-nodrag]'); },
    onDragStart(e){
      if(this.isInNoDrag(e)) return;
      this.dragging = true; this.transitionOn = false;
      this.pauseCycle();
      this.dragStartX = e.touches ? e.touches[0].clientX : e.clientX;
      this.dragOffset  = 0;
    },
    onDragMove(e){
      if(!this.dragging) return;
      const x = e.touches ? e.touches[0].clientX : e.clientX;
      this.dragOffset = x - this.dragStartX;
    },
    onDragEnd(){
      if (!this.dragging) return;
      this.transitionOn = true;
      if (this.dragOffset > 60) this.prev(); else if (this.dragOffset < -60) this.next(); else this.resumeCycle();
      this.dragging = false; this.dragOffset = 0;
    },

    /* Pause/Play button */
    togglePause(){ this.paused ? this.resumeCycle() : this.pauseCycle(); },

    /* Init + visibility handling (autoplay by default) */
    init(){
      this.loaded = Array(this.slides.length).fill(false);
      this.loaded[0] = true;
      this.preloadIndex(1, () => { this.loaded[1] = true; });
      this.warmNeighbors();
      this.resetForNewSlide();

      document.addEventListener('visibilitychange', () => {
        if (document.hidden){ this.pauseCycle(); } else { this.resumeCycle(); }
      });
    }
  }"
  x-init="init()"
  @touchstart.passive="onDragStart($event)"
  @touchmove.passive="onDragMove($event)"
  @touchend.passive="onDragEnd()"
  @mousedown="onDragStart($event)"
  @mousemove="onDragMove($event)"
  @mouseup="onDragEnd()"
  @mouseleave="dragging = false; dragOffset = 0;"
  @keydown.left.prevent="prev()"
  @keydown.right.prevent="next()"
  tabindex="0"
  class="relative hero-h w-full overflow-hidden select-none mt-12"
  style="user-select:none;"
  role="region" aria-label="Homepage hero"
>
  <!-- Slides track -->
  <div
    class="flex h-full hero-track"
    :class="transitionOn && !dragging ? 'transition-transform duration-[820ms] ease-in-out' : ''"
    :style="`transform: translate3d(calc(${-current * 100}% + ${dragging ? dragOffset : 0}px), 0, 0);`"
  >
    <template x-for="(s, i) in slides" :key="i">
      <!-- Bind CSS background as a safety net + per-slide mobile background-position via --bpo -->
      <div class="min-w-full h-full hero-slide"
           :style="{ backgroundImage: `url(${s.image})`, '--bpo': bpo(s) }">
        <img class="hero-image"
             :src="s.image"
             :alt="s.title ?? ('Slide ' + (i+1))"
             :loading="i < 2 ? 'eager' : 'lazy'"
             :fetchpriority="i < 2 ? 'high' : 'auto'"
             decoding="async"
             draggable="false"
             @load="markLoaded(i)"
             :style="{'--mo': (s.mo==='left' ? 'left center' : (s.mo==='right' ? 'right center' : 'center center'))}" />

        <!-- Caption: aligned to site width (max-w-[1575px]) -->
        <div class="relative z-10 w-full h-full">
          <div class="mx-auto max-w-[1575px] h-full px-4 sm:px-6 lg:px-8">
            <div class="h-full flex items-center">
              <!-- 500ms delayed entrance on active slide -->
              <div class="hero-pane rounded-3xl p-5 sm:p-7 md:p-8 lg:p-10 max-w-xl md:max-w-4xl text-white pane-start"
                   :class="current === i ? 'pane-in' : ''"
                   data-nodrag>
                <div class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-[15px] font-medium bg-white/14 ring-1 ring-white/25 tshadow-soft">
                  <span>#AlwaysAstoria</span>
                </div>

                <div class="mt-3">
                  <h2 x-show="!s.titleImg" class="tshadow-strong text-white/95 text-2xl leading-tight sm:text-4xl md:text-5xl lg:text-6xl font-semibold">
                    <span x-text="s.title"></span>
                  </h2>
                  <img x-show="s.titleImg" :src="s.titleImg" alt="" class="w-[240px] sm:w-[320px] md:w-[420px] h-auto" draggable="false" />
                </div>

                <p class="mt-4 text-base font-semibold sm:text-lg md:text-xl/relaxed text-white tshadow-soft" x-text="s.desc"></p>

                <div class="mt-6 flex flex-wrap gap-3">
                  <a :href="s.ctaLink"
                     class="inline-flex items-center justify-center rounded-full bg-white text-gray-900 px-6 py-3 text-sm font-semibold hover:bg-white focus:outline-none focus-visible:ring-2 focus-visible:ring-white/50">
                    Explore
                  </a>
                  <a href="{{ url('/promos') }}"
                     class="inline-flex items-center justify-center rounded-full border border-white/80 text-white px-6 py-3 text-sm font-semibold hover:bg-white/10">
                    See Promos
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div> <!-- /slide -->
    </template>
  </div>

  <!-- Global overlay to prevent mobile/tablet overlay flicker -->
  <div class="hero-vignette-global" aria-hidden="true"></div>

  <!-- Arrows -->
  <button type="button" @click="prev()" aria-label="Previous"
          class="hero-arrow absolute left-3 sm:left-4 top-1/2 -translate-y-1/2 z-20" data-nodrag>
    <span class="desk-ico">
      <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
      </svg>
    </span>
    <span class="mob-ico" aria-hidden="true">‹</span>
  </button>

  <button type="button" @click="next()" aria-label="Next"
          class="hero-arrow absolute right-3 sm:right-4 top-1/2 -translate-y-1/2 z-20" data-nodrag>
    <span class="desk-ico">
      <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
      </svg>
    </span>
    <span class="mob-ico" aria-hidden="true">›</span>
  </button>

  <!-- Bottom controls -->
  <div class="absolute bottom-4 sm:bottom-5 left-0 right-0 z-20">
    <div class="mx-auto max-w-[1575px] px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between gap-3">
        <!-- Play/Pause (text always visible) -->
        <button type="button" @click="togglePause()"
                class="inline-flex items-center gap-2 rounded-full bg-white/15 text-white px-3 py-2 text-xs font-semibold ring-1 ring-white/25 tap-transparent select-none"
                data-nodrag :aria-pressed="paused ? 'true' : 'false'">
          <span class="inline-flex items-center gap-2" x-show="!paused" x-cloak aria-live="polite">
            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>
            <span class="btn-label">Pause</span>
          </span>
          <span class="inline-flex items-center gap-2" x-show="paused" x-cloak aria-live="polite">
            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
            <span class="btn-label">Play</span>
          </span>
        </button>

        <!-- Dots -->
        <div class="flex items-center gap-2">
          <template x-for="(_, i) in slides" :key="i">
            <button type="button" @click="goTo(i)" class="hero-dot"
                    :aria-current="current === i ? 'true' : 'false'"
                    aria-label="Go to slide" data-nodrag></button>
          </template>
        </div>
      </div>

      <!-- Progress (JS-driven; pauses/resumes precisely) -->
      <div class="mt-3 hero-progress-track rounded-full">
        <div class="hero-progress-fill" :style="`width:${progress.toFixed(3)}%;`"></div>
      </div>
    </div>
  </div>
</div>


<!-- ==== AB1 Section (with fade-in animations): ==== -->
<section class="relative bg-white text-[#25282a] overflow-hidden pt-[90px] pb-[90px] lg:pb-[150px] lg:pt-[300px] xl:pt-[100px] xl:pb-[0px]" aria-labelledby="ac3-title">
  <!-- Decorative corner vectors (fade-in on view) -->
  <img
    src="/images/Home/abh-vector-top.webp"
    alt=""
    aria-hidden="true"
    class="pointer-events-none select-none absolute top-4 right-5 w-16 sm:w-20 md:w-32 lg:w-72 xl:w-56 opacity-0 translate-y-2 transition duration-700 ease-out will-change-transform apw-fade"
    style="transition-delay:150ms"
  />
  <img
    src="/images/Home/abh-vector-btm.webp"
    alt=""
    aria-hidden="true"
    class="pointer-events-none select-none absolute bottom-5 left-5 w-24 sm:w-56 md:w-52 lg:w-[18rem] xl:w-[20rem] opacity-0 translate-y-2 transition duration-700 ease-out will-change-transform apw-fade"
    style="transition-delay:750ms"
  />

  <div class="relative max-w-[1100px] mx-auto px-4 p-[60px] md:pt-[200px] sm:pb:20 md:pb-[200px] lg:pb-[300px]  lg:pt-[50px]">
    <div
      class="apw-card bg-[#ededed] border border-black/5 rounded-2xl p-5 md:p-7 lg:p-10 shadow-[0_18px_38px_rgba(0,0,0,0.08)] opacity-0 translate-y-2 transition duration-700 ease-out will-change-transform apw-fade"
      style="transition-delay:580ms"
    >
      <!-- Header -->
      <div class="grid gap-[14px] items-center justify-items-center text-center lg:grid-cols-[auto_1fr] lg:justify-items-start lg:text-left lg:gap-[18px]">
        <span class="inline-block whitespace-nowrap px-3 py-2 rounded-full bg-white border border-[#CF4520] text-[#CF4520] font-bold text-[11px] tracking-[0.14em] uppercase">
         ESCAPE TO AN IDYLLIC HAVEN


        </span>

        <div>
          <h2 id="ac3-title" class="m-0 text-[#3F2021] font-semibold leading-[1.08] tracking-[0.015em] text-[26px] sm:text-[32px] md:text-[38px] lg:text-[40px]">
        HOTEL IN BOHOL


          </h2>
          <div class="mt-2.5 h-1 rounded-full bg-[#CF4520]  w-[clamp(140px,22vw,120px)] mx-auto lg:mx-0" aria-hidden="true"></div>
        </div>
      </div>

      <!-- Body: single column paragraphs -->
      <div class="mt-4 lg:mt-6 space-y-4">
        <p class="m-0 text-[#757575] font-medium leading-[1.65] text-[14px] md:text-[15px] lg:text-[20px]">
         With its quaintness and charm, Astoria Bohol provides a retreat to those who seek privacy and exclusivity. The safe enclave in the heart of Baclayon pays homage to the province’s local heritage.

        </p>

        <p class="m-0 text-[#757575] font-medium leading-[1.65] text-[14px] md:text-[15px] lg:text-[20px]">
          Designed for the ultimate sense of cocooned resort living, the Filipino heritage mansion façade, the neutral-colored walls and interiors all reflect the rich culture, traditions, and history of Bohol.


        </p>
      </div>
    </div>
  </div>

  <!-- Simple IntersectionObserver to trigger fade-ins -->
  <script>
    (function () {
      const scope = document.currentScript.closest('section') || document;
      const els = scope.querySelectorAll('.apw-fade');
      if (!('IntersectionObserver' in window)) {
        setTimeout(() => {
          els.forEach(el => {
            el.classList.remove('opacity-0','translate-y-2');
            el.classList.add('opacity-100','translate-y-0');
          });
        }, 200);
        return;
      }
      const io = new IntersectionObserver((entries, obs) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            const el = entry.target;
            el.classList.remove('opacity-0','translate-y-2');
            el.classList.add('opacity-100','translate-y-0');
            obs.unobserve(el);
          }
        });
      }, { rootMargin: '0px 0px -10% 0px', threshold: 0.15 });
      els.forEach(el => io.observe(el));
    })();
  </script>
</section>



<!-- ===== Bohol Highlights: Full-Bleed 4-Card Grid w/ Hover Grow & Fade-In ===== -->

<section id="rooms"
         class="relative w-screen left-1/2 right-1/2 -mx-[50vw] bg-white overflow-hidden">
  <div class="space-y-0">
    <!-- GRID: 1 COL (MOBILE), 2 COL (MD+) -->
    <div id="rooms-grid" class="grid grid-cols-1 md:grid-cols-2 gap-0">

      <!-- Card 1: Deluxe Room -->
      <a
         href="{{ '/accommodations/deluxe-room' }}"
         class="group relative block
                h-[55vh] min-h-[320px] max-h-[560px]
                md:h-[60vh] md:min-h-[380px] md:max-h-[640px]
                lg:h-[75vh] lg:min-h-[460px] lg:max-h-[780px]
                overflow-hidden
                transform-gpu transition-all duration-500 ease-out
                room-fade opacity-0 translate-y-3 hover:z-[2] focus-visible:z-[2]
                focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/70"
         style="transition-delay:180ms;">
        <div class="absolute inset-0 transform-gpu will-change-transform transition-transform duration-500 ease-out group-hover:scale-[1.02]">
          <div class="absolute inset-0 bg-center bg-cover transition-transform duration-700 will-change-transform group-hover:scale-105"
               style="
                 background-image:url('{{ asset('images/Rooms/deluxe1.webp') }}');
                 backface-visibility:hidden;
                 transform:translateZ(0);
               "></div>
          <div class="absolute inset-0 bg-gradient-to-t from-black/65 via-black/20 to-transparent"></div>
          <div class="absolute inset-x-0 bottom-0 p-6 md:p-7 lg:p-8 text-white">
            <h3 class="text-2xl md:text-3xl font-semibold tracking-tight">
              Deluxe Room
            </h3>
            <p class="mt-2 text-sm md:text-base font-medium text-white/90">
              Cozy and tranquil rooms with a beautifully carved four-poster bed appointed in true heritage design.
            </p>
            <span
              class="mt-4 inline-flex items-center justify-center rounded-full px-4 py-2
                     font-semibold text-sm tracking-wide
                     border border-[#3F2021] bg-[#CF4520] text-white
                     sm:border-white/90 sm:bg-transparent sm:text-white
                     transition-colors duration-300
                     group-hover:bg-[#CF4520] group-hover:sm:bg-white/10">
              View Details
            </span>
          </div>
        </div>
      </a>

      <!-- Card 2: Luxury Room -->
      <a
         href="{{ '/accommodations/luxury-room' }}"
         class="group relative block
                h-[55vh] min-h-[320px] max-h-[560px]
                md:h-[60vh] md:min-h-[380px] md:max-h-[640px]
                lg:h-[75vh] lg:min-h-[460px] lg:max-h-[780px]
                overflow-hidden
                transform-gpu transition-all duration-500 ease-out
                room-fade opacity-0 translate-y-3 hover:z-[2] focus-visible:z-[2]
                focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/70"
         style="transition-delay:260ms;">
        <div class="absolute inset-0 transform-gpu will-change-transform transition-transform duration-500 ease-out group-hover:scale-[1.02]">
          <div class="absolute inset-0 bg-center bg-cover transition-transform duration-700 will-change-transform group-hover:scale-105"
               style="
                 background-image:url('{{ asset('images/Rooms/luxury1.webp') }}');
                 backface-visibility:hidden;
                 transform:translateZ(0);
               "></div>
          <div class="absolute inset-0 bg-gradient-to-t from-black/65 via-black/20 to-transparent"></div>
          <div class="absolute inset-x-0 bottom-0 p-6 md:p-7 lg:p-8 text-white">
            <h3 class="text-2xl md:text-3xl font-semibold tracking-tight">
              Luxury Room
            </h3>
            <p class="mt-2 text-sm md:text-base font-medium text-white/90">
              Elegant and opulent with two gorgeously designed four-poster beds made to reflect true Filipino heritage.
            </p>
            <span
              class="mt-4 inline-flex items-center justify-center rounded-full px-4 py-2
                     font-semibold text-sm tracking-wide
                     border border-[#3F2021] bg-[#CF4520] text-white
                     sm:border-white/90 sm:bg-transparent sm:text-white
                     transition-colors duration-300
                     group-hover:bg-[#CF4520] group-hover:sm:bg-white/10">
              View Details
            </span>
          </div>
        </div>
      </a>

      <!-- Card 3: Dining -->
      <a
         href="{{ url('/dining') }}"
         class="group relative block
                h-[55vh] min-h-[320px] max-h-[560px]
                md:h-[60vh] md:min-h-[380px] md:max-h-[640px]
                lg:h-[75vh] lg:min-h-[460px] lg:max-h-[780px]
                overflow-hidden
                transform-gpu transition-all duration-500 ease-out
                room-fade opacity-0 translate-y-3 hover:z-[2] focus-visible:z-[2]
                focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/70"
         style="transition-delay:340ms;">
        <div class="absolute inset-0 transform-gpu will-change-transform transition-transform duration-500 ease-out group-hover:scale-[1.02]">
          <div class="absolute inset-0 bg-center bg-cover transition-transform duration-700 will-change-transform group-hover:scale-105"
               style="
                 background-image:url('{{ asset('images/dining.webp') }}');
                 backface-visibility:hidden;
                 transform:translateZ(0);
               "></div>
          <div class="absolute inset-0 bg-gradient-to-t from-black/65 via-black/20 to-transparent"></div>
          <div class="absolute inset-x-0 bottom-0 p-6 md:p-7 lg:p-8 text-white">
            <h3 class="text-2xl md:text-3xl font-semibold tracking-tight">
              Dining
            </h3>
            <p class="mt-2 text-sm md:text-base font-medium text-white/90">
              While dining in seclusion on a private beachfront, inherit the gift of a life so delicious.
            </p>
            <span
              class="mt-4 inline-flex items-center justify-center rounded-full px-4 py-2
                     font-semibold text-sm tracking-wide
                     border border-[#3F2021] bg-[#CF4520] text-white
                     sm:border-white/90 sm:bg-transparent sm:text-white
                     transition-colors duration-300
                     group-hover:bg-[#CF4520] group-hover:sm:bg-white/10">
              View Details
            </span>
          </div>
        </div>
      </a>

      <!-- Card 4: Astoria Bohol Lantawan -->
      <a
         href="{{ url('/astoria-bohol-lantawan') }}"
         class="group relative block
                h-[55vh] min-h-[320px] max-h-[560px]
                md:h-[60vh] md:min-h-[380px] md:max-h-[640px]
                lg:h-[75vh] lg:min-h-[460px] lg:max-h-[780px]
                overflow-hidden
                transform-gpu transition-all duration-500 ease-out
                room-fade opacity-0 translate-y-3 hover:z-[2] focus-visible:z-[2]
                focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/70"
         style="transition-delay:420ms;">
        <div class="absolute inset-0 transform-gpu will-change-transform transition-transform duration-500 ease-out group-hover:scale-[1.02]">
          <div class="absolute inset-0 bg-center bg-cover transition-transform duration-700 will-change-transform group-hover:scale-105"
               style="
                 background-image:url('{{ asset('images/Lantawan/lantawan.webp') }}');
                 backface-visibility:hidden;
                 transform:translateZ(0);
               "></div>
          <div class="absolute inset-0 bg-gradient-to-t from-black/65 via-black/20 to-transparent"></div>
          <div class="absolute inset-x-0 bottom-0 p-6 md:p-7 lg:p-8 text-white">
            <h3 class="text-2xl md:text-3xl font-semibold tracking-tight">
              Astoria Bohol Lantawan
            </h3>
            <p class="mt-2 text-sm md:text-base font-medium text-white/90">
              Astoria’s second property in Baclayon with two villas, offering memorable and relaxing retreats.
            </p>
            <span
              class="mt-4 inline-flex items-center justify-center rounded-full px-4 py-2
                     font-semibold text-sm tracking-wide
                     border border-[#3F2021] bg-[#CF4520] text-white
                     sm:border-white/90 sm:bg-transparent sm:text-white
                     transition-colors duration-300
                     group-hover:bg-[#CF4520] group-hover:sm:bg-white/10">
              View Details
            </span>
          </div>
        </div>
      </a>

    </div>
  </div>

  <!-- Progressive fade-in (IntersectionObserver) -->
  <script>
    (function () {
      const section = document.currentScript.closest('section');
      if (!section) return;

      const cards = section.querySelectorAll('.room-fade');
      if (!cards.length) return;

      if ('IntersectionObserver' in window) {
        const io = new IntersectionObserver((entries, obs) => {
          entries.forEach(entry => {
            if (entry.isIntersecting) {
              const el = entry.target;
              el.classList.remove('opacity-0','translate-y-3');
              el.classList.add('opacity-100','translate-y-0');
              obs.unobserve(el);
            }
          });
        }, { rootMargin: '0px 0px -8% 0px', threshold: 0.15 });
        cards.forEach(c => io.observe(c));
      } else {
        setTimeout(() => {
          cards.forEach(el => {
            el.classList.remove('opacity-0','translate-y-3');
            el.classList.add('opacity-100','translate-y-0');
          });
        }, 150);
      }
    })();
  </script>
</section>


<!-- ===== Full-Screen Dining Hero (TailwindCSS) ===== -->
<section id="dining-hero"
         class="group relative w-full min-h-[400px] md:min-h-screen overflow-hidden text-white"
         aria-labelledby="dining-hero-title">

  <!-- Background image + overlay (subtle grow on hover) -->
  <div class="absolute inset-0 -z-10">
    <div class="absolute inset-0 bg-center bg-cover transition-transform duration-[900ms] ease-[cubic-bezier(.22,1,.36,1)] will-change-transform transform-gpu
                group-hover:scale-[1.04]"
         style="background-image:url('{{ asset('images/Amenities/amenities.webp') }}');"></div>
    <div class="absolute inset-0 bg-gradient-to-b from-black/40 via-black/25 to-black/40"></div>
    <div class="pointer-events-none absolute inset-x-0 bottom-0 h-40 bg-gradient-to-t from-black/40 to-transparent"></div>
  </div>

  <!-- Content -->
  <div class="relative mx-auto max-w-[1500px] px-4 sm:px-6 lg:px-8">
    <div class="min-h-[400px] md:min-h-screen flex items-center">
      <div class="w-full max-w-3xl">
        <!-- Header -->
        <h1 id="dining-hero-title"
            class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-semibold leading-[1.05] tracking-tight dining-reveal"
            data-dining-reveal style="--dining-delay: 120ms;">
         AMENITIES
        </h1>

        <!-- Accent underline -->
        <div class="mt-3 h-1.5 w-[clamp(160px,24vw,120px)] rounded-full bg-[#CF4520] dining-reveal"
             data-dining-reveal style="--dining-delay: 220ms;"></div>

        <!-- Text -->
        <p class="mt-5 md:mt-6 text-white/90 text-base sm:text-lg md:text-xl font-semibold max-w-prose dining-reveal"
           data-dining-reveal style="--dining-delay: 320ms;">
         Perfect places to create moments you will cherish with snapshots of serenity to remember
        </p>

        <!-- CTA -->
        <div class="mt-8 dining-reveal" data-dining-reveal style="--dining-delay: 420ms;">
          <a href="{{ url('/food-beverages') }}"
             class="inline-flex items-center gap-2 rounded-full border border-white/90 bg-transparent px-6 sm:px-7 py-3 sm:py-3.5
                    text-sm sm:text-base font-semibold tracking-wide text-white shadow-[0_8px_24px_rgba(0,0,0,0.15)]
                    transition duration-300 ease-out
                    hover:bg-white/10 hover:border-white
                    focus:outline-none focus-visible:ring-2 focus-visible:ring-white/70
                    transform-gpu will-change-transform hover:scale-[1.03]">
            Explore
            <svg class="h-4 w-4 sm:h-5 sm:w-5" fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Optional edge decorations -->
  <div class="pointer-events-none absolute left-0 top-0 w-40 sm:w-56 md:w-72 lg:w-80 h-40 sm:h-56 md:h-72 lg:h-80
              bg-gradient-to-br from-[#00a895]/15 to-transparent -z-10"></div>
  <div class="pointer-events-none absolute right-0 bottom-0 w-48 sm:w-64 md:w-80 lg:w-96 h-48 sm:h-64 md:h-80 lg:h-96
              bg-gradient-to-tl from-[#f05323]/10 to-transparent -z-10"></div>
</section>

<style>
  /* Entrance animation styles */
  .dining-reveal{
    opacity:0;
    transform: translateY(8px);
    transition:
      opacity 600ms ease,
      transform 600ms ease;
    transition-delay: var(--dining-delay, 0ms);
    will-change: opacity, transform;
  }
  .dining-reveal.is-in{
    opacity:1;
    transform: translateY(0);
  }
  @media (prefers-reduced-motion: reduce){
    .dining-reveal{ transition: none; transform: none; }
  }
</style>

<script>
  (function () {
    const els = document.querySelectorAll('[data-dining-reveal]');
    if (!('IntersectionObserver' in window)) { els.forEach(el => el.classList.add('is-in')); return; }
    const io = new IntersectionObserver((entries, obs) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-in');
          obs.unobserve(entry.target);
        }
      });
    }, { threshold: 0.15, rootMargin: '0px 0px -10% 0px' });
    els.forEach(el => io.observe(el));
  })();
</script>


<!-- ==== Travel Time & Tourist Spots (with fade-in animations): ==== -->
<section
  class="relative bg-white text-[#25282a] overflow-hidden  pt-[90px] pb-[200px] lg:pb-[150px] lg:pt-[300px] xl:pt-[100px] xl:pb-[150px]"
  aria-labelledby="travel-title"
>
  <!-- Decorative corner vectors (fade-in on view) -->
  <img
    src="/images/Home/abh-vector-top.webp"
    alt=""
    aria-hidden="true"
    class="pointer-events-none select-none absolute top-4 right-5 w-16 sm:w-20 md:w-32 lg:w-72 xl:w-56 opacity-0 translate-y-2 transition duration-700 ease-out will-change-transform apw-fade"
    style="transition-delay:150ms"
  />
  <img
    src="/images/Home/abh-vector-btm.webp"
    alt=""
    aria-hidden="true"
    class="pointer-events-none select-none absolute bottom-5 left-5 w-24 sm:w-56 md:w-52 lg:w-[18rem] xl:w-[20rem] opacity-0 translate-y-2 transition duration-700 ease-out will-change-transform apw-fade"
    style="transition-delay:750ms"
  />

  <div class="relative max-w-[1100px] mx-auto px-4 pt-24 pb-10 sm:pt-6 sm:pb-12 md:pt-60 md:pb-40 lg:pt-20 lg:pb-40">
    <div
      class="relative z-10 apw-card bg-[#ededed] border border-black/5 rounded-2xl p-5 md:p-7 lg:p-10 shadow-[0_18px_38px_rgba(0,0,0,0.08)] opacity-0 translate-y-2 transition duration-700 ease-out will-change-transform apw-fade"
      style="transition-delay:580ms"
    >
      <!-- Header -->
      <div class="grid gap-[14px] items-center justify-items-center text-center lg:justify-items-start lg:text-left">
        <div>
          <h2
            id="travel-title"
            class="m-0 text-[#3F2021] font-semibold leading-[1.08] tracking-[0.015em] text-[26px] sm:text-[32px] md:text-[38px] lg:text-[44px]"
          >
          </h2>
        </div>
      </div>

      <!-- Body -->
      <div class="mt-4 lg:mt-6 space-y-6">
        <p class="m-0 text-[#757575] font-medium leading-[1.65] text-[14px] md:text-[15px] lg:text-[20px]">
         The island of Bohol’s quaintness and charm provide a cozy retreat to those who seek privacy and exclusivity. Take a biking trip through the renowned Chocolate Hills, visit the endangered Tarsier population and trek to the most beautiful historical sites featuring remnants of the Spanish era in the Philippines. 


        </p>

        <p class="m-0 text-[#757575] font-medium leading-[1.65] text-[14px] md:text-[15px] lg:text-[20px]">
        After a long day of activity, experience the best that Filipino cuisine has to offer with a piping hot dinner featuring Boholano cuisine.
        </p>

        <!-- Divider -->
        <div class="h-px bg-gradient-to-r from-black/10 via-black/5 to-black/10"></div>
      </div>
    </div>
  </div>

  <!-- Simple IntersectionObserver to trigger fade-ins -->
  <script>
    (function () {
      const scope = document.currentScript.closest('section');
      const els = scope.querySelectorAll('.apw-fade');
      if (!('IntersectionObserver' in window)) {
        setTimeout(() => {
          els.forEach(el => {
            el.classList.remove('opacity-0', 'translate-y-2');
            el.classList.add('opacity-100', 'translate-y-0');
          });
        }, 200);
        return;
      }
      const io = new IntersectionObserver(
        (entries, obs) => {
          entries.forEach(entry => {
            if (entry.isIntersecting) {
              const el = entry.target;
              el.classList.remove('opacity-0', 'translate-y-2');
              el.classList.add('opacity-100', 'translate-y-0');
              obs.unobserve(el);
            }
          });
        },
        { rootMargin: '0px 0px -10% 0px', threshold: 0.15 }
      );
      els.forEach(el => io.observe(el));
    })();
  </script>
</section>


<!-- ===== Corporate Events (max 1600px, overflow-safe & truly responsive) ===== -->
<section id="corporate-events"
         class="group relative w-full overflow-x-hidden overflow-y-visible bg-slate-100 text-[#25282a]"
         aria-labelledby="corp-events-title">

  <!-- Wrapper (max 1600px) -->
  <div class="relative mx-auto max-w-[1600px] px-4 sm:px-6 lg:px-8 py-16 md:py-20 lg:py-28 xl:py-32">
    <div class="grid items-center gap-10 md:gap-12 lg:gap-16 xl:gap-20 lg:grid-cols-2 min-h-[460px]">

      <!-- Left: Title + copy + feature icons -->
      <div>
        <h2 id="corp-events-title"
            class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-semibold leading-[1.05] tracking-tight text-[#3F2021] ev-reveal"
            data-ev-reveal style="--ev-delay: 120ms;">
          CORPORATE EVENTS
        </h2>

        <div class="mt-3 h-1.5 w-[clamp(60px,24vw,120px)] rounded-full bg-[#CF4520]"
             data-ev-reveal style="--ev-delay: 200ms;"></div>

        <p class="mt-5 text-[#25282a]/85 text-base sm:text-lg md:text-xl font-medium max-w-prose ev-reveal"
           data-ev-reveal style="--ev-delay: 280ms;">
          Set the stage for your next corporate or social event
        </p>

        <ul class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4 ev-reveal"
            data-ev-reveal style="--ev-delay: 360ms;">
          <li class="flex items-center gap-3">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-[#CF4520]/10 ring-1 ring-[#CF4520]/20">
              <svg class="h-5 w-5 text-[#3F2021]" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2"
                   viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20v-2a4 4 0 00-4-4H7a4 4 0 00-4 4v2M13 7a4 4 0 11-8 0 4 4 0 018 0M20 8v6m3-3h-6"/></svg>
            </span>
            <div class="leading-tight">
              <p class="text-sm lg:text-xl font-semibold">Up to 60 guests</p>
              <p class="text-xs lg:text-base text-[#25282a]/70">Flexible capacity</p>
            </div>
          </li>

          <li class="flex items-center gap-3">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-[#CF4520]/10 ring-1 ring-[#CF4520]/20">
              <svg class="h-5 w-5 text-[#3F2021]" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2"
                   viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7h7v10H3zM14 7h7v5h-7zM14 14h7v3h-7z"/></svg>
            </span>
            <div class="leading-tight">
              <p class="text-sm lg:text-xl font-semibold">Theater • Classroom • Banquet</p>
              <p class="text-xs  lg:text-base text-[#25282a]/70">Multiple setups</p>
            </div>
          </li>

          <li class="flex items-center gap-3">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-[#CF4520]/10 ring-1 ring-[#CF4520]/20">
              <svg class="h-5 w-5 text-[#3F2021]" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2"
                   viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2 12h20M6 16h12a2 2 0 002-2V9a2 2 0 00-2-2H6a2 2 0 00-2 2v5a2 2 0 002 2z"/><circle cx="16" cy="12" r="2"/></svg>
            </span>
            <div class="leading-tight">
              <p class="text-sm lg:text-xl font-semibold">Integrated AV &amp; Tech</p>
              <p class="text-xs  lg:text-base text-[#25282a]/70">On-site support</p>
            </div>
          </li>

          <li class="flex items-center gap-3">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-[#CF4520]/10 ring-1 ring-[#CF4520]/20">
              <svg class="h-5 w-5 text-[#3F2021]" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2"
                   viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5a7 7 0 00-7 7h14a7 7 0 00-7-7zM5 14h14a3 3 0 01-3 3H8a3 3 0 01-3-3zM12 5V3"/></svg>
            </span>
            <div class="leading-tight">
              <p class="text-sm lg:text-xl font-semibold">Chef-curated Catering</p>
              <p class="text-xs  lg:text-base text-[#25282a]/70">Tailored menus</p>
            </div>
          </li>
        </ul>

        <div class="mt-8 ev-reveal" data-ev-reveal style="--ev-delay: 440ms;">
          <a href="{{ url('/meetings-events') }}"
             class="inline-flex items-center gap-2 rounded-full border border-[#CF4520] bg-transparent px-6 sm:px-7 py-3 sm:py-3.5
                    text-sm sm:text-base font-semibold tracking-wide text-[#CF4520] shadow-[0_8px_24px_rgba(0,0,0,0.08)]
                    transition duration-300 ease-out hover:bg-[#3F2021] hover:text-white
                    focus:outline-none focus-visible:ring-2 focus-visible:ring-[#3F2021]/50 transform-gpu hover:scale-[1.03]">
            Plan an Event
            <svg class="h-4 w-4 sm:h-5 sm:w-5" fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
          </a>
        </div>
      </div>

      <!-- Right: Media collage -->
      <div class="relative ev-reveal" data-ev-reveal style="--ev-delay: 260ms;">
        <!-- Wrapper is full width and cannot exceed viewport; negative offsets only on lg+ and clipped by section -->
        <div class="relative w-full overflow-visible">

          <!-- MAIN CARD -->
          <div class="relative z-10 w-full rounded-2xl overflow-hidden ring-1 ring-black/10
                      shadow-[0_20px_40px_rgba(0,0,0,0.16)]
                      transform-gpu transition duration-700 ease-out hover:scale-[1.02]
                      h-[220px] sm:h-[300px] md:h-[360px] lg:h-[420px] xl:h-[460px]">
            <img src="{{ asset('images/Home/meetings.webp') }}" alt="Ballroom with banquet setup"
                 class="absolute inset-0 w-full h-full max-w-full object-cover" loading="lazy" decoding="async" />
            <div class="absolute inset-0 bg-gradient-to-t from-black/15 via-black/0 to-transparent"></div>
          </div>

          <!-- MOBILE/TABLET: small cards become a grid under main (no overlapping, fully responsive) -->
          <div class="mt-4 grid grid-cols-2 gap-3 lg:hidden">
            <div class="rounded-xl overflow-hidden ring-1 ring-black/10 shadow-[0_10px_20px_rgba(0,0,0,0.12)]">
              <img src="{{ asset('images/Home/meetings1.webp') }}" alt="Boardroom with AV support"
                   class="block w-full h-28 xs:h-32 sm:h-48 object-cover" loading="lazy" decoding="async" />
            </div>
            <div class="rounded-xl overflow-hidden ring-1 ring-black/10 shadow-[0_10px_20px_rgba(0,0,0,0.12)]">
              <img src="{{ asset('images/Home/meetings2.webp') }}" alt="Cocktail reception area"
                   class="block w-full h-28 xs:h-32 sm:h-48 object-cover" loading="lazy" decoding="async" />
            </div>
          </div>

          <!-- DESKTOP: small cards overlap (with safe, clipped offsets) -->
          <div class="hidden lg:block">
            <!-- SMALL CARD — bottom-left -->
            <div class="absolute -bottom-20 -left-20 w-[40%] z-20">
              <div class="rounded-xl overflow-hidden ring-1 ring-black/10
                          shadow-[0_16px_32px_rgba(0,0,0,0.18)]
                          transform-gpu transition duration-700 ease-out hover:scale-[1.03]">
                <img src="{{ asset('images/Home/meetings1.webp') }}" alt="Boardroom with AV support"
                     class="block w-full h-40 xl:h-48 object-cover max-w-full" loading="lazy" decoding="async" />
              </div>
            </div>

            <!-- SMALL CARD — top-right -->
            <div class="absolute -top-20 -right-10 w-[30%] z-20">
              <div class="rounded-xl overflow-hidden ring-1 ring-black/10
                          shadow-[0_16px_32px_rgba(0,0,0,0.18)]
                          transform-gpu transition duration-700 ease-out hover:scale-[1.03]">
                <img src="{{ asset('images/Home/meetings2.webp') }}" alt="Cocktail reception area"
                     class="block w-full h-36 xl:h-44 object-cover max-w-full" loading="lazy" decoding="async" />
              </div>
            </div>
          </div>

        </div>
      </div>

    </div>
  </div>
</section>

<!-- Entrance animation (reuse if already included elsewhere) -->
<style>
  .ev-reveal{
    opacity:0;
    transform: translateY(10px);
    transition: opacity 600ms ease, transform 600ms ease;
    transition-delay: var(--ev-delay, 0ms);
    will-change: opacity, transform;
  }
  .ev-reveal.is-in{ opacity:1; transform: translateY(0); }
  @media (prefers-reduced-motion: reduce){
    .ev-reveal{ transition: none; transform: none; }
  }
</style>
<script>
  (function () {
    const scope = document.currentScript.closest('section') || document;
    const els = scope.querySelectorAll('[data-ev-reveal]');
    if (!('IntersectionObserver' in window)) { els.forEach(el => el.classList.add('is-in')); return; }
    const io = new IntersectionObserver((entries, obs) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-in');
          obs.unobserve(entry.target);
        }
      });
    }, { threshold: 0.15, rootMargin: '0px 0px -10% 0px' });
    els.forEach(el => io.observe(el));
  })();
</script>


{{-- ===== Explore Bohol (lighter overlay, mobile left-align, 600px desktop height) ===== --}}
<section id="explore-bohol"
         class="group relative w-full overflow-hidden text-white"
         aria-labelledby="bop-title">

  {{-- Background image + soft overlay (subtle grow on hover) --}}
  <div class="absolute inset-0 -z-10">
    <div class="absolute inset-0 bg-no-repeat bg-cover bg-[-74px] sm:bg-center
                transition-transform duration-[900ms] ease-[cubic-bezier(.22,1,.36,1)]
                will-change-transform transform-gpu group-hover:scale-[1.04]"
         style="
           background-image:url('{{ asset('images/Home/best-of.webp') }}');
           background-size:cover;
         ">
    </div>
    {{-- Lighter gradient overlay --}}
    <div class="absolute inset-0 bg-gradient-to-b from-black/20 via-black/10 to-black/30"></div>
  </div>

  {{-- Content wrapper --}}
  <div class="relative mx-auto max-w-[1600px] px-4 sm:px-6 lg:px-8">
    {{-- Mobile/tablet: min height; Desktop (lg+): fixed 600px --}}
    <div class="min-h-[480px] sm:min-h-[520px] md:min-h-[560px] lg:h-[600px] lg:min-h-[1050px]
                py-10 sm:py-14 lg:py-16 flex items-center">
      <div class="max-w-3xl ev-reveal" data-ev-reveal style="--ev-delay: 100ms;">
        <h2 id="bop-title"
            class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-semibold leading-[1.05] tracking-tight">
          EXPLORE BOHOL

        </h2>

        <div class="mt-3 h-1.5 w-[clamp(160px,24vw,120px)] rounded-full bg-[#E6E7E8]"></div>

        <p class="mt-5 text-base sm:text-lg md:text-xl text-white/90">
          Discover the beauty of Bohol, an enchanting island paradise where pristine beaches meet lush countryside. From the world-famous Chocolate Hills to the gentle tarsiers, Bohol offers a unique blend of natural wonders and rich culture. Experience the thrill of river cruising along the Loboc River, wander through centuries-old churches, or dive into the crystal-clear waters of Panglao for an underwater adventure. Whether you’re seeking relaxation, adventure, or a cultural escape, Bohol promises an unforgettable experience for every traveler.
        </p>
      </div>
    </div>
  </div>
</section>

{{-- Entrance animation helper (keep if not already present) --}}
<style>
  .ev-reveal{
    opacity:0; transform: translateY(10px);
    transition: opacity 600ms ease, transform 600ms ease;
    transition-delay: var(--ev-delay, 0ms);
    will-change: opacity, transform;
  }
  .ev-reveal.is-in{ opacity:1; transform: translateY(0); }
  @media (prefers-reduced-motion: reduce){
    .ev-reveal{ transition:none; transform:none; }
  }
</style>
<script>
  (function () {
    const scope = document.currentScript.closest('section') || document;
    const els = scope.querySelectorAll('[data-ev-reveal]');
    if (!('IntersectionObserver' in window)) {
      els.forEach(el => el.classList.add('is-in')); return;
    }
    const io = new IntersectionObserver((entries, obs) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-in');
          obs.unobserve(entry.target);
        }
      });
    }, { threshold: 0.15, rootMargin: '0px 0px -10% 0px' });
    els.forEach(el => io.observe(el));
  })();
</script>


<x-home-blogs-promos />
@endsection
