{{-- resources/views/components/cookie-consent.blade.php --}}
@php
  use Illuminate\Support\Facades\Route as R;
  $privacyUrl = url('/privacy-policy');
  if (R::has('privacy-policy'))      $privacyUrl = route('privacy-policy');
  elseif (R::has('privacy'))         $privacyUrl = route('privacy');
@endphp

<style>
  [x-cloak]{display:none!important}

  .cc-root{
    position: fixed;
    inset: 0;
    z-index: 9999;
    pointer-events: none;
  }

  /* Base visibility for CARD containers only */
  .cc-mobile  { display:block; }
  .cc-desktop { display:none; }

  @media (min-width:768px){
    .cc-mobile  { display:none; }
    .cc-desktop { display:block; }
  }

  /* Mobile popup (card) */
  .cc-mobile-popup{
    position: fixed;
    left: 12px;
    bottom: 16px;
    width: 50vw !important;
    min-width: 260px;
    max-width: 420px;
    box-sizing: border-box;
    pointer-events: auto;
  }

  .cc-mobile-popup,
  .cc-desktop-card{
    display: inline-block;
  }

  /* Desktop wrapper (card) */
  .cc-desktop-wrap{
    position: fixed;
    left: 16px;
    bottom: 16px;
    pointer-events: auto;
  }

  /* Modal containers (overlay + centered panel) */
  .cc-modal{
    position: fixed;
    inset: 0;
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
    pointer-events: auto;
  }

  .cc-modal-panel{
    width: 90vw;
    max-width: 520px;
    border-radius: 0.75rem;
    background: #fff;
    box-shadow: 0 20px 40px rgba(0,0,0,.18);
    border: 1px solid rgba(0,0,0,.06);
    padding: 1.25rem;
    position: relative;
  }

  .cc-btn-row{
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: .5rem;
  }

  .cc-dim{
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,.4);
  }
</style>

<div
  x-data="{
    open:false,
    showSettings:false,
    categories:{ analytics:false, marketing:false, functional:false },

    // viewport state
    isDesktop:false,
    syncViewport(){
      this.isDesktop = (window.innerWidth || 0) >= 768;
    },

    init(){
      this.syncViewport();

      const saved = this.getConsent();
      if (!saved) { this.open = true; return; }
      this.dispatch(saved);
    },

    acceptAll(){
      this.categories = { analytics:true, marketing:true, functional:true };
      this.save(true);
    },

    rejectAll(){
      this.categories = { analytics:false, marketing:false, functional:false };
      this.save(false);
    },

    save(explicitAccepted = null){
      const consent = {
        accepted: explicitAccepted ?? (this.categories.analytics || this.categories.marketing || this.categories.functional),
        categories: { ...this.categories },
        updatedAt: new Date().toISOString()
      };
      this.setConsent(consent);
      this.dispatch(consent);
      this.showSettings = false;
      this.open = false;
    },

    dispatch(consent){
      window.dispatchEvent(new CustomEvent('cookie-consent', { detail: consent }));
      window.dataLayer = window.dataLayer || [];
      window.dataLayer.push({ event: 'cookie_consent_update', consent });
    },

    getConsent(){
      try { return JSON.parse(sessionStorage.getItem('apw_cc') || 'null'); }
      catch (_) { return null; }
    },
    setConsent(obj){
      sessionStorage.setItem('apw_cc', JSON.stringify(obj));
    },
  }"
  x-init="init()"
  @resize.window.debounce.150ms="syncViewport()"
  x-cloak
  class="cc-root"
  aria-live="polite"
