{{-- resources/views/forms/unsubscribe.blade.php --}}
@extends('layouts.app')

@section('title', 'Unsubscribe — AVLCI')

@push('head')
  {{-- reCAPTCHA v3 --}}
  <script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site') }}"></script>
@endpush

@section('content')

{{-- HERO HEADER --}}
<section aria-labelledby="unsubscribe-title"
  class="relative text-white bg-gradient-to-r from-green-700 to-emerald-500 h-[500px] md:h-[500px] lg:h-[600px]">
  {{-- subtle highlights --}}
  <div class="pointer-events-none absolute inset-0 opacity-70"
       style="background-image:
         radial-gradient(80% 60% at 85% 10%, rgba(255,255,255,.12) 0%, transparent 60%),
         radial-gradient(70% 50% at 10% 90%, rgba(255,255,255,.08) 0%, transparent 55%);">
  </div>

  <div class="relative h-full">
    <div class="max-w-7xl mx-auto h-full px-6 flex items-center">
      <div class="max-w-3xl translate-y-1 transition-transform duration-700 ease-out">
        <h1 id="unsubscribe-title" class="text-4xl md:text-5xl font-bold tracking-tight">
          Unsubscribe
        </h1>
        <p class="mt-4 text-white/90 text-base md:text-lg leading-relaxed">
          Want to be notified less? Unsubscribe from our marketing emails. You’ll still receive
          essential messages like reservation details, account security updates, and program changes.
        </p>
      </div>
    </div>
  </div>
</section>

