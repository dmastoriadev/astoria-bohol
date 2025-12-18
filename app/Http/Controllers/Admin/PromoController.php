<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promo;
use App\Support\ScheduleNormalizer;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon as IlluminateCarbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Intervention\Image\ImageManagerStatic as Image;

class PromoController extends Controller
{
    private string $uiTz = 'Asia/Manila';

    /* =========================================================
     * Single-page Admin style: redirect list/create to dashboard
     * ========================================================= */
    public function index()
    {
        return redirect()
            ->route('admin.dashboard', ['show' => 'promos'])
            ->withFragment('promos');
    }

    public function create()
    {
        return redirect()
            ->route('admin.dashboard', ['show' => 'add-promo'])
            ->withFragment('add-promo');
    }

    public function edit(Promo $promo)
    {
        $promos   = Promo::latest('created_at')->paginate(15);
        $articles = collect();
        $stats    = $this->stats();
        $gallery  = $this->gatherGallery();

        return view('admin.dashboard', compact('promos', 'articles', 'stats'))
            ->with([
                'editPromo' => $promo,
                'gallery'   => $gallery, // media picker
            ]);
    }

    /* ======================
     * Create / Update Promo
     * ====================== */
    public function store(Request $request)
    {
        Log::info('PROMO STORE', [
            'route'         => 'admin.promos.store',
            'input_preview' => $request->except(['body', 'featured_image', 'featured_image_url']),
            'has_file'      => $request->hasFile('featured_image'),
            'has_url'       => (bool) $request->filled('featured_image_url'),
        ]);

        // Normalize action (draft|publish)
        $action = $this->resolveAction($request);
        $request->merge(['action' => $action]);

        $data = $this->validatePromo($request);

        // Slug — unique across active + trashed
        $baseSlug     = blank($data['slug'] ?? null) ? Str::slug($data['title']) : $data['slug'];
        $data['slug'] = $this->makeUniqueSlug($baseSlug);

        // Featured image: prefer gallery URL; else process upload to WebP
        $galleryUrl = trim((string) $request->input('featured_image_url', ''));
        if ($galleryUrl !== '') {
            $data['featured_image'] = $this->normalizeStorageValue($galleryUrl);
        } elseif ($request->hasFile('featured_image')) {
            $data['featured_image'] = $this->processUploadedImageToWebp(
                $request->file('featured_image'),
                'promos',
                $data['title'] ?? null
            );
        }

        $publishing = $action === 'publish';
        $isDraft    = $action === 'draft';

        // Conflicts (use raw posted schedule/expiry normalized to UTC)
        $nowUtc   = IlluminateCarbon::now('UTC')->startOfMinute();
        $rawSched = $this->firstFilled($request, ['scheduled_publish_date','hidden_sched','published_at','ui_scheduled_at','ui_publish_at']);
        $rawExp   = $this->firstFilled($request, ['expires_at','hidden_exp','ui_expires_at']);
        $schedUtc = $this->toUtcMinute($rawSched, $this->uiTz);
        $expUtc   = $this->toUtcMinute($rawExp,   $this->uiTz);

        $modeRaw = strtolower($request->input('status_intent', $request->input('publish_mode', 'now')));
        $mode    = in_array($modeRaw, ['now','schedule'], true) ? $modeRaw : 'now';

        if ($conflict = $this->checkScheduleConflicts($schedUtc, $expUtc, $publishing, $mode, $nowUtc)) {
            return back()
                ->withErrors($conflict)
                ->with('error', $this->conflictSummary($conflict))
                ->withInput();
        }

        // Compute schedule fields
        [$publishedAt, $scheduledAt, $expiresAt] = $this->computeScheduleFields(
            $request,
            null, null, null,
            true,  // isCreate
            $publishing
        );

        if ($isDraft) {
            $publishedAt = null;
            $scheduledAt = null;
            // $expiresAt = null; // keep expiry if you want drafts to carry a target unpublish date
        }

        $data['published_at']           = $publishedAt;
        $data['scheduled_publish_date'] = $scheduledAt;
        $data['expires_at']             = $expiresAt;
        $data['status']                 = $publishing ? Promo::STATUS_PUBLISHED : Promo::STATUS_DRAFT;

        // Audit — match model columns (created_by / created_by_name)
        if ($user = Auth::user()) {
            $data['created_by']     = $user->id ?? null;
            $data['created_by_name'] = $user->name ?? null;
        }

        $promo = new Promo($data);

        // Align created_at to publish time when immediate/backdated publish & not scheduled
        if (
            $publishing && $publishedAt && !$scheduledAt &&
            ($request->boolean('align_created_to_published_on_create') || $request->boolean('align_on_create'))
        ) {
            $promo->setCreatedAt($publishedAt);
        }

        $promo->save();

        // Flash messages
        $ph = fn (?IlluminateCarbon $dt) => $dt ? $dt->copy()->timezone($this->uiTz)->format('M d, Y • g:i A') . ' PH' : null;
        $primary = $publishing
            ? ($scheduledAt
                ? ($scheduledAt->gt($nowUtc) ? 'Promo scheduled.' : 'Promo backdated.')
                : 'Promo published.')
            : 'Promo saved as draft.';

        $info = [];
        if ($publishedAt) $info[] = 'Published at ' . $ph($publishedAt) . '.';
        if ($scheduledAt) $info[] = 'Scheduled for ' . $ph($scheduledAt) . '.';
        if ($expiresAt)   $info[] = 'Unpublishes on ' . $ph($expiresAt) . '.';

        $redir = redirect()->to(route('admin.promos.edit', $promo) . '?show=edit-promo#edit-promo')
            ->with('success', $primary);

        if (!empty($info)) {
            $redir->with('info', implode(' ', $info));
        }

        return $redir;
    }

