{{-- resources/views/components/nav-header.blade.php --}}
<style>
  [x-cloak]{ display:none !important; }

  /* Prevent horizontal scroll leaks globally */
  html, body { overflow-x: hidden; overscroll-behavior-x: none; }
  @supports (scrollbar-gutter: stable) {
    html { scrollbar-gutter: stable; } /* avoid extra right gap */
  }

  /* ======= BASE HEADER ======= */
  .ahr-header{
    background:#FFEED7; /* default */
    color:#ffffff;
    border-bottom:1.5px solid #13294B; /* thin border on all devices */
    min-height:4rem; /* base */
    width:100%;
    max-width:100vw;          /* clamp to viewport */
    overflow-x: clip;         /* stop abs layers from widening layout */
    /* REMOVED: contain: paint; (clips fixed children like mobile menu) */
  }
  @supports not (overflow: clip) { .ahr-header{ overflow-x:hidden; } }

  /* ======= SCROLLED COLOR STATE ======= */
  .ahr-header[data-ready="true"][data-scrolled="true"]{
    background:#3F2021; /* scrolled */
    color:#ffffff;
    border-color:#13294B; /* keep thin border even when scrolled */
  }

  /* ======= MOBILE/TABLET HEIGHTS (portrait by default) ======= */
  @media (max-width:1279px){
    .ahr-header{ min-height:3.25rem; } /* ~52px default */
    .ahr-header[data-ready="true"][data-scrolled="true"]{ min-height:3rem; } /* ~48px scrolled */
    .topbar-mobile{ height:3.25rem; }
    .ahr-header[data-scrolled="true"] .topbar-mobile{ height:3rem; }
  }

  /* Landscape phones & tablets: adjusted paddings + bigger logo */
  @media (max-width:1279px) and (orientation: landscape){
    .ahr-header{ min-height:2.75rem; } /* ~44px default */
    .ahr-header[data-ready="true"][data-scrolled="true"]{ min-height:2.5rem; } /* ~40px scrolled */
    .topbar-mobile{ height:2.75rem; }
    .ahr-header[data-scrolled="true"] .topbar-mobile{ height:2.5rem; }
    #ahr-nav{ padding-left:clamp(12px,3vw,24px) !important; padding-right:clamp(12px,3vw,24px) !important; }
    .topbar-mobile svg{ width:24px; height:24px; }
  }

  /* ======= SPECIAL TIGHT BAND: 1024–1279px ======= */
  @media (min-width:1024px) and (max-width:1279px){
    .ahr-header{ min-height:2.75rem; } /* ~44px default */
    .ahr-header[data-ready="true"][data-scrolled="true"]{ min-height:2.5rem; } /* ~40px scrolled */
    .topbar-mobile{ height:2.75rem; }
    .ahr-header[data-scrolled="true"] .topbar-mobile{ height:2.5rem; }
    #ahr-nav{ padding-top:0.25rem !important; padding-bottom:0.25rem !important; }
    .topbar-mobile svg{ width:22px; height:22px; }
  }

  /* ======= DESKTOP (xl and up) ======= */
  @media (min-width:1280px){ .ahr-header{ min-height:5rem; } } /* h-20 for desktop */

  /* ======= MICRO UX HELPERS ======= */
  .logo-smooth { transition: transform 280ms cubic-bezier(.22,1,.36,1); will-change: transform; transform: translateZ(0); }
  .fade-img    { transition: opacity 220ms ease; will-change: opacity; }

  /* Logo swap states */
  .logo-wrap, .m-logo-wrap { position: relative; }
  .logo-default  { opacity: 1; }
  .logo-scrolled { opacity: 0; }
  .logo-wrap[data-scrolled="true"] .logo-default,
  .m-logo-wrap[data-scrolled="true"] .logo-default { opacity: 0; }
  .logo-wrap[data-scrolled="true"] .logo-scrolled,
  .m-logo-wrap[data-scrolled="true"] .logo-scrolled { opacity: 1; }

  /* Slight scale change on mobile when scrolled (kept larger than before) */
  .m-logo-wrap[data-scrolled="false"] .logo-scaler { transform: scale(1.06); }
  .m-logo-wrap[data-scrolled="true"]  .logo-scaler { transform: scale(0.98); }

  /* ======= NAV TYPOGRAPHY ======= */
  :root { --ahr-font: "Whitney", "Open Sans", sans-serif; }
  .ahr-nav, .ahr-nav a, .ahr-nav button { font-family: var(--ahr-font); font-weight:600; letter-spacing:.01em; }
  .ahr-nav { font-size: 1.0625rem; }
  @media (max-width:1279px){ .ahr-nav { font-size: 1rem; } }
  .ahr-mobile, .ahr-mobile a, .ahr-mobile button { font-family: var(--ahr-font); font-weight: 600; letter-spacing:.01em; font-size:1rem; }

  /* ======= DESKTOP DROPDOWNS ======= */
  .menu-wrap { position: relative; }
  .menu-layer { position:absolute; top:100%; max-width:100vw; }
  .menu-panel { margin-top:.35rem; }

  /* Native option text readable */
  .ahr-header select option { color:#111; }

  /* Tap highlight removal */
  .ahr-header a, .ahr-header button { -webkit-tap-highlight-color: transparent; }

  /* Kill transitions on desktop only (≥1280px) */
  @media (min-width:1280px){
    .ahr-header.no-animate, .ahr-header.no-animate *{
      transition:none !important;
      animation:none !important;
    }
  }

  /* Bold form UI in header */
  .ahr-header label,
  .ahr-header input,
  .ahr-header select,
  .ahr-header textarea,
  .ahr-header option { font-weight: 700 !important; }
  .ahr-header input::placeholder,
  .ahr-header textarea::placeholder { font-weight: 700; opacity: 1; }

  /* iPad/similar width & overflow safety */
  @media (min-width:740px) and (max-width:1368px){
    .ahr-header, .ahr-header * { box-sizing: border-box; }
    .ahr-header { width:100% !important; max-width:100% !important; margin-left:0 !important; margin-right:0 !important; }
    #ahr-nav{ padding-left:clamp(16px,4vw,32px) !important; padding-right:clamp(16px,4vw,32px) !important; max-width:100% !important; }
  }
</style>

<header
  class="ahr-header fixed top-0 w-full z-50 transition-colors duration-300 border-b border-[#13294B]"
  data-ready="false"
  data-scrolled="false"
  x-data="{
    scrolled: false,
    openMenu: null,
    activeDropdown: null,
    ticking: false,
    logoDefault: '{{ asset('images/abh-logo.webp') }}',
    logoOnDark:  '{{ asset('images/abh-logo-white.webp') }}',

    langs: [
      { flag: '{{ asset('images/Flags/united-kingdom.webp') }}', name: 'English',               abbr: 'EN', code: 'en' },
      { flag: '{{ asset('images/Flags/korea.webp') }}',           name: 'Korean',                abbr: 'KO', code: 'ko' },
      { flag: '{{ asset('images/Flags/china (1).webp') }}',       name: 'Traditional Chinese',   abbr: 'ZH', code: 'zh-TW' },
      { flag: '{{ asset('images/Flags/japan.webp') }}',           name: 'Japanese',              abbr: 'JA', code: 'ja' },
      { flag: '{{ asset('images/Flags/french.webp') }}',          name: 'French',                abbr: 'FR', code: 'fr' },
      { flag: '{{ asset('images/Flags/german.webp') }}',          name: 'German',                abbr: 'DE', code: 'de' },
      { flag: '{{ asset('images/Flags/spanish.webp') }}',         name: 'Spanish',               abbr: 'ES', code: 'es' },
      { flag: '{{ asset('images/Flags/arabic.webp') }}',          name: 'Arabic',                abbr: 'AR', code: 'ar' }
    ],
    selectedLang: { flag: '{{ asset('images/Flags/united-kingdom.webp') }}', name: 'English', abbr: 'EN', code: 'en' },

    setSelectedFromCookie(){
      const m = document.cookie.match(/googtrans=\/en\/([a-zA-Z\-]+)/);
      const saved = m ? m[1] : (localStorage.getItem('preferred_lang') || 'en');
      const found = this.langs.find(l => l.code === saved);
      this.selectedLang = found || this.langs[0];
    },
    setLanguage(code){
      localStorage.setItem('preferred_lang', code);
      const v = '/en/' + code;
      document.cookie = 'googtrans=' + v + ';path=/';
      document.cookie = 'googtrans=' + v + ';domain=' + window.location.hostname + ';path=/';
      const found = this.langs.find(l => l.code === code);
      if (found) this.selectedLang = found;
      if (window.applyTranslation) window.applyTranslation(code);
    },

    openHover(id){ this.openMenu = id; },
    closeHover(id){ if (this.openMenu === id) this.openMenu = null; },

    onScroll(){ const next = window.scrollY > 10; if (next !== this.scrolled) this.scrolled = next; },

    navClick(e){
      const a = e.target.closest('a[href]');
      if (!a) return;
      if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || a.getAttribute('target') === '_blank' || e.button !== 0) return;
      if (window.matchMedia('(min-width: 1280px)').matches) {
        this.openMenu = null;
        this.$nextTick(() => this.$root.classList.add('no-animate'));
      }
    }
  }"
  x-init="
    setSelectedFromCookie();
    scrolled = window.scrollY > 10;
    $el.dataset.ready = 'true';

    const handleScroll = () => {
      if (!ticking) {
        requestAnimationFrame(() => { onScroll(); ticking = false; });
        ticking = true;
      }
    };
    window.addEventListener('scroll', handleScroll, { passive: true });
  "
  @click.away="openMenu = null"
  @click.capture="navClick($event)"
  :data-scrolled="scrolled"
