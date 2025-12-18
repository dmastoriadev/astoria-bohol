@extends('layouts.app')
@section('title','Reset Password')

@push('head')
<meta name="robots" content="noindex, nofollow">
@endpush

@section('hide_footer') @endsection

@section('content')
<section class="relative min-h-screen flex items-center justify-center pt-24 lg:pt-28">
  <div class="fixed inset-0 -z-10">
    <img src="{{ asset('images/login.webp') }}" alt="" class="w-full h-full object-cover">
    <div class="absolute inset-0 bg-black/40"></div>
  </div>

  <div class="w-full max-w-md px-4">
    <div class="rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 shadow-2xl p-6 sm:p-8 text-white">
      <div class="mb-6 text-center">
        <img src="{{ asset('images/ac3-white.webp') }}" alt="AVLCI" class="mx-auto h-8 mb-4">
        <h1 class="text-2xl font-bold tracking-tight">Create a new password</h1>
        <p class="text-white/80 text-sm mt-1">Choose a strong password you don’t use elsewhere.</p>
      </div>

      @if ($errors->any())
        <div class="mb-4 text-sm bg-red-600/20 border border-red-300/30 text-red-100 px-3 py-2 rounded">
          {{ $errors->first() }}
        </div>
      @endif

      <form method="POST" action="{{ route('password.update') }}" class="space-y-5 notranslate" translate="no">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

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
              value="{{ old('email', $email ?? '') }}"
              required
              autocomplete="email"
              class="w-full rounded-lg bg-white/15 border border-white/20 text-white placeholder-white/60
                     pl-10 pr-3 py-2 outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/30"
              placeholder="you@domain.com"
            >
          </div>
        </div>

        <div>
          <label class="block text-sm mb-1 text-white/90" for="password">New Password</label>
          <div class="relative">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <i class="fa-solid fa-lock text-white/60 text-sm"></i>
            </span>
            <input
              id="password"
              name="password"
              type="password"
              required
              autocomplete="new-password"
              class="w-full rounded-lg bg-white/15 border border-white/20 text-white placeholder-white/60
                     pl-10 pr-3 py-2 outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/30"
              placeholder="••••••••"
            >
          </div>
          <p class="mt-1 text-xs text-white/70">Minimum 8 characters; mix of letters, numbers, and symbols recommended.</p>
        </div>

        <div>
          <label class="block text-sm mb-1 text-white/90" for="password_confirmation">Confirm Password</label>
          <div class="relative">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <i class="fa-solid fa-lock text-white/60 text-sm"></i>
            </span>
            <input
              id="password_confirmation"
              name="password_confirmation"
              type="password"
              required
              autocomplete="new-password"
              class="w-full rounded-lg bg-white/15 border border-white/20 text-white placeholder-white/60
                     pl-10 pr-3 py-2 outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/30"
              placeholder="••••••••"
            >
          </div>
        </div>

        <button
          class="w-full rounded-lg bg-[#04b2e2] hover:bg-[#18206b] active:bg-[#18206b]
                 transition-colors py-2.5 font-semibold shadow-lg shadow-emerald-900/20 focus:outline-none
                 focus:ring-2 focus:ring-offset-2 focus:ring-emerald-400 focus:ring-offset-emerald-900/0"
          type="submit"
        >
          Reset Password
        </button>

        <div class="text-center text-sm mt-2">
          <a href="{{ route('login') }}" class="text-white/80 hover:text-white underline underline-offset-4">Back to sign in</a>
        </div>
      </form>
    </div>
  </div>
</section>
@endsection
