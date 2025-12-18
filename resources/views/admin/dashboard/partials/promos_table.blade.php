@php
  use Illuminate\Support\Carbon;
  use Illuminate\Support\Facades\Route;

  // ---------- Normalize input (paginator or array/collection) ----------
  $rows = is_object($promos) && method_exists($promos, 'items')
      ? collect($promos->items())
      : collect($promos);

  // ---------- Time helpers (PH display) ----------
  $tz  = 'Asia/Manila';
  $now = Carbon::now($tz);

  $toPH = function($val) use ($tz) {
      if (empty($val)) return null;
      if ($val instanceof \Carbon\Carbon) return $val->copy()->timezone($tz);
      return Carbon::parse($val, 'UTC')->timezone($tz);
  };

  // ---------- Effective status (expired > scheduled > published > draft) ----------
  $effectiveStatus = function ($item) use ($now, $toPH) {
      $raw   = strtolower($item->status ?? '');
      $sched = !empty($item->scheduled_publish_date) ? $toPH($item->scheduled_publish_date) : null;
      $pub   = !empty($item->published_at)           ? $toPH($item->published_at)           : null;
      $exp   = !empty($item->expires_at)             ? $toPH($item->expires_at)             : null;

      if ($exp && $exp->lessThanOrEqualTo($now)) return ['slug'=>'expired','label'=>'Expired','class'=>'bg-rose-50 text-rose-700 border-rose-200'];
      if (($sched && $sched->greaterThan($now)) || ($pub && $pub->greaterThan($now))) return ['slug'=>'scheduled','label'=>'Scheduled','class'=>'bg-amber-50 text-amber-700 border-amber-200'];
      if (($pub && $pub->lessThanOrEqualTo($now)) || ($sched && $sched->lessThanOrEqualTo($now))) return ['slug'=>'published','label'=>'Published','class'=>'bg-emerald-50 text-emerald-700 border-emerald-200'];
      if ($raw==='draft' || blank($item->status)) return ['slug'=>'draft','label'=>'Draft','class'=>'bg-gray-50 text-gray-700 border-gray-200'];
      return ['slug'=>'published','label'=>'Published','class'=>'bg-emerald-50 text-emerald-700 border-emerald-200'];
  };

  // ---------- Public URL builder for promos ----------
  $publicUrlForPromo = function($promo) {
      $slug = $promo->slug ?: $promo->id;
      try {
          if (Route::has('promos.show')) return route('promos.show', $slug);
          if (Route::has('promo.show'))  return route('promo.show',  $slug);
      } catch (\Throwable $e) { /* ignore */ }
      return url('/promos/' . $slug);
  };

  // ---------- Bulk Trash endpoint (POST) ----------
  $bulkTrashAction = Route::has('admin.promos.bulkTrash')
      ? route('admin.promos.bulkTrash')
      : url('/admin/promos/bulk-trash');

  // ---------- Safely collect current page IDs ----------
  $pageIds = $rows->pluck('id')->filter()->map(fn($id) => (string)$id)->values();
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
      // Drive selection purely from state; row checkboxes mirror via :checked binding
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

    // ---------- Build ALL payload formats ----------
    buildFormPayload() {
      const ids = [...new Set(this.selectedIds.map(String))];

      // 1) JSON payload
      if (this.$refs.idsJson) {
        this.$refs.idsJson.value = JSON.stringify(ids);
      }

      // 2) CSV payload
      if (this.$refs.idsCsv) {
        this.$refs.idsCsv.value = ids.join(',');
      }

      // 3) Array inputs: ids[]
      if (this.$refs.idsArray) {
        this.$refs.idsArray.innerHTML = '';
        ids.forEach(id => {
          const input = document.createElement('input');
          input.type  = 'hidden';
          input.name  = 'ids[]';
          input.value = id;
          this.$refs.idsArray.appendChild(input);
        });
      }
    },

    submitBulkTrash() {
      if (this.selectedIds.length === 0) return;

      const proceed = this.$store?.confirm
        ? new Promise((resolve) =>
            this.$store.confirm.ask({
              title: 'Move selected promos to Trash?',
              message: 'These promos will be soft-deleted. You can restore them later.',
              variant: 'danger',
              actionLabel: 'Move to Trash',
              onConfirm: () => resolve(true),
              onCancel:  () => resolve(false),
            })
          )
        : Promise.resolve(confirm('Move selected promos to Trash?'));

      proceed.then((ok) => {
        if (!ok) return;
        this.buildFormPayload();
        this.$refs.bulkForm.submit();
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
  class="space-y-3"
>
  <!-- Bulk toolbar -->
  <div class="flex items-center justify-between px-4">
    <div class="flex items-center gap-2">
      <button
        type="button"
        class="inline-flex items-center gap-2 px-3 py-2 rounded border border-red-200 text-red-700 hover:bg-red-50 disabled:opacity-50 disabled:cursor-not-allowed"
        :disabled="selectedIds.length === 0"
        @click="submitBulkTrash()"
      >
        <i class="fa-regular fa-trash-can"></i>
        <span>Move Selected to Trash</span>
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
        <span>Clear Selection</span>
      </button>
    </div>

    <div class="text-sm text-gray-600" x-show="selectedIds.length > 0">
      <span x-text="`${selectedIds.length} selected`"></span>
    </div>
  </div>

  <!-- Hidden POST form (JSON + CSV + ids[] payloads) -->
  <form x-ref="bulkForm" id="bulkForm" class="hidden" method="POST" action="{{ $bulkTrashAction }}">
    @csrf
    <!-- JSON payload -->
    <input type="hidden" name="ids_json" x-ref="idsJson">
    <!-- CSV fallback -->
    <input type="hidden" name="ids_csv"  x-ref="idsCsv">
    <!-- Array fallback: populated at submit time with <input name='ids[]'> -->
    <div x-ref="idsArray"></div>
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

          <th class="px-4 py-3 text-left font-semibold">Promo Title</th>
          <th class="px-4 py-3 text-left font-semibold">Status</th>
          <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Published/Scheduled (PH)</th>
          <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Expires (PH)</th>
          <th class="px-4 py-3 text-left font-semibold">Created By</th>
          <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Created Date</th>
          <th class="px-4 py-3 text-left font-semibold">Actions</th>
        </tr>
      </thead>

      <tbody class="divide-y divide-gray-100 text-sm">
        @forelse($rows as $promo)
          @php
            $creatorName = $promo->created_by_name ?: optional($promo->creator)->name ?: 'AVLCI';
            $createdAt   = !empty($promo->created_at) ? $toPH($promo->created_at) : null;
            $createdDisp = $createdAt ? $createdAt->format('M d, Y • g:i A') : '—';

            $schedDT  = !empty($promo->scheduled_publish_date) ? $toPH($promo->scheduled_publish_date) : null;
            $pubDT    = !empty($promo->published_at)           ? $toPH($promo->published_at)           : null;
            $expDT    = !empty($promo->expires_at)             ? $toPH($promo->expires_at)             : null;
            $dispWhen = ($schedDT && $schedDT->greaterThan($now)) ? $schedDT : ($pubDT ?: $schedDT);

            $eff       = $effectiveStatus($promo);
            $publicUrl = $publicUrlForPromo($promo);
            $rowId     = (string) $promo->id;
          @endphp

          <tr class="hover:bg-gray-50" @change.capture="onRowChange($event)">
            <!-- row checkbox (one-way bound to Alpine state) -->
            <td class="px-4 py-3">
              <input
                type="checkbox"
                data-row-cb
                value="{{ $rowId }}"
                :checked="selectedIds.includes('{{ $rowId }}')"
                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                aria-label="Select promo: {{ $promo->title }}"
              />
            </td>

            <td class="px-4 py-3 font-medium text-slate-800 max-w-[480px] truncate" title="{{ $promo->title }}">
              {{ $promo->title }}
            </td>

            <td class="px-4 py-3">
              <span class="inline-flex items-center gap-1.5 text-xs font-semibold rounded-full px-2.5 py-1 border {{ $eff['class'] }}">
                @if($eff['slug']==='published') <i class="fa-regular fa-circle-check"></i>
                @elseif($eff['slug']==='scheduled') <i class="fa-regular fa-clock"></i>
                @elseif($eff['slug']==='expired') <i class="fa-regular fa-calendar-xmark"></i>
                @else <i class="fa-regular fa-file-lines"></i> @endif
                {{ $eff['label'] }}
              </span>
            </td>

            <td class="px-4 py-3 text-gray-700 whitespace-nowrap"
                @if($dispWhen) title="{{ $dispWhen->toIso8601String() }}" @endif>
              {{ $dispWhen ? $dispWhen->format('M d, Y • g:i A') : '—' }}
            </td>

            <td class="px-4 py-3 text-gray-700 whitespace-nowrap"
                @if($expDT) title="{{ $expDT->toIso8601String() }}" @endif>
              {{ $expDT ? $expDT->format('M d, Y • g:i A') : '—' }}
            </td>

            <td class="px-4 py-3 text-gray-700">{{ $creatorName }}</td>

            <td class="px-4 py-3 text-gray-700 whitespace-nowrap"
                @if($createdAt) title="{{ $createdAt->toIso8601String() }}" @endif>
              {{ $createdDisp }}
            </td>

            <td class="px-4 py-3">
              <div class="flex items-center gap-2">
                <!-- Preview -->
                <a href="{{ $publicUrl }}" target="_blank" rel="noopener"
                   class="inline-flex items-center px-2 py-1 rounded border hover:bg-gray-50"
                   title="{{ $publicUrl }}" aria-label="Preview promo">
                  <i class="fa-regular fa-eye"></i><span class="sr-only">Preview</span>
                </a>

                <!-- Edit -->
                <a href="{{ route('admin.promos.edit', $promo) }}?show=edit-promo#edit-promo"
                   class="inline-flex items-center gap-1 px-2 py-1 rounded border hover:bg-gray-50"
                   aria-label="Edit {{ $promo->title }}">
                  <i class="fa-regular fa-pen-to-square"></i><span>Edit</span>
                </a>

                <!-- Move to Trash -->
                <form method="POST" action="{{ route('admin.promos.destroy', $promo) }}" x-data>
                  @csrf @method('DELETE')
                  <button type="button"
                          class="inline-flex items-center gap-1 px-2 py-1 rounded border hover:bg-red-50 text-red-600"
                          @click="$store?.confirm
                            ? $store.confirm.ask({
                                title: 'Move promo to Trash?',
                                message: 'This promo will be soft-deleted.',
                                variant: 'danger',
                                actionLabel: 'Move to Trash',
                                onConfirm: () => $el.closest('form').submit()
                              })
                            : (confirm('Move this promo to Trash?') && $el.closest('form').submit())">
                    <i class="fa-regular fa-trash-can"></i><span>Trash</span>
                  </button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" class="px-4 py-6 text-center text-gray-600">No promos found.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @if(is_object($promos) && method_exists($promos, 'links'))
    <div class="px-4 py-3 border-t bg-gray-50">
      {{ $promos->links() }}
    </div>
  @endif
</div>
