{{-- resources/views/layouts/app.blade.php --}}
<!--
                                                                                          █████╗ ██╗  ██╗██████╗       ██████╗ ███╗   ███╗
                                                                                         ██╔══██╗██║  ██║██╔══██╗      ██╔══██╗████╗ ████║   
                                                                                         ███████║███████║██████╔╝      ██║  ██║██╔████╔██║
                                                                                         ██╔══██║██╔══██║██╔══██╗      ██║  ██║██║╚██╔╝██║
                                                                                         ██║  ██║██║  ██║██║  ██║      ██████╔╝██║ ╚═╝ ██║
                                                                                         ╚═╝  ╚═╝╚═╝  ╚═╝╚═╝  ╚═╝      ╚═════╝ ╚═╝     ╚═╝

                                                                                     Developed by The Astoria Group Marketing Team (DTM - JBC)
                                                                                          (Laravel + TailwindCSS + AlpineJs app v1.0)

                                                                                            ######################################################
                                                                                            #   (\_/)
                                                                                            #   ( •_•)  Cookie you want?
                                                                                            #  / >🍪    
                                                                                            ######################################################
-->
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
  @if (config('app.noindex'))
  <meta name="robots" content="noindex, nofollow, noarchive, nosnippet, noimageindex">
@endif
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="google" content="notranslate"> {{-- suppress Chrome native translate bar --}}

  <title>@yield('title', config('app.name', 'Astoria Bohol'))</title>
  <link rel="icon" href="{{ asset('images/favicon.ico') }}"/>

  {{-- Tailwind + Vite --}}
  @vite(['resources/css/app.css', 'resources/js/app.js'])

  {{-- Fonts / Icons / CSS libs --}}
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />
  

  <style>
    /* Global font */
    html, body { font-family: var(--ahr-font); }
    [x-cloak]{ display:none!important; }

    /* Hide Google Website Translator banner (cover both old & new selectors) */
    iframe.goog-te-banner-frame { display:none !important; }
    .goog-te-banner-frame { display:none !important; }
    .goog-te-banner { display:none !important; }
    .VIpgJd-ZVi9od-ORHb-OEVmcd { display:none !important; } /* new banner container */
    .VIpgJd-ZVi9od-aZ2wEe-wOHMyf { display:none !important; } /* top-offset shim */
    .VIpgJd-ZVi9od-xl07Ob-OEVmcd { display:none !important; } /* overlay holder */

    /* Undo any injected top offset on body */
    body { top: 0 !important; position: static !important; }
    body[style*="top:"] { top:0 !important; }

    /* Keep the widget functional but invisible */
    #google_translate_element { position:fixed; left:-9999px; bottom:-9999px; }
    .goog-logo-link, .goog-te-gadget span { display:none !important; }
    .goog-te-gadget { height:0 !important; overflow:hidden !important; }

    /* Optional: hide tooltip + highlights */
    #goog-gt-tt { display:none !important; }
    .goog-text-highlight { background:none !important; box-shadow:none !important; }
  </style>

  @stack('styles')
  @stack('head')
<link rel="alternate icon" href="{{ asset('favicon.ico') }}">
</head>
<body class="antialiased">

{{-- NAV include ... --}}
 @includeFirst(['components.nav-header','compents.nav-header','partials.nav-header'])

<main class="relative">
  @yield('content')
</main>



{{-- Footer (conditionally included) --}}
@unless (View::hasSection('hide_footer'))
  @includeIf('components.footer')
@endunless



