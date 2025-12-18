{{-- resources/views/admin/popup/partials/form.blade.php --}}

@php
  // Normalize edit state whether called from popup.index or dashboard
  $editing = $editing ?? (isset($editPopup) && $editPopup);
  $current = $current ?? ($editing ? ($editPopup ?? null) : null);

  // Control whether to show the right-hand helper card
  $showHelper = $showHelper ?? true;

  $gridCols = $showHelper ? 'lg:grid-cols-3' : 'lg:grid-cols-1';
  $formCol  = $showHelper ? 'lg:col-span-2' : 'lg:col-span-1';

  /** CTA values */
  $cta1_label = old('cta1_label', $current->cta1_label ?? '');
  $cta1_url   = old('cta1_url',   $current->cta1_url   ?? '');
  $cta2_label = old('cta2_label', $current->cta2_label ?? '');
  $cta2_url   = old('cta2_url',   $current->cta2_url   ?? '');
  $cta3_label = old('cta3_label', $current->cta3_label ?? '');
  $cta3_url   = old('cta3_url',   $current->cta3_url   ?? '');

  $scrollDir = old('trigger_scroll_direction', $current->trigger_scroll_direction ?? 'down');
  $scrollPct = old('trigger_scroll_percent',   $current->trigger_scroll_percent   ?? 25);
  $scope     = old('target_scope',            $current->target_scope            ?? 'all');

  // Derive current status for radio group: active | draft
  if ($editing && $current) {
      if ($current->is_draft ?? false) {
          $statusDefault = 'draft';
      } else {
          $statusDefault = 'active';
      }
  } else {
      $statusDefault = 'draft';
  }
  $statusValue = old('status', $statusDefault);

  // Extra images (array) for gallery
  $imageGalleryInitial = old('image_gallery', $current->image_gallery ?? []);
  if (! is_array($imageGalleryInitial)) {
      $imageGalleryInitial = [];
  }

  // Toast mapping: popup_status = new|edit|draft|delete
  $popupStatus = session('popup_status'); // optional: 'new', 'edit', 'draft', 'delete'
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

  // Override styling if there are errors
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

