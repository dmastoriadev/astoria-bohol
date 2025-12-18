@php
  use Illuminate\Support\Carbon;
  use Illuminate\Support\Facades\Route;

  $tz  = 'Asia/Manila';
  $toPH = function($val) use ($tz) {
      if (empty($val)) return null;
      if ($val instanceof \Carbon\Carbon) return $val->copy()->timezone($tz);
      return Carbon::parse($val, 'UTC')->timezone($tz);
  };

  // ---- Bulk endpoints (POST) ----
  $bulkRestoreAction = Route::has('admin.blogs.bulkRestore')
      ? route('admin.blogs.bulkRestore')
      : url('/admin/blogs/bulk-restore');

  $bulkForceAction = Route::has('admin.blogs.bulkForceDelete')
      ? route('admin.blogs.bulkForceDelete')
      : url('/admin/blogs/bulk-force');

  // ---- Safely collect current page IDs even if paginator ----
  $rawItems = ($trashedArticles instanceof \Illuminate\Contracts\Pagination\Paginator)
      ? collect($trashedArticles->items())
      : collect($trashedArticles);

  $pageIds = $rawItems->pluck('id')->filter()->map(fn($id) => (string)$id)->values();

  // Counts (fallbacks if not provided)
  $trashTotal   = isset($trashTotal)   ? $trashTotal   : ($rawItems->count() ?? 0);
  $trashedCount = isset($trashedCount) ? $trashedCount : ($rawItems->count() ?? 0);
@endphp

<div
  x-data="{
    pageIds: @js($pageIds),
    selectedIds: [],

    updateMaster() {
      const master = this.$refs.master;
      if (!master) return;
      const total = this.pageIds.length;
      const selected = this.selectedIds.length;
      master.checked = total > 0 && selected === total;
      master.indeterminate = selected > 0 && selected < total;
    },

    toggleAll(checked) {
      this.selectedIds = checked ? [...new Set(this.pageIds)] : [];
    },

    onRowChange(e) {
      const cb = e.target;
      if (!cb || !cb.matches('input[data-row-cb]')) return;
      const id = String(cb.value);
      if (cb.checked) {
        if (!this.selectedIds.includes(id)) this.selectedIds.push(id);
      } else {
        this.selectedIds = this.selectedIds.filter(v => v !== id);
      }
    },

    clearSelection() {
      this.selectedIds = [];
      this.$nextTick(() => {
        if (this.$refs.master) {
          this.$refs.master.checked = false;
          this.$refs.master.indeterminate = false;
        }
      });
    },

    submitBulkRestore() {
      if (this.selectedIds.length === 0) return;
      const proceed = this.$store?.confirm
        ? new Promise((resolve) =>
            this.$store.confirm.ask({
              title: 'Restore selected blogs?',
              message: 'These will be restored as Draft.',
              variant: 'success',
              actionLabel: 'Restore',
              onConfirm: () => resolve(true),
              onCancel:  () => resolve(false),
            })
          )
        : Promise.resolve(confirm('Restore selected blogs as Draft?'));
      proceed.then((ok) => {
        if (!ok) return;
        this.$refs.idsJsonRestore.value = JSON.stringify([...new Set(this.selectedIds)]);
        this.$refs.bulkRestoreForm.submit();
      });
    },

    submitBulkForce() {
      if (this.selectedIds.length === 0) return;
      const proceed = this.$store?.confirm
        ? new Promise((resolve) =>
            this.$store.confirm.ask({
              title: 'Permanently delete selected?',
              message: 'This cannot be undone.',
              variant: 'danger',
              actionLabel: 'Delete forever',
              onConfirm: () => resolve(true),
              onCancel:  () => resolve(false),
            })
          )
        : Promise.resolve(confirm('Permanently delete selected blogs? This cannot be undone.'));
      proceed.then((ok) => {
        if (!ok) return;
        this.$refs.idsJsonForce.value = JSON.stringify([...new Set(this.selectedIds)]);
        this.$refs.bulkForceForm.submit();
      });
    }
  }"
  x-init="
    pageIds = [...new Set(pageIds.map(id => String(id)))];
    selectedIds = [];
    $watch('selectedIds', () => $nextTick(() => updateMaster()));
    $nextTick(() => updateMaster());
  "
  x-effect="updateMaster()"
  class="rounded-xl border bg-white overflow-hidden"
