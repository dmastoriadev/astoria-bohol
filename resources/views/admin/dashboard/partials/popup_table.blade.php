{{-- resources/views/admin/dashboard/partials/popup_table.blade.php --}}
@php
  use Illuminate\Support\Carbon;

  /** @var \Illuminate\Database\Eloquent\Collection|\Illuminate\Contracts\Pagination\Paginator|\App\Models\Popup[] $popups */

  $tz  = 'Asia/Manila';
  $now = Carbon::now($tz);

  $toPH = function ($val) use ($tz) {
      if (empty($val)) return null;
      if ($val instanceof \Carbon\Carbon) return $val->copy()->timezone($tz);
      return Carbon::parse($val, 'UTC')->timezone($tz);
  };

  // Normalize items (support paginator or plain collection)
  $rawItems = ($popups instanceof \Illuminate\Contracts\Pagination\Paginator)
      ? collect($popups->items())
      : collect($popups);
@endphp

<div class="space-y-3">
  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50 text-sm uppercase text-gray-500">
        <tr>
          <th class="px-4 py-3 text-left font-semibold">Title</th>
          <th class="px-4 py-3 text-left font-semibold">Author</th>
          <th class="px-4 py-3 text-left font-semibold">Triggers</th>
          <th class="px-4 py-3 text-left font-semibold">Target Scope</th>
          <th class="px-4 py-3 text-left font-semibold">Click Class</th>
          <th class="px-4 py-3 text-left font-semibold">Status</th>
          <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Created (PH)</th>
          <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Updated (PH)</th>
          <th class="px-4 py-3 text-left font-semibold">Actions</th>
        </tr>
      </thead>

      <tbody class="divide-y divide-gray-100 text-base">
        @forelse($rawItems as $popup)
          @php
            /** @var \App\Models\Popup $popup */

            // Triggers summary
            $triggers = [];
            if ($popup->trigger_on_click) {
              $triggers[] = 'Click';
            }
            if ($popup->trigger_on_load) {
              $sec = $popup->trigger_load_delay_seconds ?? 0;
              $triggers[] = 'Load' . ($sec ? " ({$sec}s)" : '');
            }
            if ($popup->trigger_on_scroll && $popup->trigger_scroll_percent) {
              $dir = $popup->trigger_scroll_direction ?: 'down';
              $pct = $popup->trigger_scroll_percent;
              $triggers[] = "Scroll {$dir} {$pct}%";
            }
            $triggersStr = $triggers ? implode(', ', $triggers) : '—';

            // Target scope label
            $scopeLabel = match($popup->target_scope) {
              'include' => 'Only listed paths',
              'exclude' => 'All except listed',
              default   => 'All pages',
            };

            // Click class display
            $clickClass = $popup->trigger_on_click
              ? ($popup->click_class ?: 'js-popup-' . $popup->id)
              : null;

            // Author name (expected to be provided by model/accessor or column)
            $authorName = $popup->author_name ?? '—';

            // Status chip using status accessor (active | draft | inactive)
            $statusSlug = $popup->status ?? 'inactive';
            switch ($statusSlug) {
              case 'active':
                $statusLabel = 'Active (live)';
                $statusClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                $statusIcon  = 'fa-regular fa-circle-check';
                break;

              case 'draft':
                $statusLabel = 'Draft (not ready)';
                $statusClass = 'bg-amber-50 text-amber-700 border-amber-200';
                $statusIcon  = 'fa-regular fa-file-lines';
                break;

              default:
                $statusLabel = 'Inactive';
                $statusClass = 'bg-slate-50 text-slate-600 border-slate-200';
                $statusIcon  = 'fa-regular fa-circle';
                break;
            }

            $createdAt = !empty($popup->created_at) ? $toPH($popup->created_at) : null;
            $updatedAt = !empty($popup->updated_at) ? $toPH($popup->updated_at) : null;

            $createdDisp = $createdAt ? $createdAt->format('M d, Y • g:i A') : '—';
            $updatedDisp = $updatedAt ? $updatedAt->format('M d, Y • g:i A') : '—';
          @endphp

          <tr class="hover:bg-gray-50">
            {{-- Title --}}
            <td class="px-4 py-3 font-medium text-slate-800 max-w-[320px] truncate" title="{{ $popup->title }}">
              {{ $popup->title }}
            </td>

            {{-- Author --}}
            <td class="px-4 py-3 text-gray-700 text-sm max-w-[220px] truncate" title="{{ $authorName }}">
              {{ $authorName }}
            </td>

            {{-- Triggers --}}
            <td class="px-4 py-3 text-gray-700 text-sm">
              {{ $triggersStr }}
            </td>

            {{-- Scope --}}
            <td class="px-4 py-3 text-gray-700 text-sm">
              {{ $scopeLabel }}
            </td>

            {{-- Click class --}}
            <td class="px-4 py-3 text-sm">
              @if($clickClass)
                <code class="px-1.5 py-0.5 rounded bg-slate-100 border border-slate-200">
                  {{ $clickClass }}
                </code>
              @else
                —
              @endif
            </td>

            {{-- Status --}}
            <td class="px-4 py-3">
              <span class="inline-flex items-center gap-1.5 text-sm font-semibold rounded-full px-2.5 py-1 border {{ $statusClass }}">
                <i class="{{ $statusIcon }}"></i>
                {{ $statusLabel }}
              </span>
            </td>

            {{-- Created --}}
            <td class="px-4 py-3 text-gray-700 whitespace-nowrap"
                @if($createdAt) title="{{ $createdAt->toIso8601String() }}" @endif>
              {{ $createdDisp }}
            </td>

            {{-- Updated --}}
            <td class="px-4 py-3 text-gray-700 whitespace-nowrap"
                @if($updatedAt) title="{{ $updatedAt->toIso8601String() }}" @endif>
              {{ $updatedDisp }}
            </td>

            {{-- Actions --}}
            <td class="px-4 py-3">
              <div class="flex items-center gap-2">
                <a href="{{ route('admin.popups.edit', $popup) }}"
                   class="inline-flex items-center gap-1 px-2 py-1 rounded border hover:bg-gray-50 text-sm"
                   aria-label="Edit {{ $popup->title }}">
                  <i class="fa-regular fa-pen-to-square"></i><span>Edit</span>
                </a>

                <form method="POST" action="{{ route('admin.popups.destroy', $popup) }}" x-data>
                  @csrf
                  @method('DELETE')
                  <button
                    type="button"
                    class="inline-flex items-center gap-1 px-2 py-1 rounded border hover:bg-red-50 text-red-600 text-sm"
                    @click="$store?.confirm
                      ? $store.confirm.ask({
                          title: 'Delete this pop-up?',
                          message: 'This will permanently delete the pop-up.',
                          variant: 'danger',
                          actionLabel: 'Delete',
                          onConfirm: () => $el.closest('form').submit()
                        })
                      : (confirm('Delete this pop-up?') && $el.closest('form').submit())"
                  >
                    <i class="fa-regular fa-trash-can"></i><span>Delete</span>
                  </button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="9" class="px-4 py-6 text-center text-gray-600">
              No pop-ups found.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @if(method_exists($popups, 'links'))
    <div class="px-4 py-3 border-t bg-gray-50">
      {{ $popups->links() }}
    </div>
  @endif
</div>