<div class="grid gap-6 {{ $gridCols }}">
  {{-- ===== Form ===== --}}
  <div class="{{ $formCol }} rounded-xl border bg-white p-5 space-y-5">
    <div class="flex items-center justify-between gap-2 mb-1">
      <h2 class="text-sm font-semibold text-slate-900">
        {{ $editing ? 'Edit Pop-up' : 'Create a Pop-up' }}
      </h2>

      @if($editing && $current?->updated_at)
        <p class="text-[11px] text-slate-500">
          Last updated: {{ $current->updated_at->timezone('Asia/Manila')->format('M d, Y • g:i A') }}
        </p>
      @endif
    </div>

    <form
      method="POST"
      action="{{ $editing ? route('admin.popups.update', $current) : route('admin.popups.store') }}"
      class="space-y-5"
      x-data="{
        status: @js($statusValue),
        imageGallery: @js($imageGalleryInitial),
        syncStatus() {
          if (this.$refs.is_active) {
            this.$refs.is_active.value = (this.status === 'active') ? '1' : '0';
          }
          if (this.$refs.is_draft) {
            this.$refs.is_draft.value = (this.status === 'draft') ? '1' : '0';
          }
        },
        addImage() {
          this.imageGallery.push('');
        },
        removeImage(index) {
          this.imageGallery.splice(index, 1);
        }
      }"
      x-init="syncStatus()"
      @change="syncStatus()"
    >
      @csrf
      @if($editing)
        @method('PUT')
      @endif

      {{-- Title + description --}}
      <div class="space-y-3">
        <div>
          <label class="block text-sm font-semibold mb-1">Pop-up title</label>
          <input type="text" name="title"
                 value="{{ old('title', $current->title ?? '') }}"
                 class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-emerald-500"
                 required>
        </div>

        <div>
          <label class="block text-sm font-semibold mb-1">Pop-up description</label>
          <textarea name="description" rows="3"
                    class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-emerald-500"
                    placeholder="Short description or message...">{{ old('description', $current->description ?? '') }}</textarea>
        </div>

        {{-- Primary + additional images --}}
        <div class="space-y-3">
          <div>
            <label class="block text-sm font-semibold mb-1">Primary Image URL</label>
            <input type="text" name="image_path"
                   value="{{ old('image_path', $current->image_path ?? '') }}"
                   class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-emerald-500"
                   placeholder="/storage/..., https://..., etc.">
            <p class="mt-1 text-xs text-slate-500">
              This is the first image in the pop-up. If more images are added, this one will appear first.
            </p>
          </div>

          <div class="space-y-2">
            <div class="flex items-center justify-between">
              <label class="block text-sm font-semibold text-slate-800">
                Additional Image URLs (optional)
              </label>
              <button
                type="button"
                @click="addImage()"
                class="inline-flex items-center gap-1 rounded-full border border-slate-300 px-2.5 py-1 text-[13px] font-semibold text-slate-700 hover:bg-slate-50"
              >
                <i class="fa-solid fa-plus text-[10px]"></i>
                Add image
              </button>
            </div>

            <template x-for="(img, index) in imageGallery" :key="index">
              <div class="flex items-center gap-2">
                <input
                  type="text"
                  class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-emerald-500 text-sm"
                  :name="'image_gallery[' + index + ']'"
                  x-model="imageGallery[index]"
                  placeholder="/storage/... or https://..."
                >
                <button
                  type="button"
                  @click="removeImage(index)"
                  class="inline-flex items-center justify-center w-7 h-7 rounded-full border border-slate-300 text-slate-500 hover:bg-red-50 hover:text-red-600"
                  aria-label="Remove image"
                >
                  <i class="fa-solid fa-xmark text-[11px]"></i>
                </button>
              </div>
            </template>

            <p class="text-[11px] text-slate-400" x-show="imageGallery.length > 0">
              If there are 2 or more images, the pop-up will show them as a carousel.
            </p>
          </div>
        </div>
      </div>

      {{-- CTAs --}}
      <div class="space-y-3">
        <h3 class="text-sm font-semibold text-slate-900">CTAs</h3>

        @foreach([1,2,3] as $i)
          @php
            $lblVar = ${"cta{$i}_label"};
            $urlVar = ${"cta{$i}_url"};
          @endphp
          <div class="grid gap-2 md:grid-cols-2">
            <div>
              <label class="block text-xs font-semibold mb-1">CTA {{ $i }} label</label>
              <input type="text" name="cta{{ $i }}_label"
                     value="{{ $lblVar }}"
                     class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-emerald-500"
                     placeholder="e.g. Book Now">
            </div>
            <div>
              <label class="block text-xs font-semibold mb-1">CTA {{ $i }} link</label>
              <input type="text" name="cta{{ $i }}_url"
                     value="{{ $urlVar }}"
                     class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-emerald-500"
                     placeholder="https://... or internal URL">
            </div>
          </div>
        @endforeach
      </div>

      {{-- Triggers --}}
      <div class="space-y-4 border-t pt-4">
        <h3 class="text-sm font-semibold text-slate-900">Triggers</h3>

        {{-- On click --}}
        <div class="space-y-2">
          <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="trigger_on_click" value="1"
                   class="rounded border-gray-300"
                   @checked(old('trigger_on_click', $current->trigger_on_click ?? false))>
            <span class="text-sm">On click (special class)</span>
          </label>
          <div class="grid gap-2 md:grid-cols-[minmax(0,1fr)_auto] items-center">
            <div>
              <label class="block text-xs font-semibold mb-1">Custom class (optional)</label>
              <input type="text" name="trigger_click_class"
                     value="{{ old('trigger_click_class', $current->trigger_click_class ?? '') }}"
                     class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-emerald-500"
                     placeholder="Leave blank to auto-use js-popup-{id}">
            </div>
            <div class="text-xs text-slate-500 md:text-right">
              @if($editing && $current)
                Special class:
                <code class="px-1 py-0.5 rounded bg-slate-100 text-[11px]">
                  {{ $current->click_class ?: 'js-popup-'.$current->id }}
                </code>
              @else
                After saving, auto class will be:
                <code class="px-1 py-0.5 rounded bg-slate-100 text-[11px]">js-popup-{id}</code>
              @endif
            </div>
          </div>
          <p class="text-xs text-slate-500">
            Add this class to any button/link on the site to open the pop-up.
          </p>
        </div>

        {{-- On page load --}}
        <div class="grid gap-3 md:grid-cols-[auto_minmax(0,1fr)] items-center">
          <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="trigger_on_load" value="1"
                   class="rounded border-gray-300"
                   @checked(old('trigger_on_load', $current->trigger_on_load ?? false))>
            <span class="text-sm">On page load</span>
          </label>
          <div class="flex items-center gap-2">
            <span class="text-xs text-slate-500">Within</span>
            <input type="number" name="trigger_load_delay_seconds"
                   min="0" max="600" step="1"
                   value="{{ old('trigger_load_delay_seconds', $current->trigger_load_delay_seconds ?? 0) }}"
                   class="w-20 rounded-lg border-gray-300 focus:ring-2 focus:ring-emerald-500 text-sm">
            <span class="text-xs text-slate-500">seconds</span>
          </div>
        </div>

        {{-- On scroll --}}
        <div class="space-y-2">
          <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="trigger_on_scroll" value="1"
                   class="rounded border-gray-300"
                   @checked(old('trigger_on_scroll', $current->trigger_on_scroll ?? false))>
            <span class="text-sm">On scroll</span>
          </label>

          <div class="grid gap-3 md:grid-cols-2">
            <div class="flex items-center gap-2">
              <span class="text-xs text-slate-500">Direction</span>
              <select name="trigger_scroll_direction"
                      class="rounded-lg border-gray-300 focus:ring-2 focus:ring-emerald-500 text-sm">
                <option value="down" @selected($scrollDir === 'down')>Down</option>
                <option value="up"   @selected($scrollDir === 'up')>Up</option>
              </select>
            </div>

            <div class="flex items-center gap-2">
              <span class="text-xs text-slate-500">Within %</span>
              <select name="trigger_scroll_percent"
                      class="rounded-lg border-gray-300 focus:ring-2 focus:ring-emerald-500 text-sm">
                <option value="25" @selected((int)$scrollPct === 25)>25%</option>
                <option value="50" @selected((int)$scrollPct === 50)>50%</option>
                <option value="75" @selected((int)$scrollPct === 75)>75%</option>
              </select>
            </div>
          </div>
          <p class="text-xs text-slate-500">
            Example: Direction = Down, Within = 50% → pop-up appears after user scrolls halfway down.
          </p>
        </div>
      </div>

      {{-- Page Targeting --}}
      <div class="space-y-3 border-t pt-4">
        <h3 class="text-sm font-semibold text-slate-900">Page Targeting</h3>

        <div class="space-y-1 text-sm">
          <label class="inline-flex items-center gap-2">
            <input type="radio" name="target_scope" value="all"
                   class="rounded border-gray-300"
                   @checked($scope === 'all')>
            <span>All pages</span>
          </label>
          <label class="inline-flex items-center gap-2">
            <input type="radio" name="target_scope" value="include"
                   class="rounded border-gray-300"
                   @checked($scope === 'include')>
            <span>Only these paths</span>
          </label>
          <label class="inline-flex items-center gap-2">
            <input type="radio" name="target_scope" value="exclude"
                   class="rounded border-gray-300"
                   @checked($scope === 'exclude')>
            <span>All pages except these paths</span>
          </label>
        </div>

        <div>
          <label class="block text-xs font-semibold mb-1">Paths (one per line)</label>
          <textarea name="target_paths" rows="3"
                    class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-emerald-500"
                    placeholder="Examples:
