@extends('layouts.app')

@section('title','Admin Login')

@push('head')
<meta name="robots" content="noindex, nofollow">
{{-- reCAPTCHA v3 (load ONCE and use the public "site" key) --}}
<script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site') }}"></script>
@endpush

{{-- Hide the default footer on this page --}}
@section('hide_footer') @endsection

@section('content')
<section class="relative min-h-screen flex items-center justify-center pt-24 lg:pt-28">
  {{-- Background image + overlay --}}
  <div class="fixed inset-0 -z-10">
    <img src="{{ asset('images/login.webp') }}" alt="" class="w-full h-full object-cover" loading="eager" fetchpriority="high">
    <div class="absolute inset-0 bg-black/40"></div>
  </div>

  {{-- Card --}}
  <div class="w-full max-w-md px-4">
    <div x-data="{ showPassword: false }" class="rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 shadow-2xl p-6 sm:p-8 text-white">
      <div class="mb-6 text-center">
        <img src="{{ asset('images/abh-logo-white.webp') }}" alt="ABH" class="mx-auto h-6 mb-4">
        <h1 class="text-2xl font-bold tracking-tight">Admin Sign in</h1>
        <p class="text-white/80 text-sm mt-1 notranslate" translate="no">ABH WEB ADMIN</p>
      </div>

      {{-- Status / errors --}}
      @if (session('status'))
        <div class="mb-4 text-sm bg-emerald-600/20 border border-emerald-300/30 text-emerald-100 px-3 py-2 rounded">
          {{ session('status') }}
        </div>
      @endif
      @if ($errors->any())
        <div class="mb-4 text-sm bg-red-600/20 border border-red-300/30 text-red-100 px-3 py-2 rounded">
          {{ $errors->first() }}
        </div>
      @endif

      <form id="admin-login-form" method="POST" action="{{ url('/apz-admin') }}" class="space-y-5 notranslate" translate="no">
        @csrf

        {{-- Required by reCAPTCHA v3 --}}
        <input type="hidden" name="g-recaptcha-response" id="recaptcha_token">

        {{-- Email --}}
        <div>
          <label class="block text-sm mb-1 text-white/90" for="email">Email</label>
          <div class="relative">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <i class="fa-solid fa-envelope text-white/60 text-sm"></i>
            </span>
            <input
              id="email"
              name="email"
              type="email"
              value="{{ old('email') }}"
              required
              autocomplete="username"
              autofocus
              class="w-full rounded-lg bg-white/15 border border-white/20 text-white placeholder-white/60
                     pl-10 pr-3 py-2 outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/30"
              placeholder="you@domain.com"
            >
          </div>
        </div>

        {{-- Password --}}
        <div>
          <label class="block text-sm mb-1 text-white/90" for="password">Password</label>
          <div class="relative">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <i class="fa-solid fa-lock text-white/60 text-sm"></i>
            </span>
            <input
              :type="showPassword ? 'text' : 'password'"
              id="password"
              name="password"
              required
              autocomplete="current-password"
              class="w-full rounded-lg bg-white/15 border border-white/20 text-white placeholder-white/60
                     pl-10 pr-10 py-2 outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/30"
              placeholder="••••••••"
            >
            <button
              type="button"
              @click="showPassword = !showPassword"
              class="absolute inset-y-0 right-0 pr-3 flex items-center text-white/70 hover:text-white"
              aria-label="Toggle password visibility"
            >
              <i :class="showPassword ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'"></i>
            </button>
          </div>
        </div>

        {{-- Remember + Actions --}}
        <div class="flex items-center justify-between text-sm">
          <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="remember" class="rounded bg-white/10 border-white/30 text-emerald-500 focus:ring-emerald-400/50">
            <span class="text-white/90">Remember me</span>
          </label>

          <div class="flex items-center gap-3">
            <a href="{{ route('password.request') }}" class="text-white/80 hover:text-white underline underline-offset-4">
              Forgot password?
            </a>
            <a href="{{ route('home') }}" class="text-white/80 hover:text-white underline underline-offset-4">
              Back to site
            </a>
          </div>
        </div>

        <button
          class="w-full rounded-lg bg-[#CF4520] hover:bg-[#3F2021] active:bg-[#3F2021]
                 transition-colors py-2.5 font-semibold shadow-lg shadow-emerald-900/20 focus:outline-none
                 focus:ring-2 focus:ring-offset-2 focus:ring-emerald-400 focus:ring-offset-emerald-900/0"
          type="submit"
        >
          Sign in
        </button>

        {{-- reCAPTCHA badge slot (we move the official badge here) --}}
        <div id="recaptcha-badge-slot" class="flex justify-center mt-3"></div>

        {{-- Required attribution text --}}
        <p class="text-[11px] leading-snug text-white/70 mt-2 text-center notranslate" translate="no">
          This site is protected by reCAPTCHA and the Google
          <a class="underline hover:no-underline" href="https://policies.google.com/privacy" target="_blank" rel="noopener">Privacy Policy</a>
          and
          <a class="underline hover:no-underline" href="https://policies.google.com/terms" target="_blank" rel="noopener">Terms of Service</a>
          apply.
        </p>

        <p class="text-xs text-white/60 text-center mt-2">Access is restricted to authorized users.</p>
      </form>
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script>
(function () {
  const siteKey    = "{{ config('services.recaptcha.site') }}";
  const form       = document.getElementById('admin-login-form');
  const tokenField = document.getElementById('recaptcha_token');
  const badgeSlot  = document.getElementById('recaptcha-badge-slot');

  if (!form) return;

  // Inject badge early so users can see it
  function mountBadge() {
    if (!window.grecaptcha || !siteKey) return;
    grecaptcha.ready(function () {
      // harmless action; ensures badge appears
      grecaptcha.execute(siteKey, { action: 'pageview' }).then(moveBadgeIfReady);
    });
  }

  // Move the official badge into our slot (keep it visible & unmodified)
  function moveBadgeIfReady() {
    const badge = document.querySelector('.grecaptcha-badge');
    if (!badge || !badgeSlot || badgeSlot.contains(badge)) return;
    badge.style.position = 'static';
    badge.style.right    = 'auto';
    badge.style.bottom   = 'auto';
    badge.style.boxShadow= 'none';
    badge.style.margin   = '0 auto';
    badgeSlot.appendChild(badge);
  }

  window.addEventListener('load', mountBadge);
  new MutationObserver(moveBadgeIfReady).observe(document.body, { childList: true, subtree: true });

  // Get a fresh token on submit
  form.addEventListener('submit', function (e) {
    // If a token is already present (e.g., due to double click), allow submit
    if (tokenField && tokenField.value) return;

    e.preventDefault();

    if (!window.grecaptcha || !siteKey) { form.submit(); return; }

    grecaptcha.ready(function () {
      grecaptcha.execute(siteKey, { action: 'login' })
        .then(function (token) {
          if (tokenField) tokenField.value = token;
          form.submit();
        })
        .catch(function () { form.submit(); });
    });
  });
})();
</script>
@endpush