    public function update(Request $request, Promo $promo)
    {
        Log::info('PROMO UPDATE', [
            'id'            => $promo->id,
            'input_preview' => $request->except(['body', 'featured_image', 'featured_image_url']),
            'has_file'      => $request->hasFile('featured_image'),
            'has_url'       => (bool) $request->filled('featured_image_url'),
        ]);

        $action = $this->resolveAction($request);
        $request->merge(['action' => $action]);

        $data = $this->validatePromo($request, $promo->id);

        // Slug — unique across active + trashed
        $baseSlug     = blank($data['slug'] ?? null) ? Str::slug($data['title']) : $data['slug'];
        $data['slug'] = $this->makeUniqueSlug($baseSlug, $promo->id);

        // Featured image
        $incomingUrl = trim((string) $request->input('featured_image_url', ''));
        if ($incomingUrl !== '') {
            $normalizedIncoming = $this->normalizeStorageValue($incomingUrl);
            $oldCanonical       = $this->normalizeStorageValue((string) $promo->featured_image);

            if (!empty($promo->featured_image) && $oldCanonical !== $normalizedIncoming) {
                $this->deleteStoredImage($promo->featured_image);
            }
            $data['featured_image'] = $normalizedIncoming;
        } elseif ($request->hasFile('featured_image')) {
            $newPath = $this->processUploadedImageToWebp(
                $request->file('featured_image'),
                'promos',
                $data['title'] ?? null
            );
            $this->deleteStoredImage($promo->featured_image);
            $data['featured_image'] = $newPath;
        }
        // else: keep existing

        $publishing = $action === 'publish';
        $isDraft    = $action === 'draft';

        // Keep previous for messaging
        $prevSched = $promo->scheduled_publish_date ? $promo->scheduled_publish_date->copy() : null;
        $prevPub   = $promo->published_at ? $promo->published_at->copy() : null;
        $prevExp   = $promo->expires_at ? $promo->expires_at->copy() : null;

        // Compute schedule fields
        [$publishedAt, $scheduledAt, $expiresAt] = $this->computeScheduleFields(
            $request,
            $promo->published_at ? IlluminateCarbon::instance($promo->published_at) : null,
            $promo->scheduled_publish_date ? IlluminateCarbon::instance($promo->scheduled_publish_date) : null,
            $promo->expires_at ? IlluminateCarbon::instance($promo->expires_at) : null,
            false,  // isCreate
            $publishing
        );

        if ($isDraft) {
            $publishedAt = null;
            $scheduledAt = null;
            // $expiresAt = null;
        }

        $data['published_at']           = $publishedAt;
        $data['scheduled_publish_date'] = $scheduledAt;
        $data['expires_at']             = $expiresAt;
        $data['status']                 = $publishing ? Promo::STATUS_PUBLISHED : Promo::STATUS_DRAFT;

        // Do not mutate created_by on update. Optionally refresh created_by_name if empty.
        if (blank($promo->created_by_name) && $user = Auth::user()) {
            $data['created_by_name'] = $user->name ?? null;
        }

        $promo->update($data);

        // Messages
        $nowUtc = IlluminateCarbon::now('UTC')->startOfMinute();
        $ph = fn (?IlluminateCarbon $dt) => $dt ? $dt->copy()->timezone($this->uiTz)->format('M d, Y • g:i A') . ' PH' : null;

        $newSched = $promo->scheduled_publish_date;
        $newPub   = $promo->published_at;
        $newExp   = $promo->expires_at;

        $scheduledChanged = (optional($newSched)?->toIso8601String()) !== (optional($prevSched)?->toIso8601String());
        $publishedChanged = (optional($newPub)?->toIso8601String())   !== (optional($prevPub)?->toIso8601String());
        $expiresChanged   = (optional($newExp)?->toIso8601String())   !== (optional($prevExp)?->toIso8601String());

        if ($publishing) {
            if ($newSched) {
                $primary = $newSched->gt($nowUtc)
                    ? ($scheduledChanged ? 'Promo rescheduled.' : 'Promo update scheduled.')
                    : 'Promo backdated.';
            } else {
                $primary = ($newPub && $newPub->lt($nowUtc) && $publishedChanged)
                    ? 'Promo backdated & updated.'
                    : 'Promo updated & published.';
            }
        } elseif ($isDraft) {
            $primary = 'Promo updated (saved as draft).';
        } else {
            $primary = $scheduledChanged ? 'Promo rescheduled.' : 'Promo updated.';
        }

        $info = [];
        if ($publishing && $newPub) {
            $info[] = 'Published at ' . $ph($newPub) . '.';
        }
        if ($scheduledChanged) {
            $info[] = $newSched ? ('Scheduled for ' . $ph($newSched) . '.') : 'Schedule cleared.';
        } elseif ($newSched) {
            $info[] = 'Schedule remains at ' . $ph($newSched) . '.';
        }
        if ($expiresChanged) {
            $info[] = $newExp ? ('Unpublishes on ' . $ph($newExp) . '.') : 'Unpublish date cleared.';
        } elseif ($newExp) {
            $info[] = 'Will unpublish on ' . $ph($newExp) . '.';
        }

        $redir = redirect()->to(route('admin.promos.edit', $promo) . '?show=edit-promo#edit-promo')
            ->with('success', $primary);

        if (!empty($info)) {
            $redir->with('info', implode(' ', $info));
        }

        return $redir;
    }