>

  {{-- ===== MOBILE (<= md) — card ===== --}}
  <div
    x-show="open && !isDesktop"
    x-transition
    class="cc-mobile cc-mobile-popup"
    role="dialog"
    aria-modal="true"
    aria-labelledby="cc-mobile-title"
  >
    <div class="rounded-xl border border-gray-200 bg-white/95 backdrop-blur shadow-2xl cc-desktop-card">
      <div class="p-4">
        <p id="cc-mobile-title" class="text-xs font-semibold tracking-widest text-[#CF4520]">
          PRIVACY NOTICE
        </p>
        <p class="mt-1 text-sm leading-5 text-gray-700">
          We use cookies to improve your experience. See our
          <a href="{{ $privacyUrl }}" class="underline font-medium hover:text-[#F05323]">Privacy Policy</a>.
        </p>

        <div class="mt-3 cc-btn-row">
          <button @click="showSettings = true"
                  class="rounded-lg px-0 py-2 text-sm font-semibold ring-1 ring-gray-300 hover:bg-gray-50">
            Settings
          </button>
          <button @click="rejectAll"
                  class="rounded-lg px-0 py-2 text-sm font-semibold bg-gray-100 hover:bg-gray-200">
            Reject
          </button>
          <button @click="acceptAll"
                  class="rounded-lg px-0 py-2 text-sm font-semibold text-white bg-[#CF4520] hover:brightness-110">
            Accept
          </button>
        </div>
      </div>
    </div>
  </div>

  {{-- ===== MOBILE Settings Modal ===== --}}
  <div
    x-show="showSettings && !isDesktop"
    x-transition.opacity
    x-cloak
    class="cc-mobile cc-modal"
    @keydown.escape.window="showSettings = false"
  >
    <div class="cc-dim" @click="showSettings = false"></div>

    <div x-show="showSettings && !isDesktop" x-transition
         role="dialog" aria-modal="true" aria-labelledby="cookie-settings-title-mobile"
         class="cc-modal-panel">
      <button type="button"
              @click="showSettings = false"
              class="absolute top-2.5 right-2.5 inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#CF4520]"
              aria-label="Close cookie settings">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 011.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
        </svg>
      </button>

      <h2 id="cookie-settings-title-mobile" class="text-sm font-semibold text-gray-900">Cookie Settings</h2>
      <p class="mt-1 text-[13px] text-gray-600">Essential cookies are required and can’t be turned off.</p>

      <div class="mt-4 space-y-3">
        <div class="flex items-start justify-between gap-3 rounded-lg border p-3">
          <div>
            <div class="text-[13px] font-medium text-gray-900">Essential</div>
            <div class="text-[12px] text-gray-600">Required for basic site functionality.</div>
          </div>
          <div class="text-[11px] font-semibold px-2 py-0.5 rounded bg-gray-100 text-gray-700">ON</div>
        </div>

        <div class="flex items-start justify-between gap-3 rounded-lg border p-3">
          <div>
            <div class="text-[13px] font-medium text-gray-900">Functional</div>
            <div class="text-[12px] text-gray-600">Remembers preferences and enhances features.</div>
          </div>
          <label class="inline-flex items-center cursor-pointer">
            <input type="checkbox" class="sr-only peer" x-model="categories.functional">
            <span class="relative w-10 h-5 rounded-full bg-gray-200 transition peer-checked:bg-[#CF4520]
                          after:content-[''] after:absolute after:top-0.5 after:left-0.5
                          after:w-4 after:h-4 after:bg-white after:rounded-full after:shadow after:transition
                          peer-checked:after:translate-x-5"></span>
          </label>
        </div>

        <div class="flex items-start justify-between gap-3 rounded-lg border p-3">
          <div>
            <div class="text-[13px] font-medium text-gray-900">Analytics</div>
            <div class="text-[12px] text-gray-600">Helps us understand site usage.</div>
          </div>
          <label class="inline-flex items-center cursor-pointer">
            <input type="checkbox" class="sr-only peer" x-model="categories.analytics">
            <span class="relative w-10 h-5 rounded-full bg-gray-200 transition peer-checked:bg-[#CF4520]
                          after:content-[''] after:absolute after:top-0.5 after:left-0.5
                          after:w-4 after:h-4 after:bg-white after:rounded-full after:shadow after:transition
                          peer-checked:after:translate-x-5"></span>
          </label>
        </div>

        <div class="flex items-start justify-between gap-3 rounded-lg border p-3">
          <div>
            <div class="text-[13px] font-medium text-gray-900">Marketing</div>
            <div class="text-[12px] text-gray-600">Personalized content and ads.</div>
          </div>
          <label class="inline-flex items-center cursor-pointer">
            <input type="checkbox" class="sr-only peer" x-model="categories.marketing">
            <span class="relative w-10 h-5 rounded-full bg-gray-200 transition peer-checked:bg-[#CF4520]
                          after:content-[''] after:absolute after:top-0.5 after:left-0.5
                          after:w-4 after:h-4 after:bg-white after:rounded-full after:shadow after:transition
                          peer-checked:after:translate-x-5"></span>
          </label>
        </div>
      </div>

      <div class="mt-5 flex flex-wrap justify-end gap-2">
        <button @click="rejectAll"
                class="rounded-lg px-3 py-1.5 text-[13px] font-semibold bg-gray-100 hover:bg-gray-200">
          Reject All
        </button>
        <button @click="acceptAll"
                class="rounded-lg px-4 py-2 text-[13px] font-semibold text-white bg-[#CF4520] hover:brightness-110">
          Accept All
        </button>
        <button @click="save()"
                class="rounded-lg px-3 py-1.5 text-[13px] font-semibold ring-1 ring-gray-300 hover:bg-gray-50">
          Save Preferences
        </button>
      </div>
    </div>
  </div>

  {{-- ===== DESKTOP (>= md) — card ===== --}}
  <div
    x-show="open && isDesktop"
    x-transition
    class="cc-desktop cc-desktop-wrap"
    role="dialog"
    aria-modal="true"
    aria-labelledby="cc-desktop-title"
  >
    <div class="cc-desktop-card w-auto rounded-xl border border-gray-200 bg-white/95 backdrop-blur shadow-xl">
      <div class="p-4" style="width: 28rem; max-width: 92vw;">
        <p id="cc-desktop-title" class="text-[11px] lg:text-xs font-semibold tracking-widest text-[#CF4520]">
          PRIVACY NOTICE
        </p>
        <p class="mt-1 text-[13px] lg:text-sm leading-5 text-gray-700">
          We use cookies to improve your experience. See our
          <a href="{{ $privacyUrl }}" class="underline font-medium hover:text-[#F05323]">Privacy Policy</a>.
        </p>

        <div class="mt-3 cc-btn-row">
          <button @click="showSettings = true"
                  class="rounded-lg px-0 py-2 text-[13px] lg:text-sm font-semibold ring-1 ring-gray-300 hover:bg-gray-50">
            Settings
          </button>
          <button @click="rejectAll"
                  class="rounded-lg px-0 py-2 text-[13px] lg:text-sm font-semibold bg-gray-100 hover:bg-gray-200">
            Reject
          </button>
          <button @click="acceptAll"
                  class="rounded-lg px-0 py-2 text-[13px] lg:text-sm font-semibold text-white bg-[#CF4520] hover:brightness-110">
            Accept
          </button>
        </div>
      </div>
    </div>
  </div>

  {{-- ===== DESKTOP Settings Modal ===== --}}
  <div
    x-show="showSettings && isDesktop"
    x-transition.opacity
    x-cloak
    class="cc-desktop cc-modal"
    @keydown.escape.window="showSettings = false"
  >
    <div class="cc-dim" @click="showSettings = false"></div>

    <div x-show="showSettings && isDesktop" x-transition
         role="dialog" aria-modal="true" aria-labelledby="cookie-settings-title"
         class="cc-modal-panel">
      <button type="button"
              @click="showSettings = false"
              class="absolute top-2.5 right-2.5 inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#CF4520]"
              aria-label="Close cookie settings">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 011.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
        </svg>
      </button>

      <h2 id="cookie-settings-title" class="text-sm font-semibold text-gray-900">Cookie Settings</h2>
      <p class="mt-1 text-[13px] text-gray-600">Essential cookies are required and can’t be turned off.</p>

      <div class="mt-4 space-y-3">
        <div class="flex items-start justify-between gap-3 rounded-lg border p-3">
          <div>
            <div class="text-[13px] font-medium text-gray-900">Essential</div>
            <div class="text-[12px] text-gray-600">Required for basic site functionality.</div>
          </div>
          <div class="text-[11px] font-semibold px-2 py-0.5 rounded bg-gray-100 text-gray-700">ON</div>
        </div>

        <div class="flex items-start justify-between gap-3 rounded-lg border p-3">
          <div>
            <div class="text-[13px] font-medium text-gray-900">Functional</div>
            <div class="text-[12px] text-gray-600">Remembers preferences and enhances features.</div>
          </div>
          <label class="inline-flex items-center cursor-pointer">
            <input type="checkbox" class="sr-only peer" x-model="categories.functional">
            <span class="relative w-10 h-5 rounded-full bg-gray-200 transition peer-checked:bg-[#CF4520]
                          after:content-[''] after:absolute after:top-0.5 after:left-0.5
                          after:w-4 after:h-4 after:bg-white after:rounded-full after:shadow after:transition
                          peer-checked:after:translate-x-5"></span>
          </label>
        </div>

        <div class="flex items-start justify-between gap-3 rounded-lg border p-3">
          <div>
            <div class="text-[13px] font-medium text-gray-900">Analytics</div>
            <div class="text-[12px] text-gray-600">Helps us understand site usage.</div>
          </div>
          <label class="inline-flex items-center cursor-pointer">
            <input type="checkbox" class="sr-only peer" x-model="categories.analytics">
            <span class="relative w-10 h-5 rounded-full bg-gray-200 transition peer-checked:bg-[#CF4520]
                          after:content-[''] after:absolute after:top-0.5 after:left-0.5
                          after:w-4 after:h-4 after:bg-white after:rounded-full after:shadow after:transition
                          peer-checked:after:translate-x-5"></span>
          </label>
        </div>

        <div class="flex items-start justify-between gap-3 rounded-lg border p-3">
          <div>
            <div class="text-[13px] font-medium text-gray-900">Marketing</div>
            <div class="text-[12px] text-gray-600">Personalized content and ads.</div>
          </div>
          <label class="inline-flex items-center cursor-pointer">
            <input type="checkbox" class="sr-only peer" x-model="categories.marketing">
            <span class="relative w-10 h-5 rounded-full bg-gray-200 transition peer-checked:bg-[#CF4520]
                          after:content-[''] after:absolute after:top-0.5 after:left-0.5
                          after:w-4 after:h-4 after:bg-white after:rounded-full after:shadow after:transition
                          peer-checked:after:translate-x-5"></span>
          </label>
        </div>
      </div>

      <div class="mt-5 flex flex-wrap justify-end gap-2">
        <button @click="rejectAll"
                class="rounded-lg px-3 py-1.5 text-[13px] font-semibold bg-gray-100 hover:bg-gray-200">
          Reject All
        </button>
        <button @click="acceptAll"
                class="rounded-lg px-4 py-2 text-[13px] font-semibold text-white bg-[#CF4520] hover:brightness-110">
          Accept All
        </button>
        <button @click="save()"
                class="rounded-lg px-3 py-1.5 text-[13px] font-semibold ring-1 ring-gray-300 hover:bg-gray-50">
          Save Preferences
        </button>
      </div>
    </div>
  </div>

</div>
