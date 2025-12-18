{{-- resources/views/errors/404.blade.php --}}
@extends('layouts.app')

@section('title', '404 — Page Not Found | Astoria Plaza')

@section('content')
<section class="bg-[#CF4520]/5 min-h-[600px] md:min-h-[720px] lg:min-h-[800px] flex items-center">
  <div class="max-w-4xl mx-auto px-6 py-16 md:py-20 lg:py-24 text-center">
    <div class="inline-flex items-center justify-center rounded-full border border-[#CF4520]/40 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-[.22em] text-[#CF4520]">
      Error 404 · Page not found
    </div>

    {{-- Bigger 404, tall hero --}}
    <div class="mt-6 text-[#CF4520] font-extrabold tracking-tight text-7xl md:text-8xl lg:text-9xl leading-none">
      404
    </div>

    <h1 class="mt-4 text-2xl md:text-3xl lg:text-4xl font-extrabold text-[#25282a] leading-tight">
       This page is missing, but your next great stay isn’t.
    </h1>

    <p class="mt-4 text-base md:text-lg text-gray-700 max-w-2xl mx-auto">
      This link may be broken or the page may have been removed.
      Use the buttons below to continue exploring Astoria Bohol.
    </p>

    <p class="mt-3 text-xs text-gray-500 break-all">
      Requested URL:
      <span class="font-mono bg-white border border-gray-200 rounded px-1.5 py-0.5">
        {{ request()->fullUrl() ?: '/(unknown)' }}
      </span>
    </p>

    <div class="mt-6 flex flex-wrap justify-center gap-3">
      @if (Route::has('home'))
        <a href="{{ route('home') }}"
           class="inline-flex items-center gap-2 rounded-xl bg-[#CF4520] px-5 py-2.5 text-sm md:text-base font-semibold text-white shadow-sm hover:bg-[#3F2021] transition">
          <i class="fa-solid fa-house"></i>
          Home
        </a>
      @endif

      @if (Route::has('about-avlci'))
        <a href="{{ route('about-avlci') }}"
           class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm md:text-base font-semibold text-gray-800 hover:bg-gray-50 transition">
          <i class="fa-solid fa-circle-info text-[#CF4520]"></i>
          About AVLCI
        </a>
      @endif

      <a href="{{ url()->previous() }}"
         class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm md:text-base font-semibold text-gray-800 hover:bg-gray-50 transition">
        <i class="fa-solid fa-arrow-left-long text-[#CF4520]"></i>
        Go back
      </a>
    </div>
  </div>
</section>

