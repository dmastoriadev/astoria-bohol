{{-- resources/views/forms/access-forms.blade.php --}}
@extends('layouts.app')

@section('title', 'Access Form — Astoria Current')

@push('head')
  {{-- reCAPTCHA v3 --}}
  <script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site') }}"></script>
@endpush

@section('content')

{{-- HERO --}}
<section aria-labelledby="access-title"
  class="relative text-white bg-gradient-to-r from-green-700 to-emerald-500 h-[500px] md:h-[500px] lg:h-[600px]">
  <div class="pointer-events-none absolute inset-0 opacity-70"
       style="background-image:
         radial-gradient(80% 60% at 85% 10%, rgba(255,255,255,.12) 0%, transparent 60%),
         radial-gradient(70% 50% at 10% 90%, rgba(255,255,255,.08) 0%, transparent 55%);">
  </div>
  <div class="relative h-full">
    <div class="max-w-7xl mx-auto h-full px-6 flex items-center">
      <div class="max-w-3xl">
        <h1 id="access-title" class="text-4xl md:text-5xl font-bold tracking-tight">Access Form</h1>
        <p class="mt-4 text-white/90 text-base md:text-lg leading-relaxed">
          AVLCI acknowledges and respects our members’ privacy. Submit your request securely using this form.
          For your protection, we only grant requests for the personal data associated with the email address
          and/or agreement number you identify, and we may need to verify your identity before fulfilling certain requests.
        </p>
      </div>
    </div>
  </div>
</section>