>
  {{-- Preload logos --}}
  <img src="{{ asset('images/abh-logo.webp') }}" alt="" class="hidden" aria-hidden="true">
  <img src="{{ asset('images/abh-logo-white.webp') }}" alt="" class="hidden" aria-hidden="true">

  <nav id="ahr-nav" class="relative mx-auto px-4 sm:px-6 lg:px-8 overflow-x-clip max-w-[min(1575px,100vw)]">

    {{-- ===================== DESKTOP (xl and up) — NOT SCROLLED ===================== --}}
    <div class="hidden xl:flex items-center justify-between h-16 md:h-20 gap-3" x-show="!scrolled" x-cloak>
      {{-- Left: Logo --}}
      <a href="{{ route('home') }}" aria-label="AHR Home" class="shrink-0">
        <div class="logo-wrap w-56 md:w-72 h-12 md:h-16" :data-scrolled="false">
          <div class="logo-smooth logo-scaler h-full w-full relative flex items-center justify-start">
            <img :src="logoDefault"
                 onerror="this.onerror=null; this.src='{{ asset('images/abh-logo.webp') }}';"
                 alt="Astoria Plaza"
                 class="fade-img absolute inset-0 h-full w-full object-contain logo-default"/>
            <img :src="logoOnDark" class="fade-img absolute inset-0 h-full w-full object-contain logo-scrolled" alt="">
          </div>
        </div>
      </a>

      {{-- Right: Menus + Language + DESKTOP Contact Us CTA --}}
      <div class="flex items-center gap-6 min-w-0">
        <div class="ahr-nav flex items-center gap-5 2xl:gap-6 min-w-0">
          <a href="{{ route('home') }}" class="hover:opacity-80 whitespace-nowrap text-[#63666a]">Home</a>

          {{-- About (label is a link; chevron toggles dropdown) --}}
          <div class="menu-wrap" @mouseenter="openHover('about')" @mouseleave="closeHover('about')">
            <div class="inline-flex items-center gap-1 text-[#63666A]">
              <a href="{{ route('about') }}" class="hover:opacity-80 whitespace-nowrap text-[#63666a]">About</a>
              <button type="button"
                      class="p-1 -mr-1 hover:opacity-80"
                      :aria-expanded="openMenu==='about'"
                      aria-controls="menu-about"
                      @click.prevent="openMenu = openMenu==='about' ? null : 'about'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
              </button>
            </div>
            <div class="menu-layer left-0 z-[900]">
              <div id="menu-about"
                   x-show="openMenu==='about'"
                   x-transition.opacity.duration.120ms
                   x-cloak
                   class="menu-panel w-80 rounded-md shadow-lg bg-white text-[#63666a] ring-1 ring-black/5 overflow-hidden">
                <a @click="openMenu=null" href="{{ route('amenities') }}"      class="block px-4 py-2 hover:bg-gray-100">Amenities</a>
                <a @click="openMenu=null" href="{{ route('explore') }}"      class="block px-4 py-2 hover:bg-gray-100">Explore</a>
                <a @click="openMenu=null" href="{{ route('faqs') }}"      class="block px-4 py-2 hover:bg-gray-100">FAQs</a>
                <a @click="openMenu=null" href="{{ route('blogs') }}"     class="block px-4 py-2 hover:bg-gray-100">Blogs</a>
                <a @click="openMenu=null" href="{{ route('promos') }}"    class="block px-4 py-2 hover:bg-gray-100">Promos</a>
              </div>
            </div>
          </div>

          {{-- Accommodations (label is a link; chevron toggles dropdown) --}}
          <div class="menu-wrap" @mouseenter="openHover('accommodations')" @mouseleave="closeHover('accommodations')">
            <div class="inline-flex items-center gap-1 text-[#63666a]">
              <a href="{{ route('accommodations') }}" class="hover:opacity-80 whitespace-nowrap">
                Accommodations
              </a>
              <button type="button"
                      class="p-1 -mr-1 hover:opacity-80"
                      :aria-expanded="openMenu==='accommodations'"
                      aria-controls="menu-accommodations"
                      @click.prevent="openMenu = openMenu==='accommodations' ? null : 'accommodations'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
              </button>
            </div>

            <div class="menu-layer left-0 z-[900]">
              <div id="menu-accommodations"
                   x-show="openMenu==='accommodations'"
                   x-transition.opacity.duration.120ms
                   x-cloak
                   class="menu-panel w-80 rounded-md shadow-lg bg-white text-[#63666A] ring-1 ring-black/5 overflow-hidden">
                <a @click="openMenu=null" href="{{ route('deluxe') }}"    class="block px-4 py-2 hover:bg-gray-100">Deluxe Room</a>
                <a @click="openMenu=null" href="{{ route('luxury') }}"   class="block px-4 py-2 hover:bg-gray-100">Luxury Room</a>
              </div>
            </div>
          </div>

          {{-- Meetings & Events --}}
          <div class="menu-wrap z-[900] text-[#63666a]">
            <a href="{{ url('/meetings-events') }}" class="hover:opacity-80 whitespace-nowrap">
              Meetings &amp; Events
            </a>
          </div>

          {{-- Dining --}}
          <div>
            <a href="{{ url('/food-beverages') }}" class="flex justify-between w-full py-2 font-semibold text-[#63666a]">
              <span>F&amp;B</span>
            </a>
          </div>

          {{-- Lantawan --}}
          <div>
            <a href="{{ url('/astoria-bohol-lantawan') }}" class="flex justify-between w-full py-2 font-semibold text-[#63666a]">
              <span>Astoria Bohol Lantawan</span>
            </a>
          </div>
        </div>

        {{-- Language --}}
        <div class="hidden xl:block relative z-[1000] shrink-0 text-[#63666a]">
          <div class="menu-wrap" @mouseenter="openHover('language')" @mouseleave="closeHover('language')">
            <button class="inline-flex items-center gap-2 hover:opacity-80 font-semibold"
                    :aria-expanded="openMenu==='language'"
                    @click="openMenu = openMenu==='language' ? null : 'language'">
              <img :src="selectedLang.flag" class="w-5 h-5 rounded-sm" alt="">
              <span class="hidden 2xl:inline" x-text="selectedLang.name"></span>
              <span class="2xl:hidden" x-text="selectedLang.abbr"></span>
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div class="menu-layer right-0 z-[1000]">
              <div x-show="openMenu==='language'" x-transition.opacity.duration.120ms x-cloak
                   class="menu-panel w-64 rounded-md shadow-xl bg-white text-[#25282a] ring-1 ring-black/5 overflow-hidden font-semibold">
                <template x-for="lang in langs" :key="lang.code">
                  <a href="#" class="flex items-center gap-2 px-4 py-2 hover:bg-gray-100"
                     @click.prevent="setLanguage(lang.code); openMenu=null;">
                    <img :src="lang.flag" class="w-5 h-5 rounded-sm" alt="">
                    <span x-text="lang.name"></span>
                    <span class="ml-auto opacity-70" x-text="'(' + lang.abbr + ')'"></span>
                  </a>
                </template>
              </div>
            </div>
          </div>
        </div>

        {{-- DESKTOP ONLY (xl): CONTACT US CTA --}}
        <div class="hidden xl:block shrink-0">
          <a
            href="{{ route('contact') }}"
            class="inline-flex items-center justify-center bg-[#CF4520] text-white hover:brightness-110 font-bold px-5 py-2.5 rounded-xl shadow transition text-sm uppercase tracking-widest"
          >
            Contact Us
          </a>
        </div>

      </div>
    </div>

    {{-- ===================== SCROLLED DESKTOP (xl only) — SAME NAV, WHITE LINKS ===================== --}}
    <div class="hidden xl:flex items-center justify-between h-16 md:h-20 gap-3" x-show="scrolled" x-cloak>
      {{-- Left: Logo (scrolled state for swap) --}}
      <a href="{{ route('home') }}" aria-label="AHR Home" class="shrink-0">
        <div class="logo-wrap w-56 md:w-72 h-12 md:h-16" :data-scrolled="true">
          <div class="logo-smooth logo-scaler h-full w-full relative flex items-center justify-start">
            <img :src="logoDefault"
                 onerror="this.onerror=null; this.src='{{ asset('images/abh-logo.webp') }}';"
                 alt="Astoria Plaza"
                 class="fade-img absolute inset-0 h-full w-full object-contain logo-default"/>
            <img :src="logoOnDark" class="fade-img absolute inset-0 h-full w-full object-contain logo-scrolled" alt="">
          </div>
        </div>
      </a>

      {{-- Right: Menus + Language + DESKTOP Contact Us CTA (white links) --}}
      <div class="flex items-center gap-6 min-w-0">
        <div class="ahr-nav flex items-center gap-5 2xl:gap-6 min-w-0 text-white">
          <a href="{{ route('home') }}" class="hover:opacity-80 whitespace-nowrap text-white">Home</a>

          {{-- About (label is a link; chevron toggles dropdown) --}}
          <div class="menu-wrap" @mouseenter="openHover('about')" @mouseleave="closeHover('about')">
            <div class="inline-flex items-center gap-1 text-white">
              <a href="{{ route('about') }}" class="hover:opacity-80 whitespace-nowrap text-white">About</a>
              <button type="button"
                      class="p-1 -mr-1 hover:opacity-80"
                      :aria-expanded="openMenu==='about'"
                      aria-controls="menu-about"
                      @click.prevent="openMenu = openMenu==='about' ? null : 'about'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
              </button>
            </div>
            <div class="menu-layer left-0 z-[900]">
              <div id="menu-about"
                   x-show="openMenu==='about'"
                   x-transition.opacity.duration.120ms
                   x-cloak
                   class="menu-panel w-80 rounded-md shadow-lg bg-white text-[#63666a] ring-1 ring-black/5 overflow-hidden">
                <a @click="openMenu=null" href="{{ route('amenities') }}"      class="block px-4 py-2 hover:bg-gray-100">Amenities</a>
                <a @click="openMenu=null" href="{{ route('explore') }}"      class="block px-4 py-2 hover:bg-gray-100">Explore</a>
                <a @click="openMenu=null" href="{{ route('faqs') }}"      class="block px-4 py-2 hover:bg-gray-100">FAQs</a>
                <a @click="openMenu=null" href="{{ route('blogs') }}"     class="block px-4 py-2 hover:bg-gray-100">Blogs</a>
                <a @click="openMenu=null" href="{{ route('promos') }}"    class="block px-4 py-2 hover:bg-gray-100">Promos</a>
              </div>
            </div>
          </div>

          {{-- Accommodations (label is a link; chevron toggles dropdown) --}}
          <div class="menu-wrap" @mouseenter="openHover('accommodations')" @mouseleave="closeHover('accommodations')">
            <div class="inline-flex items-center gap-1 text-white">
              <a href="{{ route('accommodations') }}" class="hover:opacity-80 whitespace-nowrap text-white">
                Accommodations
              </a>
              <button type="button"
                      class="p-1 -mr-1 hover:opacity-80"
                      :aria-expanded="openMenu==='accommodations'"
                      aria-controls="menu-accommodations"
                      @click.prevent="openMenu = openMenu==='accommodations' ? null : 'accommodations'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
              </button>
            </div>

            <div class="menu-layer left-0 z-[900]">
              <div id="menu-accommodations"
                   x-show="openMenu==='accommodations'"
                   x-transition.opacity.duration.120ms
                   x-cloak
                   class="menu-panel w-80 rounded-md shadow-lg bg-white text-[#63666A] ring-1 ring-black/5 overflow-hidden">
                <a @click="openMenu=null" href="{{ route('deluxe') }}"    class="block px-4 py-2 hover:bg-gray-100">Deluxe Room</a>
                <a @click="openMenu=null" href="{{ route('luxury') }}"   class="block px-4 py-2 hover:bg-gray-100">Luxury Room</a>
              </div>
            </div>
          </div>

          {{-- Meetings & Events --}}
          <div class="menu-wrap z-[900] text-white">
            <a href="{{ url('/meetings-events') }}" class="hover:opacity-80 whitespace-nowrap text-white">
              Meetings &amp; Events
            </a>
          </div>

          {{-- Dining --}}
          <div>
            <a href="{{ url('/food-beverages') }}" class="flex justify-between w-full py-2 font-semibold text-white">
              <span>F&amp;B</span>
            </a>
          </div>

          {{-- Amenities --}}
          <div>
            <a href="{{ url('/astoria-bohol-lantawan') }}" class="flex justify-between w-full py-2 font-semibold text-white">
              <span>Astoria Bohol Lantawan</span>
            </a>
          </div>
        </div>

        {{-- Language (white trigger, same dropdown) --}}
        <div class="hidden xl:block relative z-[1000] shrink-0 text-white">
          <div class="menu-wrap" @mouseenter="openHover('language')" @mouseleave="closeHover('language')">
            <button class="inline-flex items-center gap-2 hover:opacity-80 font-semibold text-white"
                    :aria-expanded="openMenu==='language'"
                    @click="openMenu = openMenu==='language' ? null : 'language'">
              <img :src="selectedLang.flag" class="w-5 h-5 rounded-sm" alt="">
              <span class="hidden 2xl:inline" x-text="selectedLang.name"></span>
              <span class="2xl:hidden" x-text="selectedLang.abbr"></span>
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div class="menu-layer right-0 z-[1000]">
              <div x-show="openMenu==='language'" x-transition.opacity.duration.120ms x-cloak
                   class="menu-panel w-64 rounded-md shadow-xl bg-white text-[#25282a] ring-1 ring-black/5 overflow-hidden font-semibold">
                <template x-for="lang in langs" :key="lang.code">
                  <a href="#" class="flex items-center gap-2 px-4 py-2 hover:bg-gray-100"
                     @click.prevent="setLanguage(lang.code); openMenu=null;">
                    <img :src="lang.flag" class="w-5 h-5 rounded-sm" alt="">
                    <span x-text="lang.name"></span>
                    <span class="ml-auto opacity-70" x-text="'(' + lang.abbr + ')'"></span>
                  </a>
                </template>
              </div>
            </div>
          </div>
        </div>

        {{-- DESKTOP ONLY (xl): CONTACT US CTA (kept same) --}}
        <div class="hidden xl:block shrink-0">
          <a
            href="{{ route('contact') }}"
            class="inline-flex items-center justify-center bg-[#CF4520] text-white hover:brightness-110 font-bold px-5 py-2.5 rounded-xl shadow transition text-sm uppercase tracking-widest"
          >
            Contact Us
          </a>
        </div>

      </div>
    </div>

    {{-- ===================== MOBILE / TABLET TOP BAR ===================== --}}
    <div class="topbar-mobile flex xl:hidden items-center justify-between gap-4">
      <a href="{{ route('home') }}" aria-label="AHR Home" class="shrink-0">
        {{-- Bigger mobile logo — colored by default, white when scrolled --}}
        <div class="m-logo-wrap w-44 h-14 md:w-52 md:h-16" :data-scrolled="scrolled">
          <div class="logo-smooth logo-scaler h-full w-full relative flex items-center justify-start">
            {{-- default (not scrolled) = abh-logo.webp --}}
            <img
              :src="logoDefault"
              onerror="this.onerror=null; this.src='{{ asset('images/abh-logo.webp') }}';"
              class="fade-img absolute inset-0 h-full w-full object-contain logo-default"
              alt="Astoria Bohol"
            >
            {{-- scrolled = abh-logo-white.webp --}}
            <img
              :src="logoOnDark"
              class="fade-img absolute inset-0 h-full w-full object-contain logo-scrolled"
              alt="Astoria Bohol"
            >
          </div>
        </div>
      </a>
      <button class="p-2 shrink-0"
              @click.stop="openMenu = openMenu === 'mobile' ? null : 'mobile'"
              aria-label="Open menu">
        {{-- Burger: grey by default, white when scrolled --}}
        <svg class="w-7 h-7 transition-colors duration-200"
             :class="scrolled ? 'text-white' : 'text-[#63666A]'"
             fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
      </button>
    </div>

  </nav>

  {{-- ===================== MOBILE/TABLET FULL-SCREEN MENU ===================== --}}
  <div
    x-show="openMenu === 'mobile'"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 scale-95"
    x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95"
    x-cloak
    class="ahr-mobile xl:hidden fixed inset-0 overflow-y-auto z-[9999] transition-colors duration-300"
    :class="scrolled ? 'bg-[#3F2021] text-white' : 'bg-[#3F2021] text-white'"
    @keydown.escape.window="openMenu = null"
    @click.self="openMenu = null"
  >
    <div class="px-5 py-5 flex items-center justify-between">
      {{-- Mobile logo inside overlay — always white --}}
      <div class="m-logo-wrap w-44 h-12 md:w-52 md:h-14" :data-scrolled="scrolled">
        <div class="logo-smooth logo-scaler h-full w-full relative flex items-center justify-start">
          <img
            src="{{ asset('images/abh-logo-white.webp') }}"
            onerror="this.onerror=null; this.src='{{ asset('images/abh-logo.webp') }}';"
            class="fade-img h-full w-full object-contain"
            alt="Astoria Bohol"
          >
        </div>
      </div>
      <button @click="openMenu = null" aria-label="Close menu" class="p-2 hover:opacity-80">
        <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>

    <div class="px-6 pb-16 space-y-6">

      {{-- ===== Primary CTA: Contact Us (mobile/tablet) ===== --}}
      <div class="pt-1">
        <a
          href="{{ route('contact') }}"
          class="w-full inline-flex items-center justify-center gap-2 font-bold px-5 py-3 rounded-xl shadow transition text-sm uppercase tracking-widest bg-[#CF4520] text-white hover:brightness-110"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
            <rect x="3" y="5" width="18" height="14" rx="2" ry="2"></rect>
            <path d="M3 7l9 6 9-6"></path>
          </svg>
          <span>Contact Us</span>
        </a>
      </div>

      {{-- ===== Full menu items (mobile/tablet) ===== --}}
      <nav class="space-y-2">
        <a @click="openMenu = null" href="{{ route('home') }}" class="block py-2">Home</a>

        {{-- About (split: link + chevron) --}}
        <div x-data="{ open:false }" class="border-y border-black/10/0">
          <div class="flex justify-between w-full py-2 items-center">
            <a @click="openMenu=null" href="{{ route('about') }}" class="font-semibold">About</a>
            <button @click="open = !open" class="p-1" :aria-expanded="open.toString()" aria-controls="m-about">
              <svg :class="open ? 'rotate-180' : ''" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
              </svg>
            </button>
          </div>
          <div id="m-about" x-show="open" x-transition x-cloak class="pl-4 space-y-2 font-semibold">
            <a @click="openMenu=null" href="{{ route('amenities') }}"      class="block py-1">Amenities</a>
            <a @click="openMenu=null" href="{{ route('explore') }}"      class="block py-1">Explore</a>
            <a @click="openMenu=null" href="{{ route('faqs') }}"      class="block py-1">FAQs</a>
            <a @click="openMenu=null" href="{{ route('blogs') }}"     class="block py-1">Blogs</a>
            <a @click="openMenu=null" href="{{ route('promos') }}"    class="block py-1">Promos</a>
          </div>
        </div>

        {{-- Accommodations (split: link + chevron) --}}
        <div x-data="{ open:false }">
          <div class="flex justify-between w-full py-2 items-center">
            <a @click="openMenu=null" href="{{ route('accommodations') }}" class="font-semibold">Accommodations</a>
            <button @click="open = !open" class="p-1" :aria-expanded="open.toString()" aria-controls="m-accommodations">
              <svg :class="open ? 'rotate-180' : ''" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
              </svg>
            </button>
          </div>
          <div id="m-accommodations" x-show="open" x-transition x-cloak class="pl-4 space-y-2 font-semibold">
            <a @click="openMenu=null" href="{{ route('deluxe') }}"    class="block py-1">Deluxe Room</a>
            <a @click="openMenu=null" href="{{ route('luxury') }}"   class="block py-1">Luxury Room</a>
          </div>
        </div>
        
        <a href="{{ url('/astoria-bohol-lantawan') }}" class="block py-2 font-semibold">Astoria Bohol Lantawan</a>
        <a href="{{ url('/food-beverages') }}" class="block py-2 font-semibold">F&amp;B</a>
        <a href="{{ url('/meetings-events') }}" class="block py-2 font-semibold">Meetings &amp; Events</a>

        {{-- Language --}}
        <div x-data="{ open:false }" class="pt-2">
          <div class="flex justify-between w-full py-2 items-center gap-2 font-semibold">
            <span class="flex items-center gap-2">
              <img :src="selectedLang.flag" class="w-5 h-5 rounded-sm" alt="">
              <span x-text="selectedLang.name"></span>
              <span class="opacity-70" x-text="'(' + selectedLang.abbr + ')'"></span>
            </span>
            <button @click="open = !open" class="p-1" :aria-expanded="open.toString()" aria-controls="m-language">
              <svg :class="open ? 'rotate-180' : ''" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
              </svg>
            </button>
          </div>
          <div id="m-language" x-show="open" x-transition x-cloak class="pl-4 space-y-2">
            <template x-for="lang in langs" :key="lang.code">
              <a href="#" class="flex items-center font-semibold gap-2 py-1"
                 @click.prevent="setLanguage(lang.code); openMenu=null;">
                <img :src="lang.flag" class="w-5 h-5 rounded-sm" alt="">
                <span x-text="lang.name"></span>
                <span class="ml-auto font-semibold opacity-70" x-text="'(' + lang.abbr + ')'"></span>
              </a>
            </template>
          </div>
        </div>
      </nav>

      <div class="h-8"></div>
    </div>
  </div>
</header>