    /* ==================
     * Delete / Restore
     * ================== */

    /** Soft delete (Trash) where supported; otherwise force delete. */
    public function destroy(Promo $promo)
    {
        $name = trim((string)($promo->title ?? $promo->slug ?? 'Promo'));

        try {
            if ($this->promoSupportsSoftDeletes()) {
                if ($promo->trashed()) {
                    return redirect()
                        ->route('admin.dashboard', ['show' => 'promos'])
                        ->withFragment('promos')
                        ->with('info', "“{$name}” is already in the Trash.");
                }

                $promo->delete();

                // Clean stray flash keys that could retrigger modals
                session()->forget(['status','info','warning','message','flash_notification','laravel_flash']);

                return redirect()
                    ->route('admin.dashboard', ['show' => 'promos'])
                    ->withFragment('promos')
                    ->with('success', "“{$name}” moved to Trash.");
            }

            // Fallback: hard delete
            $this->deleteStoredImage($promo->featured_image);
            $promo->forceDelete();

            session()->forget(['status','info','warning','message','flash_notification','laravel_flash']);

            return redirect()
                ->route('admin.dashboard', ['show' => 'promos'])
                ->withFragment('promos')
                ->with('warning', "“{$name}” permanently deleted (soft deletes not enabled).");
        } catch (\Throwable $e) {
            Log::error('PROMO DELETE FAILED', ['id' => $promo->id ?? null, 'err' => $e->getMessage()]);
            return back()->with('error', "Failed to delete “{$name}”. Please try again.");
        }
    }