{{-- FORM CARD --}}
<section class="w-full bg-white mt-5 pb-16">
  <div class="max-w-3xl mx-auto px-6">
    <div class="rounded-2xl shadow-xl border border-gray-100 bg-white p-8 space-y-8">

      {{-- Success --}}
      @if(session('ok'))
        <div x-data="{show:true}" x-init="setTimeout(()=>show=false,6000)" x-show="show"
             class="rounded-xl border border-green-200 bg-green-50 p-4 text-green-800">
          {{ session('ok') }}
        </div>
      @endif

      {{-- General error --}}
      @error('general')
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-red-800">
          {{ $message }}
        </div>
      @enderror

      <form id="access-form"
            x-data="{ sending:false }" x-on:submit="sending = true"
            method="POST" action="{{ route('access-form.store') }}" enctype="multipart/form-data" class="space-y-8">
        @csrf

        {{-- reCAPTCHA v3 token --}}
        <input type="hidden" name="g-recaptcha-response" id="recaptcha_token_access">

        {{-- Honeypot --}}
        <input type="text" name="website" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">

        {{-- Personal info --}}
        <div class="grid md:grid-cols-2 gap-6">
          <div>
            <label for="full_name" class="block text-sm font-medium text-gray-700">Full name <span class="text-red-500">*</span></label>
            <input id="full_name" type="text" name="full_name" value="{{ old('full_name') }}" required
                   class="mt-1 w-full rounded-xl border-gray-300 focus:border-[#1A8700] focus:ring-[#1A8700]" />
            @error('full_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
          </div>
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email <span class="text-red-500">*</span></label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required
                   class="mt-1 w-full rounded-xl border-gray-300 focus:border-[#1A8700] focus:ring-[#1A8700]" />
            @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
          </div>
          <div>
            <label for="mobile" class="block text-sm font-medium text-gray-700">Mobile (optional)</label>
            <input id="mobile" type="text" name="mobile" value="{{ old('mobile') }}"
                   class="mt-1 w-full rounded-xl border-gray-300 focus:border-[#1A8700] focus:ring-[#1A8700]" />
            @error('mobile') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
          </div>
          <div>
            <label for="agreement_number" class="block text-sm font-medium text-gray-700">Agreement # (optional)</label>
            <input id="agreement_number" type="text" name="agreement_number" value="{{ old('agreement_number') }}"
                   class="mt-1 w-full rounded-xl border-gray-300 focus:border-[#1A8700] focus:ring-[#1A8700]" />
            @error('agreement_number') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
          </div>
        </div>

        {{-- Request types (chips) --}}
        <fieldset>
          <legend class="block text-sm font-medium text-gray-700 mb-3">What would you like to request? <span class="text-red-500">*</span></legend>
          @php
            $opts = [
              'Access my data',
              'Rectify/Update my data',
              'Delete my data (erasure)',
              'Restrict processing',
              'Object to processing',
              'Data portability (copy/transfer)',
              'Marketing preferences (unsubscribe)',
              'Other',
            ];
            $oldRequests = collect(old('requests', []));
          @endphp
          <div class="grid sm:grid-cols-2 gap-3">
            @foreach($opts as $i => $label)
              @php $id = 'rq'.($i+1); @endphp
              <label for="{{ $id }}" class="cursor-pointer">
                <input id="{{ $id }}" type="checkbox" name="requests[]" value="{{ $label }}"
                       @checked($oldRequests->contains($label)) class="peer sr-only">
                <span class="block rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-800
                             hover:bg-gray-50 hover:border-gray-300
                             peer-checked:border-[#1A8700] peer-checked:bg-[#1A8700]/5 peer-checked:shadow-inner">
                  {{ $label }}
                </span>
              </label>
            @endforeach
          </div>
          @error('requests') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </fieldset>

        {{-- Details --}}
        <div>
          <div class="flex items-center justify-between">
            <label for="description" class="block text-sm font-medium text-gray-700">
              Details of your request <span class="text-red-500">*</span>
            </label>
            <span class="text-xs text-gray-400">Max 4,000 characters</span>
          </div>
          <textarea id="description" name="description" rows="5"
                    class="mt-1 w-full rounded-xl border border-gray-300 focus:border-[#1A8700] focus:ring-[#1A8700]">{{ old('description') }}</textarea>
          @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        {{-- Preferred contact --}}
        <div>
          <span class="block text-sm font-medium text-gray-700 mb-2">Preferred contact method <span class="text-red-500">*</span></span>
          <div class="flex flex-wrap gap-3">
            <label class="inline-flex items-center gap-2 cursor-pointer">
              <input type="radio" name="prefer_contact" value="email" @checked(old('prefer_contact','email')==='email')
                     class="h-4 w-4 text-[#1A8700] border-gray-300 focus:ring-[#1A8700]">
              <span class="text-sm text-gray-800">Email</span>
            </label>
            <label class="inline-flex items-center gap-2 cursor-pointer">
              <input type="radio" name="prefer_contact" value="phone" @checked(old('prefer_contact')==='phone')
                     class="h-4 w-4 text-[#1A8700] border-gray-300 focus:ring-[#1A8700]">
              <span class="text-sm text-gray-800">Phone</span>
            </label>
          </div>
          @error('prefer_contact') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        {{-- Uploads --}}
        <div class="grid md:grid-cols-2 gap-6">
          <div>
            <label for="id_document" class="block text-sm font-medium text-gray-700">
              Proof of identity (optional)
            </label>
            <input id="id_document" type="file" name="id_document"
                   accept=".jpg,.jpeg,.png,.pdf,.doc,.docx"
                   class="mt-1 block w-full text-sm text-gray-700 file:mr-4 file:py-2.5 file:px-4
                          file:rounded-xl file:border-0 file:text-sm file:font-semibold
                          file:bg-gray-100 hover:file:bg-gray-200" />
            <p class="mt-1 text-xs text-gray-500">Accepted: JPG, PNG, PDF (max 5MB)</p>
            @error('id_document') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
          </div>

          <div>
            <label for="supporting_files" class="block text-sm font-medium text-gray-700">
              Supporting documents (optional)
            </label>
            <input id="supporting_files" type="file" name="supporting_files[]" multiple
                   accept=".jpg,.jpeg,.png,.pdf,.doc,.docx"
                   class="mt-1 block w-full text-sm text-gray-700 file:mr-4 file:py-2.5 file:px-4
                          file:rounded-xl file:border-0 file:text-sm file:font-semibold
                          file:bg-gray-100 hover:file:bg-gray-200" />
            <p class="mt-1 text-xs text-gray-500">Up to 5 files, 5MB each</p>
            @error('supporting_files') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            @error('supporting_files.*') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
          </div>
        </div>

        {{-- Consent --}}
        <div class="flex items-start gap-3">
          <input id="consent" type="checkbox" name="consent" value="1"
                 class="mt-1 h-4 w-4 rounded border-gray-300 text-[#1A8700] focus:ring-[#1A8700]" required>
          <label for="consent" class="text-sm text-gray-700">
            I declare that the information I’ve provided is true and correct. I understand AVLCI may need to verify my identity
            and will respond as soon as reasonably practicable and consistent with applicable law.
          </label>
        </div>
        @error('consent') <p class="-mt-2 text-sm text-red-600">{{ $message }}</p> @enderror

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

        <p class="text-xs text-gray-500">
          Notes: For your protection, we only grant requests associated with the email and/or agreement number you provide.
          Identity verification may be required before fulfilling certain requests.
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
  const form    = document.getElementById('access-form');
  const tokenEl = document.getElementById('recaptcha_token_access');
  const badgeSlot = document.getElementById('recaptcha-badge-slot');

  if (!form) return;

  // Show the badge ASAP
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
    // already have a token (double-click)? let it pass
    if (tokenEl && tokenEl.value) return;

    e.preventDefault();
    if (!window.grecaptcha || !siteKey) { form.submit(); return; }

    grecaptcha.ready(function () {
      grecaptcha.execute(siteKey, { action: 'access_form' })
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
