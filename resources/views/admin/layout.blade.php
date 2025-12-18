{{-- resources/views/admin/layout.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
  {{-- Keep ANY output before this line empty to avoid Quirks Mode. Save as UTF-8 (no BOM). --}}
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  @if (config('app.noindex'))
    <meta name="robots" content="noindex, nofollow, noarchive, nosnippet, noimageindex">
  @endif
  <meta name="google" content="notranslate">

  {{-- Title: prefer @section('title'), else @section('page_title'), else fallback --}}
  <title>
    @hasSection('title')
      @yield('title')
    @else
      @yield('page_title', 'ABH Admin')
    @endif
  </title>

  {{-- App assets --}}
  @vite(['resources/css/app.css', 'resources/js/app.js'])

  {{-- Font Awesome (global) --}}
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

  {{-- Head hooks for pages (e.g., TinyMCE CDN, per-page meta) --}}
  @stack('head')
  @yield('head')

  {{-- Global Alpine cloak (prevents FOUC while Alpine initializes) --}}
  <style>[x-cloak]{display:none!important}</style>

  {{-- Debug guard: log if Quirks Mode is active --}}
  <script>
    (function () {
      try {
        if (document.compatMode !== 'CSS1Compat') {
          console.error('Quirks Mode detected (document.compatMode=', document.compatMode, '). TinyMCE will not initialize.');
          document.documentElement.classList.add('quirks');
        }
      } catch (_) {}
    })();
  </script>
</head>
<body class="h-full bg-[#ebebeb] text-slate-800 antialiased">
<div x-data="{ openSidebar:false }" class="min-h-screen flex">
  {{-- SIDEBAR (desktop) --}}
  <aside class="hidden md:flex md:w-64 md:flex-col border-r bg-white">
    <img
      src="{{ asset('images/abh-logo.webp') }}"
      alt="Astoria Bohol"
      class="block w-[250px] h-24 p-3 object-contain"
    />

    <nav class="flex-1 overflow-y-auto p-3">
      @php $show = request()->query('show'); @endphp

      {{-- Group: Main --}}
      <div class="mb-4">
        <div class="px-2 py-2 text-xs font-semibold uppercase text-slate-500">Main</div>
        <a href="{{ route('admin.dashboard') }}"
           class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-100
                  {{ request()->routeIs('admin.dashboard') && (is_null($show) || $show === 'main') ? 'bg-slate-100 font-semibold' : '' }}">
          <i class="fa-solid fa-gauge w-4 text-center"></i>
          Overview
        </a>
      </div>

      {{-- Group: Blogs --}}
      <div class="mb-4">
        <div class="px-2 py-2 text-xs font-semibold uppercase text-slate-500">Blogs</div>
        <a href="{{ route('admin.dashboard') }}?show=blogs#blogs"
           class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-100
                  {{ request()->routeIs('admin.dashboard') && $show === 'blogs' ? 'bg-slate-100 font-semibold' : '' }}">
          <i class="fa-regular fa-list-alt w-4 text-center"></i>
          View All
        </a>
        <a href="{{ route('admin.dashboard') }}?show=add-blog#add-blog"
           class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-100
                  {{ request()->routeIs('admin.dashboard') && $show === 'add-blog' ? 'bg-slate-100 font-semibold' : '' }}">
          <i class="fa-solid fa-plus w-4 text-center"></i>
          Add a Blog
        </a>
      </div>

      {{-- Group: Promos --}}
      <div class="mb-4">
        <div class="px-2 py-2 text-xs font-semibold uppercase text-slate-500">Promos</div>
        <a href="{{ route('admin.dashboard', ['show' => 'promos']) }}#promos"
           class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-100
                  {{ request()->routeIs('admin.dashboard') && $show === 'promos' ? 'bg-slate-100 font-semibold' : '' }}">
          <i class="fa-regular fa-rectangle-list w-4 text-center"></i>
          View All
        </a>
        <a href="{{ route('admin.dashboard', ['show' => 'add-promo']) }}#add-promo"
           class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-100
                  {{ request()->routeIs('admin.dashboard') && $show === 'add-promo' ? 'bg-slate-100 font-semibold' : '' }}">
          <i class="fa-solid fa-plus w-4 text-center"></i>
          Add a Promo
        </a>
      </div>

            {{-- Group: Pop-ups --}}
      <div class="mb-4">
        <div class="px-2 py-2 text-xs font-semibold uppercase text-slate-500">Pop-ups</div>

        {{-- View All Pop-ups --}}
        <a href="{{ route('admin.popups.index') }}"
           class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-100
                  {{ request()->routeIs('admin.popups.index') ? 'bg-slate-100 font-semibold' : '' }}">
          <i class="fa-regular fa-window-restore w-4 text-center"></i>
          View All
        </a>

        {{-- Add Pop-up --}}
        <a href="{{ route('admin.popups.create') }}"
           class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-100
                  {{ request()->routeIs('admin.popups.create') ? 'bg-slate-100 font-semibold' : '' }}">
          <i class="fa-solid fa-plus w-4 text-center"></i>
          Add Pop-up
        </a>
      </div>


      {{-- Group: Library --}}
      <div class="mb-4">
        <div class="px-2 py-2 text-xs font-semibold uppercase text-slate-500">Library</div>
        <a href="{{ route('admin.media.index') }}"
           class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-100
                  {{ request()->routeIs('admin.media.index') ? 'bg-slate-100 font-semibold' : '' }}">
          <i class="fa-regular fa-images w-4 text-center"></i>
          Library
        </a>
      </div>
    </nav>
  </aside>

  {{-- MOBILE DRAWER --}}
  <div class="md:hidden" x-cloak>
    <button
      @click="openSidebar = true"
      class="fixed top-3 left-3 z-[60] inline-flex items-center justify-center w-9 h-9 rounded-lg bg-white/90 shadow"
    >
      <i class="fa-solid fa-bars"></i>
    </button>

    <div
      x-show="openSidebar"
      class="fixed inset-0 z-[70] bg-black/40"
      @click="openSidebar = false"
    ></div>

    <aside
      x-show="openSidebar"
      x-transition
      class="fixed z-[80] inset-y-0 left-0 w-72 bg-white border-r shadow-lg overflow-y-auto"
    >
      <div class="h-14 flex items-center justify-between px-4 border-b">
        <span class="font-bold">APZ Admin</span>
        <button
          @click="openSidebar = false"
          class="w-9 h-9 grid place-items-center rounded-lg hover:bg-slate-100"
        >
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <nav class="p-3">
        {{-- Main --}}
        <div class="mb-4">
          <div class="px-2 py-2 text-xs font-semibold uppercase text-slate-500">Main</div>
          <a href="{{ route('admin.dashboard') }}"
             class="block px-3 py-2 rounded-lg hover:bg-slate-100
                    {{ request()->routeIs('admin.dashboard') && (is_null($show) || $show === 'main') ? 'bg-slate-100 font-semibold' : '' }}">
            Overview
          </a>
        </div>

        {{-- Blogs --}}
        <div class="mb-4">
          <div class="px-2 py-2 text-xs font-semibold uppercase text-slate-500">Blogs</div>
          <a href="{{ route('admin.dashboard') }}?show=blogs#blogs"
             class="block px-3 py-2 rounded-lg hover:bg-slate-100
                    {{ request()->routeIs('admin.dashboard') && $show === 'blogs' ? 'bg-slate-100 font-semibold' : '' }}">
            View All
          </a>
          <a href="{{ route('admin.dashboard') }}?show=add-blog#add-blog"
             class="block px-3 py-2 rounded-lg hover:bg-slate-100
                    {{ request()->routeIs('admin.dashboard') && $show === 'add-blog' ? 'bg-slate-100 font-semibold' : '' }}">
            Add a Blog
          </a>
        </div>

        {{-- Promos --}}
        <div class="mb-4">
          <div class="px-2 py-2 text-xs font-semibold uppercase text-slate-500">Promos</div>
          <a href="{{ route('admin.dashboard', ['show' => 'promos']) }}#promos"
             class="block px-3 py-2 rounded-lg hover:bg-slate-100
                    {{ request()->routeIs('admin.dashboard') && $show === 'promos' ? 'bg-slate-100 font-semibold' : '' }}">
            View All
          </a>
          <a href="{{ route('admin.dashboard', ['show' => 'add-promo']) }}#add-promo"
             class="block px-3 py-2 rounded-lg hover:bg-slate-100
                    {{ request()->routeIs('admin.dashboard') && $show === 'add-promo' ? 'bg-slate-100 font-semibold' : '' }}">
            Add a Promo
          </a>
        </div>

                {{-- Group: Pop-ups --}}
        <div class="mb-4">
          <div class="px-2 py-2 text-xs font-semibold uppercase text-slate-500">Pop-ups</div>

          {{-- View All Pop-ups --}}
          <a href="{{ route('admin.popups.index') }}"
             class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-100
                    {{ request()->routeIs('admin.popups.index') ? 'bg-slate-100 font-semibold' : '' }}">
            <i class="fa-regular fa-window-restore w-4 text-center"></i>
            View All
          </a>

          {{-- Add Pop-up --}}
          <a href="{{ route('admin.popups.create') }}"
             class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-100
                    {{ request()->routeIs('admin.popups.create') ? 'bg-slate-100 font-semibold' : '' }}">
            <i class="fa-solid fa-plus w-4 text-center"></i>
            Add Pop-up
          </a>
        </div>


        {{-- Library --}}
        <div class="mb-4">
          <div class="px-2 py-2 text-xs font-semibold uppercase text-slate-500">Library</div>
          <a href="{{ route('admin.media.index') }}"
             class="block px-3 py-2 rounded-lg hover:bg-slate-100
                    {{ request()->routeIs('admin.media.index') ? 'bg-slate-100 font-semibold' : '' }}">
            Library
          </a>
        </div>
      </nav>
    </aside>
  </div>

  {{-- CONTENT COLUMN --}}
  <div class="flex-1 min-w-0 flex flex-col">
    <header class="sticky top-0 z-40 h-14 bg-white border-b flex items-center justify-between px-4">
      <div class="font-semibold">@yield('page_title','Dashboard')</div>
      <div class="flex items-center gap-4">
        <div class="text-sm">
          Hi,
          <span class="font-semibold">
            {{ auth()->user()->name ?? auth()->user()->email }}
          </span>!
        </div>
        <form method="POST" action="{{ route('admin.logout') }}">
          @csrf
          <button class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-900 text-white hover:bg-black">
            <i class="fa-solid fa-arrow-right-from-bracket"></i>
            Logout
          </button>
        </form>
      </div>
    </header>

    <main class="p-6">
      @stack('modals')
      @yield('content')
    </main>
  </div>
</div>

{{-- Alpine (defer) --}}
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" crossorigin="anonymous"></script>

{{-- Page/script hooks (your pages, like Library, will push their own scripts here) --}}
@stack('scripts')
@yield('scripts')

</body>
</html>