    /** Restore from Trash → ALWAYS set status=draft and clear publish/schedule (bulk or single). */
    public function restore(int $id)
    {
        if (!$this->promoSupportsSoftDeletes()) {
            return back()->with('warning', 'Restore unavailable — soft deletes are not enabled for promos.');
        }

        $promo = Promo::withTrashed()->findOrFail($id);

        if (!$promo->trashed()) {
            return back()->with('info', 'Promo is not in the Trash.');
        }

        DB::transaction(function () use ($promo) {
            $promo->restore();
            // After restoring, enforce Draft state
            $promo->forceFill([
                'status'                 => Promo::STATUS_DRAFT,
                'published_at'           => null,
                'scheduled_publish_date' => null,
                // Clear expiry to avoid confusion; comment out if you prefer to keep it
                'expires_at'             => null,
            ])->save();
        });

        return redirect()
            ->route('admin.dashboard', ['show' => 'promos'])
            ->withFragment('promos')
            ->with('success', 'Promo restored as Draft.');
    }

    /** Permanently delete (only allowed if item is in Trash when soft deletes are enabled). */
    public function forceDelete(int $id)
    {
        $promo = Promo::withTrashed()->findOrFail($id);

        if ($this->promoSupportsSoftDeletes() && !$promo->trashed()) {
            return back()->with('warning', 'You can only permanently delete items that are in the Trash.');
        }

        $this->deleteStoredImage($promo->featured_image);

        $name = trim((string)($promo->title ?? $promo->slug ?? 'Promo'));
        $promo->forceDelete();

        return redirect()
            ->route('admin.dashboard', ['show' => 'promos'])
            ->withFragment('promos')
            ->with('success', "“{$name}” permanently deleted.");
    }

    /* ===================
     * BULK Actions
     * =================== */

    /** BULK: move to Trash (soft delete) or force delete if unsupported. */
    public function bulkTrash(Request $request)
    {
        $ids = $this->parseSelectedIds($request);

        if ($ids->isEmpty()) {
            return back()->with('warning', 'No promos selected.');
        }

        $count = 0;

        if ($this->promoSupportsSoftDeletes()) {
            Promo::whereIn('id', $ids)
                ->whereNull('deleted_at')
                ->chunkById(200, function ($chunk) use (&$count) {
                    foreach ($chunk as $p) {
                        $p->delete();
                        $count++;
                    }
                });
        } else {
            Promo::whereIn('id', $ids)
                ->chunkById(200, function ($chunk) use (&$count) {
                    foreach ($chunk as $p) {
                        $this->deleteStoredImage($p->featured_image);
                        $p->forceDelete();
                        $count++;
                    }
                });
        }

        return back()->with('success', "{$count} promo(s) moved to Trash.");
    }

    /** BULK: restore from Trash → set to Draft & clear publish/schedule (also clears expiry). */
    public function bulkRestore(Request $request)
    {
        if (!$this->promoSupportsSoftDeletes()) {
            return back()->with('warning', 'Restore unavailable — soft deletes are not enabled for promos.');
        }

        $ids = $this->parseSelectedIds($request);

        if ($ids->isEmpty()) {
            return back()->with('warning', 'No promos selected.');
        }

        $restored = 0;

        Promo::onlyTrashed()
            ->whereIn('id', $ids)
            ->chunkById(200, function ($chunk) use (&$restored) {
                foreach ($chunk as $p) {
                    DB::transaction(function () use ($p) {
                        $p->restore();
                        $p->forceFill([
                            'status'                 => Promo::STATUS_DRAFT,
                            'published_at'           => null,
                            'scheduled_publish_date' => null,
                            'expires_at'             => null,
                        ])->save();
                    });
                    $restored++;
                }
            });

        return back()->with('success', "{$restored} promo(s) restored as Draft.");
    }

