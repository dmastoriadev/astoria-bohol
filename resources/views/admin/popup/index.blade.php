@extends('admin.layout')

@section('page_title', 'Dashboard')

@section('content')
<section id="popup-manager" class="space-y-6">

  @php
    // Toast mapping: popup_status = new|edit|draft|delete
    $popupStatus = session('popup_status'); // 'new', 'edit', 'draft', 'delete'
    $hasError    = session('error') || $errors->any();

    $toastTitle     = null;
    $toastSub       = null;
    $toastClasses   = 'border-slate-200 bg-slate-50';
    $toastIconClass = 'fa-solid fa-circle-info text-slate-500 text-2xl';

    if (! $hasError && $popupStatus) {
        switch ($popupStatus) {
            case 'new':
                $toastTitle     = 'Pop-up has been published';
                $toastSub       = 'This pop-up is now live based on its triggers and page targeting.';
                $toastClasses   = 'border-emerald-200 bg-white';
                $toastIconClass = 'fa-solid fa-circle-check text-emerald-500 text-2xl';
                break;

            case 'edit':
                $toastTitle     = 'Pop-up has been edited';
                $toastSub       = 'Your latest changes are now saved.';
                $toastClasses   = 'border-sky-200 bg-white';
                $toastIconClass = 'fa-solid fa-circle-check text-sky-500 text-2xl';
                break;

            case 'draft':
                $toastTitle     = 'Pop-up has been drafted';
                $toastSub       = 'This pop-up is saved as draft and will not show to visitors.';
                $toastClasses   = 'border-amber-200 bg-amber-50';
                $toastIconClass = 'fa-solid fa-file-lines text-amber-500 text-2xl';
                break;

            case 'delete':
                $toastTitle     = 'Pop-up has been permanently deleted';
                $toastSub       = 'This record has been removed from the Pop-up Manager.';
                $toastClasses   = 'border-red-200 bg-red-50';
                $toastIconClass = 'fa-solid fa-trash-can text-red-500 text-2xl';
                break;
        }
    }

    if ($hasError) {
        $toastClasses   = 'border-red-200 bg-red-50';
        $toastIconClass = 'fa-solid fa-circle-exclamation text-red-500 text-2xl';
    }
  @endphp

  {{-- Toast-style flash notifications (TOP-RIGHT, larger text) --}}
  @if($popupStatus || $hasError)
    <div
      x-data="{ show: true }"
      x-show="show"
      x-transition
      x-init="setTimeout(() => show = false, 6000)"
      class="fixed top-4 right-4 z-[9999] max-w-sm w-full sm:w-96"
      style="pointer-events:none;"
    >
      <div class="pointer-events-auto rounded-xl shadow-lg border overflow-hidden {{ $toastClasses }}">
        <div class="flex items-start gap-3 px-4 py-3">
          <div class="mt-1">
            <i class="{{ $toastIconClass }}"></i>
          </div>

          <div class="text-sm sm:text-[15px] leading-snug">
            @if($hasError)
              @if(session('error'))
                <p class="font-semibold text-red-900 mb-0.5">
                  {{ session('error') }}
                </p>
              @else
                <p class="font-semibold text-red-900 mb-0.5">
                  Please fix the following:
                </p>
              @endif

              @if($errors->any())
                <ul class="list-disc list-inside space-y-0.5 text-red-800 mt-1">
                  @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                  @endforeach
                </ul>
              @endif
            @elseif($popupStatus && $toastTitle)
              <p class="font-semibold text-slate-900 mb-0.5">
                {{ $toastTitle }}
              </p>
              @if($toastSub)
                <p class="text-[13px] text-slate-600">
                  {{ $toastSub }}
                </p>
              @endif
            @endif
          </div>

          <button
            type="button"
            @click="show = false"
            class="ml-auto text-[12px] text-slate-500 hover:text-slate-700"
          >
            Dismiss
          </button>
        </div>
      </div>
    </div>
  @endif

  {{-- Heading / intro --}}
  <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
    <div>
      <h1 class="text-xl font-bold text-slate-900">Pop-up Manager</h1>
      <p class="text-sm text-slate-600">
        View all site pop-ups, manage their status, and configure where they appear.
      </p>
    </div>

    <div class="flex flex-col items-stretch sm:flex-row sm:items-center gap-3">
      @isset($counts)
        <div class="inline-flex flex-wrap items-center gap-2 text-xs text-slate-600">
          <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-white border border-slate-200 shadow-sm">
            <span class="w-2 h-2 rounded-full bg-slate-400"></span>
            <span>All: {{ $counts['all'] ?? 0 }}</span>
          </span>
          <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-emerald-50 border border-emerald-200">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            <span>Active: {{ $counts['active'] ?? 0 }}</span>
          </span>
          <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-yellow-50 border border-yellow-200">
            <span class="w-2 h-2 rounded-full bg-yellow-500"></span>
            <span>Draft: {{ $counts['draft'] ?? 0 }}</span>
          </span>
        </div>
      @endisset

      <a href="{{ route('admin.popups.create') }}"
         class="inline-flex items-center justify-center gap-2 rounded-lg bg-slate-900 text-white px-4 py-2 text-sm font-semibold hover:bg-black shadow-sm">
        <i class="fa-solid fa-plus text-xs"></i>
        Add new Pop-up
      </a>
    </div>
  </div>

  {{-- Filters + search --}}
  <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
    {{-- Status pills --}}
    <div class="flex flex-wrap items-center gap-2 text-xs">
      @php
        $currentStatus = $status ?? null;
      @endphp

      <span class="text-[11px] uppercase tracking-[.16em] text-slate-500 mr-1">
        Status:
      </span>

      <a href="{{ request()->fullUrlWithQuery(['status' => null]) }}"
         class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full border text-xs
                {{ !$currentStatus ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50' }}">
        All
      </a>

      <a href="{{ request()->fullUrlWithQuery(['status' => 'active']) }}"
         class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full border text-xs
                {{ $currentStatus === 'active' ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white text-slate-700 border-slate-200 hover:bg-emerald-50' }}">
        Active
      </a>

      <a href="{{ request()->fullUrlWithQuery(['status' => 'draft']) }}"
         class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full border text-xs
                {{ $currentStatus === 'draft' ? 'bg-yellow-500 text-white border-yellow-500' : 'bg-white text-slate-700 border-slate-200 hover:bg-yellow-50' }}">
        Draft
      </a>
    </div>

    {{-- Scope + search --}}
    <form method="GET"
          action="{{ route('admin.popups.index') }}"
          class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-end">
      {{-- Preserve status in search --}}
      @if($status)
        <input type="hidden" name="status" value="{{ $status }}">
      @endif

      <div class="flex items-center gap-2">
        <label for="scope" class="text-xs text-slate-600">Scope:</label>
        <select id="scope" name="scope"
                class="rounded-lg border border-slate-300 bg-white text-xs px-2 py-1.5 focus:ring-2 focus:ring-emerald-500">
          <option value=""         {{ $scope === null        ? 'selected' : '' }}>All</option>
          <option value="all"      {{ $scope === 'all'       ? 'selected' : '' }}>All pages</option>
          <option value="include"  {{ $scope === 'include'   ? 'selected' : '' }}>Only listed</option>
          <option value="exclude"  {{ $scope === 'exclude'   ? 'selected' : '' }}>All except listed</option>
        </select>
      </div>

      <div class="relative w-full sm:w-64">
        <input
          type="search"
          name="q"
          value="{{ $q }}"
          placeholder="Search pop-ups…"
          class="w-full rounded-lg border border-slate-300 pl-8 pr-10 py-1.5 text-xs focus:ring-2 focus:ring-emerald-500"
        >
        <span class="absolute inset-y-0 left-2 flex items-center justify-center pointer-events-none">
          <i class="fa-solid fa-magnifying-glass text-slate-400 text-xs"></i>
        </span>

        @if($q !== '')
          <a href="{{ route('admin.popups.index', array_filter(['status' => $status, 'scope' => $scope])) }}"
             class="absolute inset-y-0 right-2 flex items-center justify-center text-[11px] text-slate-400 hover:text-slate-600">
            Clear
          </a>
        @endif
      </div>

      <button
        type="submit"
        class="inline-flex items-center gap-1 rounded-lg bg-slate-900 text-white px-3 py-1.5 text-xs font-semibold hover:bg-black">
        Apply
      </button>
    </form>
  </div>

  {{-- Table card --}}
  <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    @include('admin.dashboard.partials.popup_table', ['popups' => $popups])
  </div>
</section>
@endsection