>
  <div class="flex items-center justify-between gap-3 px-4 py-3 bg-gray-50 border-b">
    <div class="flex items-center gap-2">
      <span class="font-semibold">Trash</span>
      <span class="text-xs rounded-full px-2 py-0.5 border bg-white">{{ $trashedCount }}</span>
    </div>

    <div class="flex items-center gap-2">
      <button
        type="button"
        class="inline-flex items-center gap-2 px-3 py-2 rounded border border-emerald-200 text-emerald-700 hover:bg-emerald-50 disabled:opacity-50 disabled:cursor-not-allowed"
        :disabled="selectedIds.length === 0"
        @click="submitBulkRestore()"
      >
        <i class="fa-solid fa-rotate-left"></i>
        <span>Restore Selected</span>
        <span class="inline-flex items-center justify-center min-w-6 h-6 text-xs font-semibold rounded-full border border-emerald-200 px-2"
              x-text="selectedIds.length"></span>
      </button>

      <button
        type="button"
        class="inline-flex items-center gap-2 px-3 py-2 rounded border border-red-200 text-red-700 hover:bg-red-50 disabled:opacity-50 disabled:cursor-not-allowed"
        :disabled="selectedIds.length === 0"
        :aria-disabled="selectedIds.length === 0"
        @click="submitBulkForce()"
        >
        <i class="fa-regular fa-trash-can"></i>
        <span>Delete Selected Forever</span>
        <span
            class="inline-flex items-center justify-center min-w-6 h-6 text-xs font-semibold rounded-full border border-red-200 px-2"
            x-text="selectedIds.length"
            aria-live="polite"
        ></span>
        </button>

      <button
        type="button"
        class="inline-flex items-center gap-2 px-3 py-2 rounded border border-gray-200 text-gray-700 hover:bg-gray-50 disabled:opacity-50"
        :disabled="selectedIds.length === 0"
        @click="clearSelection()"
      >
        <i class="fa-regular fa-square-minus"></i>
        <span>Clear</span>
      </button>
    </div>
  </div>

  <!-- Hidden POST forms (JSON payloads) -->
  <form x-ref="bulkRestoreForm" class="hidden" method="POST" action="{{ $bulkRestoreAction }}">
    @csrf
    <input type="hidden" name="ids_json" x-ref="idsJsonRestore">
  </form>

  <form x-ref="bulkForceForm" class="hidden" method="POST" action="{{ $bulkForceAction }}">
    @csrf
    <input type="hidden" name="ids_json" x-ref="idsJsonForce">
  </form>

  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50 text-xs uppercase text-gray-500">
        <tr>
          <!-- master select-all -->
          <th class="w-10 px-4 py-3">
            <input
              x-ref="master"
              type="checkbox"
              class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 disabled:opacity-50"
              :disabled="pageIds.length === 0"
              @change="toggleAll($event.target.checked)"
              aria-label="Select all on this page"
            />
          </th>

          <th class="px-4 py-3 text-left font-semibold">Page Title</th>
          <th class="px-4 py-3 text-left font-semibold">Slug</th>
          <th class="px-4 py-3 text-left font-semibold">Category</th>
          <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Deleted At (PH)</th>
          <th class="px-4 py-3 text-left font-semibold">Actions</th>
        </tr>
      </thead>

      <tbody class="divide-y divide-gray-100 text-sm">
        @php
          $hasRestore = \Illuminate\Support\Facades\Route::has('admin.blogs.restore');
          $hasForce   = \Illuminate\Support\Facades\Route::has('admin.blogs.forceDelete');
        @endphp

        @if(($trashTotal ?? 0) === 0)
          <tr>
            <td colspan="6" class="px-4 py-6 text-center text-gray-600">Trash is empty.</td>
          </tr>
        @elseif((is_object($trashedArticles) && method_exists($trashedArticles,'count') ? $trashedArticles->count() : 0) === 0)
          <tr>
            <td colspan="6" class="px-4 py-6 text-center text-gray-600">
              No items on this page.
              <a class="text-emerald-700 underline" href="{{ request()->fullUrlWithQuery(['trash_page'=>1]) }}">Go to page 1</a>.
            </td>
          </tr>
        @else
          @foreach($trashedArticles as $t)
            @php
              $rowId = (string) $t->id;
              $deletedDisp = !empty($t->deleted_at) ? $toPH($t->deleted_at)->format('M d, Y • g:i A') : '—';
            @endphp
            <tr class="hover:bg-gray-50" @change.capture="onRowChange($event)">
              <td class="px-4 py-3">
                <input
                  type="checkbox"
                  data-row-cb
                  value="{{ $rowId }}"
                  :checked="selectedIds.includes('{{ $rowId }}')"
                  class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                  aria-label="Select trashed blog: {{ $t->title }}"
                />
              </td>

              <td class="px-4 py-3 font-medium text-slate-800">{{ $t->title }}</td>
              <td class="px-4 py-3"><span class="font-mono text-xs bg-gray-100 px-2 py-0.5 rounded">{{ $t->slug }}</span></td>
              <td class="px-4 py-3 text-gray-700">{{ optional($t->category)->name ?? '—' }}</td>
              <td class="px-4 py-3 text-gray-700 whitespace-nowrap">{{ $deletedDisp }}</td>

              <td class="px-4 py-3">
                <div class="flex items-center gap-2">
                  @if($hasRestore)
                    <form method="POST" action="{{ route('admin.blogs.restore', $t->id) }}" x-data>
                      @csrf
                      <button type="button"
                              class="inline-flex items-center gap-1 px-2 py-1 rounded border hover:bg-emerald-50 text-emerald-700"
                              @click="$store?.confirm
                                ? $store.confirm.ask({
                                    title: 'Restore blog?',
                                    message: 'This will move the blog back to active as Draft.',
                                    variant: 'success',
                                    actionLabel: 'Restore',
                                    onConfirm: () => $el.closest('form').submit()
                                  })
                                : (confirm('Restore this blog?') && $el.closest('form').submit())">
                        <i class="fa-solid fa-rotate-left"></i><span>Restore</span>
                      </button>
                    </form>
                  @endif

                  @if($hasForce)
                    <form method="POST" action="{{ route('admin.blogs.forceDelete', $t->id) }}" x-data>
                      @csrf @method('DELETE')
                      <button type="button"
                              class="inline-flex items-center gap-1 px-2 py-1 rounded border hover:bg-red-50 text-red-600"
                              @click="$store?.confirm
                                ? $store.confirm.ask({
                                    title: 'Permanently delete?',
                                    message: 'This cannot be undone.',
                                    variant: 'danger',
                                    actionLabel: 'Delete forever',
                                    onConfirm: () => $el.closest('form').submit()
                                  })
                                : (confirm('Permanently delete? This cannot be undone.') && $el.closest('form').submit())">
                        <i class="fa-regular fa-trash-can"></i><span>Delete</span>
                      </button>
                    </form>
                  @endif
                </div>
              </td>
            </tr>
          @endforeach
        @endif
      </tbody>
    </table>
  </div>

  @if(method_exists($trashedArticles, 'links'))
    <div class="px-4 py-3 border-t bg-gray-50">
      {{ $trashedArticles->links() }}
    </div>
  @endif
</div>