    /** BULK: permanently delete (only from Trash when soft deletes are enabled). */
    public function bulkForceDelete(Request $request)
    {
        $ids = $this->parseSelectedIds($request);

        if ($ids->isEmpty()) {
            return back()->with('warning', 'No promos selected.');
        }

        $count = 0;

        Promo::withTrashed()
            ->whereIn('id', $ids)
            ->chunkById(200, function ($chunk) use (&$count) {
                foreach ($chunk as $p) {
                    if ($this->promoSupportsSoftDeletes() && !$p->trashed()) {
                        continue; // only allow force delete from trash
                    }
                    $this->deleteStoredImage($p->featured_image);
                    $p->forceDelete();
                    $count++;
                }
            });

        return back()->with('success', "{$count} promo(s) permanently deleted.");
    }

    /* ===========================
     * Validation / Schedule utils
     * =========================== */

    protected function validatePromo(Request $request, $ignoreId = null): array
    {
        $action     = $request->input('action', 'draft');
        $isDraft    = $action === 'draft';
        $modeRaw    = strtolower($request->input('status_intent', $request->input('publish_mode', 'now')));
        $isSchedule = in_array($modeRaw, ['schedule'], true);

        return $request->validate([
            'title'          => ['required', 'string', 'max:200'],
            'slug'           => ['nullable', 'string', 'max:220', Rule::unique('promos', 'slug')->ignore($ignoreId)],
            'excerpt'        => ['nullable', 'string', 'max:1000'],
            'body'           => $isDraft ? ['nullable','string'] : ['required','string'],

            'featured_image'     => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,gif', 'max:4096'],
            'featured_image_url' => ['nullable', 'string', 'max:4096', 'starts_with:http://,https://,/storage/'],

            'scheduled_publish_date' => $isDraft
                ? ['nullable', 'date']
                : ($isSchedule ? ['required', 'date'] : ['nullable', 'date']),
            'expires_at'             => ['nullable', 'date'],

            'action'        => ['nullable', Rule::in(['draft', 'publish'])],
            'publish_mode'  => ['nullable', Rule::in(['now', 'schedule'])],
            'status_intent' => ['nullable', Rule::in(['now', 'schedule'])],
        ]);
    }


    protected function checkScheduleConflicts(
        ?IlluminateCarbon $schedUtc,
        ?IlluminateCarbon $expUtc,
        bool $publishing,
        string $mode,
        IlluminateCarbon $nowUtc
    ): ?array {
        $errors = [];

        if ($schedUtc && $expUtc && $expUtc->lt($schedUtc)) {
            $errors['expires_at']             = 'Unpublish time must be the same as or after the scheduled publish time.';
            $errors['scheduled_publish_date'] = 'Scheduled publish time must be the same as or before the unpublish time.';
        }

        if ($publishing) {
            $effectivePublish = $mode === 'now'
                ? $nowUtc
                : ($schedUtc ?: $nowUtc);

            if ($expUtc && $expUtc->lt($effectivePublish)) {
                $errors['expires_at'] = 'Unpublish time must be the same as or after the publish time.';
            }
        }

        return empty($errors) ? null : $errors;
    }

    protected function conflictSummary(array $fieldErrors): string
    {
        $msgs = array_values(array_unique(array_filter(array_map('strval', $fieldErrors))));
        return empty($msgs)
            ? 'Schedule conflict. Please fix the highlighted fields.'
            : 'Schedule conflict — ' . implode(' • ', $msgs);
    }

    protected function toUtcMinute(?string $value, string $tz = 'Asia/Manila'): ?IlluminateCarbon
    {
        if ($value === null || $value === '') return null;

        $dt = ScheduleNormalizer::toUtc($value, $tz);

        if ($dt instanceof IlluminateCarbon)     return $dt->copy()->startOfMinute();
        if ($dt instanceof CarbonInterface)      return IlluminateCarbon::instance($dt)->startOfMinute();
        if ($dt instanceof \DateTimeInterface)   return IlluminateCarbon::instance(\Carbon\Carbon::instance($dt))->startOfMinute();

        return IlluminateCarbon::parse($value, $tz)->timezone('UTC')->startOfMinute();
    }

    /* ==================
     * Misc helpers
     * ================== */