blog/*
promos/*
about
/rooms/premier">{{ old('target_paths', $current->target_paths ?? '') }}</textarea>
          <p class="mt-1 text-xs text-slate-500">
            Matching uses simple wildcards. <code>*</code> matches any segment, e.g. <code>blog/*</code>.
          </p>
        </div>
      </div>

      {{-- Status radios + submit --}}
      <div class="flex flex-wrap items-center justify-between gap-3 border-t pt-4">
        <div class="space-y-1 text-sm">
          <p class="text-[11px] uppercase tracking-[.16em] text-slate-500 font-semibold mb-1">
            Status
          </p>

          <label class="inline-flex items-center gap-2">
            <input
              type="radio"
              name="status"
              value="active"
              class="border-gray-300"
              x-model="status"
            >
            <span>Active (live)</span>
          </label>

          <label class="inline-flex items-center gap-2">
            <input
              type="radio"
              name="status"
              value="draft"
              class="border-gray-300"
              x-model="status"
            >
            <span>Draft (not ready)</span>
          </label>
        </div>

        <div class="flex items-center gap-2">
          {{-- Hidden fields used by controller (maps radios → booleans) --}}
          <input type="hidden" name="is_active" x-ref="is_active">
          <input type="hidden" name="is_draft"  x-ref="is_draft">

          <button type="submit"
                  class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 text-white px-4 py-2 text-sm font-semibold hover:bg-emerald-700">
            <i class="fa-regular fa-floppy-disk"></i>
            {{ $editing ? 'Save changes' : 'Create pop-up' }}
          </button>
        </div>
      </div>
    </form>
  </div>

  {{-- ===== Helper side card (optional) --}}
  @if($showHelper)
    <aside class="space-y-4">
      <div class="rounded-xl border bg-white p-4 text-sm">
        <h3 class="font-semibold text-slate-900 mb-2">How to use</h3>
        <ol class="list-decimal list-inside space-y-1 text-slate-700 text-xs">
          <li>Create a pop-up and save it.</li>
          <li>For on-click: add
            <code class="px-1 rounded bg-slate-100">js-popup-{id}</code>
            (or your custom class) to any CTA.
          </li>
          <li>For page-load / scroll: just set the triggers; no extra markup needed.</li>
          <li>
            When a visitor closes the pop-up, it stays hidden while that tab is open.
            A simple refresh keeps it hidden; a new tab or fresh visit can show it again.
          </li>
        </ol>
      </div>
    </aside>
  @endif
</div>