{{-- FORM SECTION --}}
<section class="w-full bg-white mt-5 pb-16">
  <div class="max-w-3xl mx-auto px-6">
    <div class="rounded-2xl shadow-xl border border-gray-100 bg-white p-8">

      {{-- Success toast --}}
      @if(session('ok'))
        <div x-data="{show:true}" x-init="setTimeout(()=>show=false,6000)" x-show="show"
             class="mb-6 rounded-xl border border-green-200 bg-green-50 p-4 text-green-800">
          {{ session('ok') }}
        </div>
      @endif

      {{-- General error (e.g., SendGrid failure) --}}
      @error('general')
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-red-800">
          {{ $message }}
        </div>
      @enderror

      <form
        id="unsubscribe-form"
        x-data="{ sending:false }"
        x-on:submit="sending = true"
        method="POST"
        action="{{ route('unsubscribe.store') }}"
        class="space-y-8"
        novalidate
      >
        @csrf

        {{-- reCAPTCHA v3 token --}}
        <input type="hidden" name="g-recaptcha-response" id="recaptcha_token_unsub">

        {{-- Honeypot --}}
        <input type="text" name="website" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">

        {{-- Name / Email / Mobile --}}
        <div class="grid md:grid-cols-2 gap-6">
          <div>
            <label for="full_name" class="block text-sm font-medium text-gray-700">
              Full name <span class="text-red-500">*</span>
            </label>
            <div class="relative mt-1">
              <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                {{-- user icon --}}
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M15.75 7.5a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 19.5a7.5 7.5 0 0 1 15 0" />
                </svg>
              </span>
              <input
                id="full_name"
                type="text"
                name="full_name"
                value="{{ old('full_name') }}"
                required
                autocomplete="name"
                @class([
                  'w-full rounded-xl pl-10 pr-3 py-2 border focus:outline-none focus:ring-2',
                  'border-gray-300 focus:border-[#1A8700] focus:ring-[#1A8700]',
                  'border-red-300 focus:border-red-400 focus:ring-red-300' => $errors->has('full_name'),
                ])
                aria-invalid="{{ $errors->has('full_name') ? 'true' : 'false' }}"
                aria-describedby="{{ $errors->has('full_name') ? 'full_name_error' : '' }}"
              />
            </div>
            @error('full_name') <p id="full_name_error" class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
          </div>

          <div>
            <label for="email" class="block text-sm font-medium text-gray-700">
              Email <span class="text-red-500">*</span>
            </label>
            <div class="relative mt-1">
              <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                {{-- mail icon --}}
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M3 8.25l7.5 4.5L18 8.25M3.75 6h16.5A1.75 1.75 0 0 1 22 7.75v8.5A1.75 1.75 0 0 1 20.25 18H3.75A1.75 1.75 0 0 1 2 16.25v-8.5A1.75 1.75 0 0 1 3.75 6z" />
                </svg>
              </span>
              <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autocomplete="email"
                @class([
                  'w-full rounded-xl pl-10 pr-3 py-2 border focus:outline-none focus:ring-2',
                  'border-gray-300 focus:border-[#1A8700] focus:ring-[#1A8700]',
                  'border-red-300 focus:border-red-400 focus:ring-red-300' => $errors->has('email'),
                ])
                aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                aria-describedby="{{ $errors->has('email') ? 'email_error' : '' }}"
              />
            </div>
            @error('email') <p id="email_error" class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
          </div>

          <div class="md:col-span-2">
            <label for="mobile" class="block text-sm font-medium text-gray-700">Mobile (optional)</label>
            <div class="relative mt-1">
              <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                {{-- phone icon --}}
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M2.25 6.75A2.25 2.25 0 0 1 4.5 4.5h3.04c.9 0 1.68.6 1.9 1.46l.73 2.9a2 2 0 0 1-.52 1.95l-1.2 1.2a16.5 16.5 0 0 0 6.97 6.97l1.2-1.2a2 2 0 0 1 1.95-.52l2.9.73c.86.22 1.46 1 1.46 1.9V19.5a2.25 2.25 0 0 1-2.25 2.25H19.5C10.3 21.75 2.25 13.7 2.25 4.5V6.75z" />
                </svg>
              </span>
              <input
                id="mobile"
                type="text"
                name="mobile"
                value="{{ old('mobile') }}"
                autocomplete="tel"
                class="w-full rounded-xl pl-10 pr-3 py-2 border border-gray-300 focus:outline-none focus:ring-2 focus:border-[#1A8700] focus:ring-[#1A8700]"
              />
            </div>
            @error('mobile') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
          </div>
        </div>

        {{-- Reasons (chips) --}}
        <fieldset>
          <legend class="block text-sm font-medium text-gray-700 mb-3">Please tell us why (optional)</legend>

          <div class="grid sm:grid-cols-2 gap-3">
            @php
              $opts = [
                'Too many emails',
                'Content not relevant',
                'Prefer SMS updates',
                'Prefer WhatsApp updates',
                'Prefer Viber updates',
                'Wrong person / not a member',
                'I never subscribed',
                'Other (specify below)',
              ];
              $oldReasons = collect(old('reasons', []));
            @endphp

            @foreach($opts as $i => $label)
              @php $id = 'r'.($i+1); @endphp
              <label for="{{ $id }}" class="cursor-pointer">
                <input
                  id="{{ $id }}"
                  type="checkbox"
                  name="reasons[]"
                  value="{{ $label }}"
                  @checked($oldReasons->contains($label))
                  class="peer sr-only"
                >
                <span class="block rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-800
                             hover:bg-gray-50 hover:border-gray-300
                             peer-checked:border-[#1A8700] peer-checked:bg-[#1A8700]/5 peer-checked:shadow-inner">
                  {{ $label }}
                </span>
              </label>
            @endforeach
          </div>

          @error('reasons') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </fieldset>

        {{-- Message --}}
        <div>
          <div class="flex items-center justify-between">
            <label for="message" class="block text-sm font-medium text-gray-700">Message (optional)</label>
            <span class="text-xs text-gray-400">You can add details here</span>
          </div>
          <textarea
            id="message"
            name="message"
            rows="4"
            class="mt-1 w-full rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:border-[#1A8700] focus:ring-[#1A8700]"
          >{{ old('message') }}</textarea>
          @error('message') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        {{-- Confirm --}}
        <div class="flex items-start gap-3">
          <input
            id="agree"
            type="checkbox"
            name="agree"
            value="1"
            class="mt-1 h-4 w-4 rounded border-gray-300 text-[#1A8700] focus:ring-[#1A8700]"
            required
          >
          <label for="agree" class="text-sm text-gray-700">
            I confirm I want to unsubscribe from AVLCI marketing emails. I understand essential service emails may still be sent.
          </label>
        </div>
        @error('agree') <p class="-mt-2 text-sm text-red-600">{{ $message }}</p> @enderror

        {{-- Submit --}}
        <div class="pt-2">
         <button type="submit" :disabled="sending"
          class="inline-flex items-center gap-2 rounded-2xl bg-[#1A8700] px-5 py-3 text-white font-semibold shadow hover:brightness-110 disabled:opacity-60">

          <i x-show="!sending" class="fa-solid fa-paper-plane text-[18px]" aria-hidden="true"></i>
          <i x-show="sending" class="fa-solid fa-spinner fa-spin text-[18px]" aria-hidden="true"></i>

          <span x-text="sending ? 'Sending…' : 'Submit'">Submit</span>
        </button>

        </div>

        {{-- reCAPTCHA badge slot + required attribution --}}
        <div id="recaptcha-badge-slot" class="flex justify-center mt-3"></div>
        <p class="text-xs text-gray-500 mt-1 text-center">
          This site is protected by reCAPTCHA and the Google
          <a class="underline hover:no-underline" href="https://policies.google.com/privacy" target="_blank" rel="noopener">Privacy Policy</a>
          and
          <a class="underline hover:no-underline" href="https://policies.google.com/terms" target="_blank" rel="noopener">Terms of Service</a>
          apply.
        </p>

      </form>
    </div>
  </div>
</section>

@endsection

@push('scripts')
<script>
(function () {
  const siteKey = "{{ config('services.recaptcha.site') }}";
  const form    = document.getElementById('unsubscribe-form');
  const tokenEl = document.getElementById('recaptcha_token_unsub');
  const badgeSlot = document.getElementById('recaptcha-badge-slot');

  if (!form) return;

  // Show badge ASAP
  function mountBadge() {
    if (!window.grecaptcha || !siteKey) return;
    grecaptcha.ready(function () {
      grecaptcha.execute(siteKey, { action: 'pageview' }).then(moveBadgeIfReady);
    });
  }
  function moveBadgeIfReady() {
    const badge = document.querySelector('.grecaptcha-badge');
    if (!badge || !badgeSlot || badgeSlot.contains(badge)) return;
    badge.style.position = 'static';
    badge.style.right = 'auto';
    badge.style.bottom = 'auto';
    badge.style.boxShadow = 'none';
    badge.style.margin = '0 auto';
    badgeSlot.appendChild(badge);
  }
  window.addEventListener('load', mountBadge);
  new MutationObserver(moveBadgeIfReady).observe(document.body, { childList: true, subtree: true });

  // Token on submit
  form.addEventListener('submit', function (e) {
    if (tokenEl && tokenEl.value) return; // already set

    e.preventDefault();
    if (!window.grecaptcha || !siteKey) { form.submit(); return; }

    grecaptcha.ready(function () {
      grecaptcha.execute(siteKey, { action: 'unsubscribe' })
        .then(function (token) {
          if (tokenEl) tokenEl.value = token;
          form.submit();
        })
        .catch(function () { form.submit(); });
    });
  });
})();
</script>
@endpush
