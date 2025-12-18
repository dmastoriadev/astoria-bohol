<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Popup;
use Illuminate\Http\Request;

class PopupController extends Controller
{
    /**
     * Pop-up Manager list (with basic filters).
     *
     * Route: admin.popups.index   (plural, from Route::resource('popups', ...))
     * View : resources/views/admin/popup/index.blade.php
     */
    public function index(Request $request)
    {
        // null | active | draft
        $status = $request->input('status');
        $scope  = $request->input('scope');    // null | all | include | exclude
        $q      = trim((string) $request->input('q', ''));

        $query = Popup::query();

        // Filter: status (driven by is_active + is_draft)
        if ($status === 'active') {
            $query->where('is_active', true)
                  ->where('is_draft', false);
        } elseif ($status === 'draft') {
            $query->where('is_draft', true);
        }

        // Filter: target scope
        if (in_array($scope, ['all', 'include', 'exclude'], true)) {
            if ($scope !== 'all') {
                $query->where('target_scope', $scope);
            }
        }

        // Filter: simple search
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('title', 'like', '%' . $q . '%')
                    ->orWhere('description', 'like', '%' . $q . '%')
                    ->orWhere('image_path', 'like', '%' . $q . '%');
            });
        }

        $popups = $query
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        // Counts for filter badges (NO INACTIVE)
        $counts = [
            'all'    => Popup::count(),
            'active' => Popup::where('is_active', true)->where('is_draft', false)->count(),
            'draft'  => Popup::where('is_draft', true)->count(),
        ];

        return view('admin.popup.index', compact('popups', 'status', 'scope', 'q', 'counts'));
    }

    /**
     * Show the "Add Pop-up" page.
     *
     * Route: admin.popups.create   (plural route name)
     * View : resources/views/admin/popup/create.blade.php
     */
    public function create()
    {
        $editing    = false;
        $current    = null;
        $showHelper = true;

        return view('admin.popup.create', compact('editing', 'current', 'showHelper'));
    }

    /**
     * Store a new pop-up.
     *
     * NEW:
     *  - Active (live)        → popup_status = "new"   → "Pop-up has been published"
     *  - Draft (not ready)    → popup_status = "draft" → "Pop-up has been drafted"
     *
     * Route: admin.popups.store
     */
    public function store(Request $request)
    {
        $data  = $this->validatedData($request);
        $popup = Popup::create($data);

        $statusCode = $popup->is_draft ? 'draft' : 'new';

        return redirect()
            ->route('admin.popups.edit', $popup)
            ->with('popup_status', $statusCode);
    }

    /**
     * Show the "Edit Pop-up" page.
     *
     * Route: admin.popups.edit
     * View : re-uses resources/views/admin/popup/create.blade.php
     */
    public function edit(Popup $popup)
    {
        $editing    = true;
        $current    = $popup;
        $showHelper = true;

        // Re-use the same form view, driven by $editing + $current
        return view('admin.popup.create', compact('editing', 'current', 'showHelper'));
    }

    /**
     * Update an existing pop-up.
     *
     * EDIT:
     *  - Saved as draft                 → popup_status = "draft"
     *  - Was not active, becomes active → popup_status = "new"   (published)
     *  - Active → Active (changed)      → popup_status = "edit"  (edited)
     *
     * Route: admin.popups.update
     */
    public function update(Request $request, Popup $popup)
    {
        $wasActive = (bool) $popup->is_active;
        $wasDraft  = (bool) $popup->is_draft;

        $data = $this->validatedData($request);
        $popup->update($data);

        if ($popup->is_draft) {
            $statusCode = 'draft';
        } elseif (! $wasActive && $popup->is_active) {
            $statusCode = 'new';   // published now
        } else {
            $statusCode = 'edit';  // just edited
        }

        return redirect()
            ->route('admin.popups.edit', $popup)
            ->with('popup_status', $statusCode);
    }

    /**
     * Delete a pop-up.
     *
     * DELETE:
     *  - popup_status = "delete" → "Pop-up has been permanently deleted"
     *
     * Route: admin.popups.destroy
     */
    public function destroy(Popup $popup)
    {
        $popup->delete();

        return redirect()
            ->route('admin.popups.index')
            ->with('popup_status', 'delete');
    }

    /**
     * Validate and normalise request data for create/update.
     *
     * The form sends:
     *  - status radios (active/draft)
     *  - trigger checkboxes
     *  - targeting fields
     */
    protected function validatedData(Request $request): array
    {
        $data = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],

            // primary image
            'image_path'  => ['nullable', 'string', 'max:500'],

            // extra images
            'image_gallery'   => ['nullable', 'array'],
            'image_gallery.*' => ['nullable', 'string', 'max:500'],

            'cta1_label'  => ['nullable', 'string', 'max:100'],
            'cta1_url'    => ['nullable', 'string', 'max:500'],
            'cta2_label'  => ['nullable', 'string', 'max:100'],
            'cta2_url'    => ['nullable', 'string', 'max:500'],
            'cta3_label'  => ['nullable', 'string', 'max:100'],
            'cta3_url'    => ['nullable', 'string', 'max:500'],

            'trigger_on_click'           => ['sometimes', 'boolean'],
            'trigger_click_class'        => ['nullable', 'string', 'max:100'],
            'trigger_on_load'            => ['sometimes', 'boolean'],
            'trigger_load_delay_seconds' => ['nullable', 'integer', 'min:0', 'max:600'],
            'trigger_on_scroll'          => ['sometimes', 'boolean'],
            'trigger_scroll_direction'   => ['nullable', 'in:up,down'],
            'trigger_scroll_percent'     => ['nullable', 'integer', 'in:25,50,75'],

            'target_scope' => ['required', 'in:all,include,exclude'],
            'target_paths' => ['nullable', 'string'],

            'is_active' => ['sometimes', 'boolean'],
            'is_draft'  => ['sometimes', 'boolean'],
        ]);

        // Normalise booleans (checkboxes)
        $data['trigger_on_click']  = $request->boolean('trigger_on_click');
        $data['trigger_on_load']   = $request->boolean('trigger_on_load');
        $data['trigger_on_scroll'] = $request->boolean('trigger_on_scroll');
        $data['is_active']         = $request->boolean('is_active', true);
        $data['is_draft']          = $request->boolean('is_draft', false);

        // Clean up image_gallery array
        $gallery = collect($data['image_gallery'] ?? [])
            ->map(fn ($url) => trim((string) $url))
            ->filter()
            ->values()
            ->all();

        $data['image_gallery'] = $gallery ?: null;

        // Safety fallback for target_scope
        if (! in_array($data['target_scope'] ?? 'all', ['all', 'include', 'exclude'], true)) {
            $data['target_scope'] = 'all';
        }

        return $data;
    }

}