    private function gatherGallery(): array
    {
        $disk = Storage::disk('public');
        $dirs = ['articles', 'promos', 'tinymce', 'uploads', 'gallery'];
        $out  = [];

        foreach ($dirs as $dir) {
            try {
                foreach ($disk->files($dir) as $p) {
                    if (!preg_match('/\.(jpe?g|png|gif|webp|svg)$/i', $p)) continue;
                    $out[] = [
                        'path'  => $p,
                        'url'   => Storage::url($p),
                        'mtime' => $disk->lastModified($p),
                    ];
                }
            } catch (\Throwable $e) {
                // ignore directory errors
            }
        }

        usort($out, fn($a, $b) => $b['mtime'] <=> $a['mtime']);
        return array_slice($out, 0, 200);
    }

    private function normalizeStorageValue(string $value): string
    {
        $v = trim($value);
        if ($v === '') return $v;

        $base = rtrim(config('app.url'), '/');

        if (Str::startsWith($v, $base.'/storage/')) {
            return ltrim(Str::after($v, $base.'/storage/'), '/');
        }

        if (Str::startsWith($v, '/storage/')) {
            return ltrim(Str::after($v, '/storage/'), '/');
        }

        return $v;
    }

    protected function makeUniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $slug     = Str::slug($base) ?: Str::random(8);
        $original = $slug;
        $i        = 2;

        $query = Promo::query();
        if ($this->modelUsesSoftDeletes(Promo::class)) {
            $query = $query->withTrashed();
        }

        while (
            (clone $query)
                ->when($ignoreId, fn ($q) => $q->where('id', '<>', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $original . '-' . $i;
            $i++;
        }

        return $slug;
    }

    protected function modelUsesSoftDeletes(string $modelClass): bool
    {
        return in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive($modelClass), true);
    }

    private function promoSupportsSoftDeletes(): bool
    {
        try {
            $usesTrait = $this->modelUsesSoftDeletes(Promo::class);
            $hasColumn = Schema::hasColumn((new Promo)->getTable(), 'deleted_at');
            return $usesTrait && $hasColumn;
        } catch (\Throwable $e) {
            return false;
        }
    }