<section class="bg-white py-10 md:py-14">
  <div class="max-w-5xl mx-auto px-6">
    <div class="rounded-2xl border border-gray-100 bg-white shadow-sm p-6 md:p-8">
      <h2 class="text-lg md:text-xl font-semibold text-gray-900">
        You might be looking for:
      </h2>

      {{-- Use key navigation pages here --}}
      <div class="mt-4 grid gap-3.5 sm:grid-cols-2 md:grid-cols-3 text-[15px] md:text-base">

        {{-- Home --}}
        @if (Route::has('home'))
          <a href="{{ route('home') }}"
             class="group rounded-xl border border-gray-200 p-4 hover:border-[#CF4520] hover:bg-[#CF4520]/5 transition">
            <div class="font-semibold text-gray-900 group-hover:text-[#CF4520]">
              Home
            </div>
            <div class="text-gray-600 mt-1">
              Start from the main page.
            </div>
          </a>
        @endif

        {{-- Promos --}}
        @if (Route::has('promos.index'))
          <a href="{{ route('promos.index') }}"
             class="group rounded-xl border border-gray-200 p-4 hover:border-[#CF4520] hover:bg-[#CF4520]/5 transition">
            <div class="font-semibold text-gray-900 group-hover:text-[#CF4520]">
              Promos &amp; Offers
            </div>
            <div class="text-gray-600 mt-1">
              View the latest membership and resort deals.
            </div>
          </a>
        @elseif (Route::has('promos'))
          <a href="{{ route('promos') }}"
             class="group rounded-xl border border-gray-200 p-4 hover:border-[#CF4520] hover:bg-[#CF4520]/5 transition">
            <div class="font-semibold text-gray-900 group-hover:text-[#CF4520]">
              Promos &amp; Offers
            </div>
            <div class="text-gray-600 mt-1">
              View the latest membership and resort deals.
            </div>
          </a>
        @endif

        {{-- Blogs / Articles --}}
        @if (Route::has('blogs'))
          <a href="{{ route('blogs') }}"
             class="group rounded-xl border border-gray-200 p-4 hover:border-[#CF4520] hover:bg-[#CF4520]/5 transition">
            <div class="font-semibold text-gray-900 group-hover:text-[#CF4520]">
              Articles &amp; Stories
            </div>
            <div class="text-gray-600 mt-1">
              Read updates, tips, and resort highlights.
            </div>
          </a>
        @endif

        {{-- Payment --}}
        @if (Route::has('payment'))
          <a href="{{ route('payment') }}"
             class="group rounded-xl border border-gray-200 p-4 hover:border-[#CF4520] hover:bg-[#CF4520]/5 transition">
            <div class="font-semibold text-gray-900 group-hover:text-[#CF4520]">
              AVLCI Payment Portal
            </div>
            <div class="text-gray-600 mt-1">
              Settle your dues securely online.
            </div>
          </a>
        @endif

        {{-- FAQs --}}
        @if (Route::has('faqs'))
          <a href="{{ route('faqs') }}"
             class="group rounded-xl border border-gray-200 p-4 hover:border-[#CF4520] hover:bg-[#CF4520]/5 transition">
            <div class="font-semibold text-gray-900 group-hover:text-[#CF4520]">
              FAQs
            </div>
            <div class="text-gray-600 mt-1">
              Find quick answers to common questions.
            </div>
          </a>
        @endif

        {{-- Testimonials --}}
        @if (Route::has('testimonials'))
          <a href="{{ route('testimonials') }}"
             class="group rounded-xl border border-gray-200 p-4 hover:border-[#CF4520] hover:bg-[#CF4520]/5 transition">
            <div class="font-semibold text-gray-900 group-hover:text-[#CF4520]">
              Member Testimonials
            </div>
            <div class="text-gray-600 mt-1">
              See what other members are saying.
            </div>
          </a>
        @endif

        {{-- Contact / Help --}}
        @if (Route::has('contact'))
          <a href="{{ route('contact') }}"
             class="group rounded-xl border border-gray-200 p-4 hover:border-[#CF4520] hover:bg-[#CF4520]/5 transition">
            <div class="font-semibold text-gray-900 group-hover:text-[#CF4520]">
              Contact Us
            </div>
            <div class="text-gray-600 mt-1">
              Get in touch with our team.
            </div>
          </a>
        @endif

        {{-- About Astoria, if present --}}
        @if (Route::has('about-astoria'))
          <a href="{{ route('about-astoria') }}"
             class="group rounded-xl border border-gray-200 p-4 hover:border-[#CF4520] hover:bg-[#CF4520]/5 transition">
            <div class="font-semibold text-gray-900 group-hover:text-[#CF4520]">
              About Astoria Hotels &amp; Resorts
            </div>
            <div class="text-gray-600 mt-1">
              Explore our destinations and properties.
            </div>
          </a>
        @endif
      </div>

      <p class="mt-6 text-[15px] md:text-base text-gray-500">
        Still can’t find what you’re looking for?
        <a href="mailto:digital@astoria.com.ph"
           class="font-semibold text-[#CF4520] hover:text-[#3F2021] underline underline-offset-2">
          Let us know
        </a>.
      </p>
    </div>
  </div>
</section>
@endsection