{{-- Back-to-top (MAIN #3F2021base, SECONDARY #CF4520 progress ring, ultra z-index) --}}
<div id="abh-btt"
     x-data="backToTop()"
     x-init="init()"
     class="fixed right-5 bottom-5"
     style="z-index:2147483647;">
  <button
    x-show="show"
    x-transition
    @click="scrollTop"
    aria-label="Back to top"
    class="group relative grid place-items-center w-14 h-14 rounded-full
           bg-[#3F2021] text-white
           shadow-[0_10px_28px_rgba(99,102,106,.55)]
           hover:shadow-[0_14px_36px_rgba(99,102,106,.7)]
           ring-2 ring-white/90
           focus:outline-none focus-visible:ring-4 focus-visible:ring-[#3F2021]/50
           transition">

    <!-- Subtle pulsing glow on hover/focus -->
    <span class="pointer-events-none absolute inset-0 rounded-full bg-[#3F2021]/45
                 opacity-0 group-hover:opacity-100 group-focus:opacity-100
                 animate-[ping_1.6s_ease-out_infinite]"></span>

    <!-- Scroll progress ring -->
    <svg class="absolute -inset-1 w-16 h-16" viewBox="0 0 60 60" aria-hidden="true">
      <!-- Track -->
      <circle cx="30" cy="30" r="26" stroke="rgba(99,102,106,.18)" stroke-width="4" fill="none"/>
      <!-- Progress -->
      <circle cx="30" cy="30" r="26"
              :stroke-dasharray="circumference"
              :stroke-dashoffset="circumference - (progress/100)*circumference"
              stroke="#CF4520" stroke-width="4" stroke-linecap="round" fill="none"/>
    </svg>

    <!-- Icon -->
    <i class="fa-solid fa-arrow-up text-xl relative z-10"></i>

    <!-- Tiny label on hover -->
    <span class="pointer-events-none absolute right-full mr-2 top-1/2 -translate-y-1/2
                  px-2 py-1 rounded bg-[#3F2021] text-white text-[11px]
                  opacity-0 group-hover:opacity-100 transition">
      Top
    </span>
  </button>
</div>

<script>
  function backToTop(){
    return {
      show: false,
      progress: 0,
      circumference: 2 * Math.PI * 26, // r=26 from the SVG circle

      onScroll(){
        const scrolled = window.scrollY || document.documentElement.scrollTop;
        const max = document.documentElement.scrollHeight - window.innerHeight;
        this.progress = max > 0 ? Math.min(100, Math.max(0, (scrolled / max) * 100)) : 0;
        this.show = scrolled > 300;
      },

      scrollTop(){ window.scrollTo({ top: 0, behavior: 'smooth' }); },

      init(){
        this.onScroll();
        window.addEventListener('scroll', () => this.onScroll(), { passive: true });
      }
    }
  }
</script>

<style>
  /* Respect users who prefer less motion */ 
  @media (prefers-reduced-motion: reduce) {
    #apz-btt .animate-\[ping_1\.6s_ease-out_infinite\] { animation: none !important; }
    #apz-btt button { transition: none !important; }
  }
  /* Don’t print the FAB */
  @media print { #apz-btt { display: none !important; } }
</style>


  {{-- Libraries --}}
  {{-- IMPORTANT: Swiper JS must NOT be deferred so page inits see it immediately --}}
  <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <script defer src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
  <script defer src="https://unpkg.com/@alpinejs/intersect@3.x.x/dist/cdn.min.js"></script>

  {{-- Google Translate (hidden widget, cookie-aware) --}}
  <div id="google_translate_element" aria-hidden="true"></div>
  <script>
    /* Cross-browser event helper used by Google’s typical doGTranslate flow */
    function GTranslateFireEvent(el, eventName) {
      try {
        if (document.createEvent) {
          var evObj = document.createEvent('HTMLEvents');
          evObj.initEvent(eventName, true, true);
          el.dispatchEvent(evObj);
        } else if (document.createEventObject) { // IE <= 8 fallback
          var evObj = document.createEventObject();
          el.fireEvent('on' + eventName, evObj);
        }
      } catch (e) {}
    }

    /* Reliable programmatic translate */
    function doGTranslate(langPair) {
      if (!langPair) return;
      var lang = langPair.split('|')[1];
      var combo = document.querySelector('.goog-te-combo');
      if (!combo) return setTimeout(function(){ doGTranslate(langPair); }, 100);
      combo.value = lang;
      GTranslateFireEvent(combo, 'change');
    }

    // Public helper; call from nav: window.applyTranslation('ja'), etc.
    window.applyTranslation = function(code) {
      var v = '/en/' + code;
      document.cookie = 'googtrans=' + v + ';path=/';
      document.cookie = 'googtrans=' + v + ';domain=' + location.hostname + ';path=/';
      doGTranslate('en|' + code);
    };

    function googleTranslateElementInit() {
      new google.translate.TranslateElement({
        pageLanguage: 'en',
        autoDisplay: false
      }, 'google_translate_element');

      // Apply cookie-selected language on load
      var m = document.cookie.match(/googtrans=\/en\/([a-zA-Z\-]+)/);
      var code = m && m[1];
      if (code) doGTranslate('en|' + code);
    }

    // Kill any injected banner/offset that slips through
    (function killGTranslateBar(){
      function nuke() {
        var ifr = document.querySelector('iframe.goog-te-banner-frame');
        if (ifr && ifr.parentNode) ifr.parentNode.removeChild(ifr);
        [
          '.goog-te-banner', '.goog-te-banner-frame',
          '.VIpgJd-ZVi9od-ORHb-OEVmcd', '.VIpgJd-ZVi9od-aZ2wEe-wOHMyf', '.VIpgJd-ZVi9od-xl07Ob-OEVmcd'
        ].forEach(function(s){
          document.querySelectorAll(s).forEach(function(el){ el.style.display='none'; });
        });
        document.body.style.top = '0px';
      }
      window.addEventListener('load', nuke);
      document.addEventListener('DOMContentLoaded', nuke);
      var tries = 0, t = setInterval(function(){ nuke(); if (++tries > 240) clearInterval(t); }, 50); // ~12s
    })();
  </script>
  <script src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>

  {{-- Page-specific scripts (e.g., Swiper inits) come after libs --}}
  @stack('scripts')

    {{-- Site-wide pop-up runner (front-end) --}}
  @includeIf('components.popup-runner')

  @include('components.cookie-consent')
</body>
</html>