    protected function deleteStoredImage(?string $path): void
    {
        if (!$path) return;

        // Absolute URL?
        if (Str::startsWith($path, ['http://', 'https://'])) {
            $base = rtrim(config('app.url'), '/');
            if (!Str::startsWith($path, $base)) {
                return; // external host — don't delete
            }
            $path = Str::after($path, $base); // '/storage/...'
        }

        // '/storage/...'
        if (Str::startsWith($path, '/storage/')) {
            $relative = Str::after($path, '/storage/');
            if (Storage::disk('public')->exists($relative)) {
                Storage::disk('public')->delete($relative);
            }
            return;
        }

        // Relative on public disk
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    protected function processUploadedImageToWebp(UploadedFile $file, string $dir = 'promos', ?string $title = null, int $quality = 85): string
    {
        $disk = Storage::disk('public');

        // Safe filename
        $base = $title ? Str::slug($title) : pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $base = Str::slug($base) ?: 'image';
        $name = $base . '-' . IlluminateCarbon::now('UTC')->format('YmdHis') . '-' . Str::random(5) . '.webp';
        $path = trim($dir, '/') . '/' . $name;

        try {
            $img = Image::make($file->getRealPath())->orientate();

            if ($img->width() > 1920) {
                $img->resize(1920, null, function ($c) {
                    $c->aspectRatio();
                    $c->upsize();
                });
            }

            $img->encode('webp', $quality);

            $disk->put($path, (string) $img);
            return $path;
        } catch (\Throwable $e) {
            Log::warning('Image processing failed, storing original file instead', [
                'err'  => $e->getMessage(),
                'file' => $file->getClientOriginalName(),
            ]);
            return $file->store($dir, 'public');
        }
    }

    private function computeScheduleFields(
        Request $r,
        ?IlluminateCarbon $existingPublishedAt,
        ?IlluminateCarbon $existingScheduledAt,
        ?IlluminateCarbon $existingExpiresAt,
        bool $isCreate,
        bool $publishing
    ): array {
        $mode         = $r->input('publish_mode', $r->input('status_intent', 'now')); // 'now' | 'schedule'
        $nowUtcMinute = IlluminateCarbon::now('UTC')->startOfMinute();

        $publishedAt  = $existingPublishedAt;
        $scheduledAt  = $existingScheduledAt;
        $expiresAt    = $existingExpiresAt;

        $rawSched = $this->firstFilled($r, ['scheduled_publish_date','hidden_sched','published_at','ui_scheduled_at','ui_publish_at']);
        $postedScheduled = $rawSched ? $this->toUtcMinute($rawSched, $this->uiTz) : null;

        $rawExp = $this->firstFilled($r, ['expires_at','hidden_exp','ui_expires_at']);
        $postedExpires = $rawExp ? $this->toUtcMinute($rawExp, $this->uiTz) : $expiresAt;

        if ($mode === 'now') {
            if ($publishing) {
                $publishedAt = $nowUtcMinute;
            } else {
                $publishedAt = null;
            }
            $scheduledAt = null;
            $expiresAt   = $postedExpires;

        } else { // schedule
            if ($postedScheduled) {
                if ($postedScheduled->lte($nowUtcMinute)) {
                    // Backdate
                    if ($publishing) {
                        $publishedAt = $postedScheduled;
                        $scheduledAt = null;
                    } else {
                        $publishedAt = null;
                        $scheduledAt = $postedScheduled;
                    }
                    $expiresAt = $postedExpires;
                } else {
                    // Future schedule
                    $publishedAt = null;
                    $scheduledAt = $postedScheduled;
                    $expiresAt   = $postedExpires;
                }
            } else {
                // No schedule provided
                if ($publishing) {
                    $publishedAt = $nowUtcMinute;
                } else {
                    $publishedAt = null;
                }
                $scheduledAt = null;
                $expiresAt   = $postedExpires;
            }
        }

        return [$publishedAt, $scheduledAt, $expiresAt];
    }

    protected function stats(): array
    {
        $blogs = 0;
        $careers = 0;

        if (class_exists(\App\Models\Article::class)) {
            try { $blogs = \App\Models\Article::where('status', 'published')->count(); } catch (\Throwable $e) {}
        }
        if (class_exists(\App\Models\Career::class)) {
            try { $careers = \App\Models\Career::where('status', 'published')->count(); } catch (\Throwable $e) {}
        }

        $promos = Promo::where('status', Promo::STATUS_PUBLISHED)->count();

        return compact('blogs', 'careers', 'promos');
    }

    private function firstFilled(Request $r, array $keys): ?string
    {
        foreach ($keys as $k) {
            if (!$r->has($k)) continue;
            $v = trim((string)$r->input($k, ''));
            if ($v !== '') return $v;
        }
        return null;
    }

    private function resolveAction(Request $r): string
    {
        $candidates = [
            strtolower((string)$r->input('action','')),
            strtolower((string)$r->input('intent','')),
            strtolower((string)$r->input('status','')),
        ];
        foreach ($candidates as $val) {
            if (in_array($val, ['publish','draft'], true)) return $val;
        }

        // button & checkbox style fallbacks
        if (
            $r->boolean('publish') ||
            $r->has('btn_publish') || $r->has('btn_save_publish') ||
            $r->has('action_publish') || $r->has('do_publish')
        ) {
            return 'publish';
        }

        return 'draft';
    }

    /** Accept ids from: ids[], ids, ids_json (JSON array), ids_csv ("1,2,3"). */
    private function parseSelectedIds(Request $request)
    {
        $ids = collect();

        // ids[]
        if ($request->has('ids') && is_array($request->input('ids'))) {
            $ids = $ids->merge($request->input('ids', []));
        }

        // ids (scalar or array)
        if ($request->has('ids') && !is_array($request->input('ids'))) {
            $ids = $ids->merge([$request->input('ids')]);
        }

        // ids_json
        if ($request->filled('ids_json')) {
            try {
                $decoded = json_decode($request->input('ids_json'), true, 512, JSON_THROW_ON_ERROR);
                if (is_array($decoded)) {
                    $ids = $ids->merge($decoded);
                }
            } catch (\Throwable $e) {
                // ignore malformed JSON
            }
        }

        // ids_csv
        if ($request->filled('ids_csv')) {
            $csv = explode(',', (string)$request->input('ids_csv'));
            $ids = $ids->merge($csv);
        }

        // Normalize numeric IDs
        return $ids->filter(fn($v) => is_numeric($v))
            ->map(fn($v) => (int) $v)
            ->unique()
            ->values();
    }
}
