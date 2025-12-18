{{-- resources/views/admin/popup/create.blade.php --}}
@extends('admin.layout')

@section('page_title', $editing ? 'Dashboard' : 'Dashboard')

@section('content')
<section class="space-y-6">
  <div class="flex items-center justify-between gap-2">
    <div>
      <h1 class="text-xl font-bold text-slate-900">
        {{ $editing ? 'Edit Pop-up' : 'Add a New Pop-up' }}
      </h1>
      <p class="text-sm text-slate-600">
        Configure content, triggers, and page targeting for this pop-up.
      </p>
    </div>

    <a href="{{ route('admin.popups.index') }}"
       class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-3 py-1.5 text-sm text-slate-700 bg-white hover:bg-slate-50">
      <i class="fa-solid fa-arrow-left-long text-xs"></i>
      Back to Pop-up Manager
    </a>
  </div>

  @include('admin.popup.partials.form', [
      'editing'    => $editing,
      'current'    => $current ?? null,
      'showHelper' => $showHelper ?? true,
  ])
</section>
@endsection
