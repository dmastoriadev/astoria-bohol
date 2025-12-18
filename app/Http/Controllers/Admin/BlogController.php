<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Support\ScheduleNormalizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon as IlluminateCarbon;
use Carbon\CarbonInterface;
use Illuminate\Http\UploadedFile;
use Intervention\Image\ImageManagerStatic as Image;

class BlogController extends Controller
{
    private string $uiTz = 'Asia/Manila';

    /* ========================== Helpers ========================== */

    /**
     * Normalize a 'datetime-local' string (Asia/Manila by default) to
     * Illuminate Carbon in UTC, truncated to the minute.
     */
    private function toUtcMinute(?string $value, string $tz = 'Asia/Manila'): ?IlluminateCarbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        $dt = ScheduleNormalizer::toUtc($value, $tz);

        if ($dt instanceof IlluminateCarbon) {
            return $dt->copy()->startOfMinute();
        }
        if ($dt instanceof CarbonInterface) {
            return IlluminateCarbon::instance($dt)->startOfMinute();
        }
        if ($dt instanceof \DateTimeInterface) {
            return IlluminateCarbon::instance(\Carbon\Carbon::instance($dt))->startOfMinute();
        }

        // Fallback parse if normalizer returned something unexpected.
        return IlluminateCarbon::parse($value, $tz)->timezone('UTC')->startOfMinute();
    }

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
                // ignore missing dirs
            }
        }

        usort($out, fn($a, $b) => $b['mtime'] <=> $a['mtime']);
        return array_slice($out, 0, 200);
    }

    private function makeUniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $slug = Str::slug($base) ?: Str::random(8);
        $original = $slug;
        $i = 2;

        while (
            Article::query()->withTrashed()
                ->when($ignoreId, fn ($q) => $q->where('id', '<>', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $original . '-' . $i;
            $i++;
        }
        return $slug;
    }

    private function resolveCategory(?string $newName, ?int $existingId): ?int
    {
        if ($existingId) return $existingId;

        if ($newName) {
            $name = trim($newName);
            if ($name === '') return null;

            $slug = Str::slug($name);

            try {
                $cat = ArticleCategory::firstOrCreate(
                    ['slug' => $slug],
                    ['name' => $name]
                );
                return $cat->id;
            } catch (QueryException $e) {
                return ArticleCategory::where('slug', $slug)->value('id');
            }
        }

        return null;
    }

    private function isEmptyRichText(?string $html): bool
    {
        if ($html === null) return true;
        $plain = trim(html_entity_decode(strip_tags($html)));
        return $plain === '';
    }

    /**
     * Delete an image stored on the "public" disk when we know it's ours.
     * Supports relative disk paths, /storage/... URLs, and absolute app URLs.
     * Skips external hosts.
     */
    private function deleteStoredImage(?string $path): void
    {
        if (!$path) return;

        // Convert absolute storage URL to a relative /storage/... path
        if (Str::startsWith($path, ['http://', 'https://'])) {
            $base = rtrim(config('app.url'), '/'); // e.g., https://your-app.com
            if (Str::startsWith($path, $base)) {
                $path = Str::after($path, $base); // now '/storage/...'
            } else {
                return; // unknown host → do not delete
            }
        }

        if (Str::startsWith($path, '/storage/')) {
            // Storage::url('foo/bar.jpg') → '/storage/foo/bar.jpg' → real disk path is 'foo/bar.jpg'
            $relative = Str::after($path, '/storage/');
            if (Storage::disk('public')->exists($relative)) {
                Storage::disk('public')->delete($relative);
            }
            return;
        }

        // Already a relative 'public' disk path
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    /**
     * Process an uploaded image file → WebP (quality 85), optional max width,
     * store on the "public" disk, and return the relative path (e.g., 'articles/xxx.webp').
     * Falls back to storing the original file if processing fails.
     */
    private function processUploadedImageToWebp(UploadedFile $file, string $dir = 'articles', ?string $title = null, int $quality = 85): string
    {
        $disk = Storage::disk('public');

        // Build safe filename
        $base = $title ? Str::slug($title) : pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $base = Str::slug($base) ?: 'image';
        $name = $base . '-' . IlluminateCarbon::now('UTC')->format('YmdHis') . '-' . Str::random(5) . '.webp';
        $path = trim($dir, '/') . '/' . $name;

        try {
            $img = Image::make($file->getRealPath())->orientate();

            // Keep files light: cap width at ~1920px
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
                'dir'  => $dir,
            ]);
            // Fallback: just store the raw file (keeps original extension)
            return $file->store($dir, 'public');
        }
    }

    /**
     * Compute publish/schedule/unpublish datetimes based on intent and mode.
     * Returns [publishedAtUTC|null, scheduledAtUTC|null, expiresAtUTC|null]
     */
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

        // Normalize inputs (UI posts Asia/Manila)
        $postedScheduled = null;
        if ($r->has('scheduled_publish_date')) {
            $postedScheduled = $this->toUtcMinute($r->input('scheduled_publish_date'), $this->uiTz);
        }
        // Back-compat (“published_at” used as schedule by old views)
        if (!$postedScheduled && $mode === 'schedule' && $r->has('published_at')) {
            $postedScheduled = $this->toUtcMinute($r->input('published_at'), $this->uiTz);
        }

        $postedExpires = $r->has('expires_at')
            ? $this->toUtcMinute($r->input('expires_at'), $this->uiTz)
            : $expiresAt; // keep existing when key missing

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
                    // backdate
                    if ($publishing) {
                        $publishedAt = $postedScheduled;
                        $scheduledAt = null;
                    } else {
                        $publishedAt = null;
                        $scheduledAt = $postedScheduled;
                    }
                    $expiresAt = $postedExpires;
                } else {
                    // future schedule
                    $publishedAt = null;
                    $scheduledAt = $postedScheduled;
                    $expiresAt   = $postedExpires;
                }
            } else {
                // No schedule supplied
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

    /** Friendly PH formatter (for toasts/messages). */
    private function fmtPH(?IlluminateCarbon $dt): ?string
    {
        return $dt ? $dt->copy()->timezone($this->uiTz)->format('M d, Y • g:i A') : null;
    }

    /** Return PH+UTC strings for a datetime. */
    private function fmtBoth(?IlluminateCarbon $dt): ?array
    {
        if (!$dt) return null;
        return [
            'ph'  => $dt->copy()->timezone($this->uiTz)->format('M d, Y • g:i A'),
            'utc' => $dt->copy()->timezone('UTC')->format('M d, Y • g:i A'),
        ];
    }

    /** Build a single detail line: "Label … PH / … UTC." */
    private function fmtBothLine(string $label, ?IlluminateCarbon $dt): ?string
    {
        $b = $this->fmtBoth($dt);
        return $b ? "{$label} {$b['ph']} PH / {$b['utc']} UTC." : null;
    }

    /**
     * Flash (success/error/warning/info) or return a JSON payload with the same message.
     */
    private function flashOrJson(Request $r, string $type, string $message, string $redirectUrl, array $extra = [])
    {
        $payload = array_filter($extra, fn ($v) => $v !== null && $v !== '');

        if ($r->wantsJson()) {
            return response()->json(array_merge([
                'ok'      => $type === 'success',
                'type'    => $type,
                'message' => $message,
            ], $payload));
        }

        $redir = redirect()->to($redirectUrl)->with($type, $message);

        if (!empty($payload['info']))    { $redir->with('info',    $payload['info']); }
        if (!empty($payload['warning'])) { $redir->with('warning', $payload['warning']); }

        foreach (['article_id','status','published_at','expires_at','scheduled_publish_date'] as $k) {
            if (array_key_exists($k, $payload)) $redir->with($k, $payload[$k]);
        }

        return $redir;
    }

    /**
     * Parse ids from request: accepts 'ids_json' (JSON array) or 'ids[]'.
     * Returns a Collection<int>.
     */
    private function collectIdsFromRequest(Request $request)
    {
        $raw = $request->input('ids_json');
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            $ids = is_array($decoded) ? $decoded : [];
        } else {
            $ids = $request->input('ids', []);
        }

        return collect($ids)
            ->map(fn($v) => (int) $v)
            ->filter(fn($v) => $v > 0)
            ->unique()
            ->values();
    }

    /* ============================ CRUD =========================== */

    public function store(Request $request)
    {
        Log::info('BLOG STORE', [
            'route'         => 'admin.blogs.store',
            'input_preview' => $request->except(['body', 'featured_image']),
            'has_file'      => $request->hasFile('featured_image'),
            'has_url'       => (bool) $request->filled('featured_image_url'),
        ]);

        if ($this->isEmptyRichText($request->input('body'))) {
            $request->merge(['body' => null]);
        }

        // Intent (button) vs mode (radio)
        // Robust fallback: if action missing, default to 'draft' (safe)
        $action     = $request->input('action', 'draft');        // 'draft' | 'publish'
        $publishing = $action === 'publish';
        $isDraft    = $action === 'draft';
        $isSchedule = $request->input('publish_mode', $request->input('status_intent', 'now')) === 'schedule';
        $bodyRule   = $isDraft ? ['nullable','string'] : ['required','string'];

        try {
            // Validate; require schedule date only when publishing + choosing schedule
            $validated = $request->validate([
                'title'   => ['required', 'string', 'max:255'],
                'slug'    => ['nullable', 'string', 'max:255', 'unique:articles,slug'],
                'excerpt' => ['nullable', 'string'],
                'body'    => $bodyRule,

                'featured_image'     => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,gif', 'max:4096'],
                'featured_image_url' => ['nullable', 'string', 'max:4096', 'starts_with:http://,https://,/storage/'],
                'tags'               => ['nullable', 'string', 'max:255'],

                'article_category_id'  => [
                    'nullable','integer',
                    Rule::exists((new ArticleCategory)->getTable(), 'id'),
                ],
                'new_article_category' => ['nullable', 'string', 'max:120'],

                // Scheduling inputs (UI posts PH local)
                'publish_mode'            => ['nullable', 'in:now,schedule'],
                'scheduled_publish_date'  => $isDraft
                    ? ['nullable', 'date']
                    : ($isSchedule ? ['required', 'date'] : ['nullable', 'date']),
                // Legacy fallback if your Blade still posts this for scheduling
                'published_at'            => ['nullable', 'date'],

                // Unpublish
                'expires_at'              => ['nullable', 'date', 'after_or_equal:scheduled_publish_date'],
                // action button
                'action'                  => ['nullable', 'in:draft,publish'],
            ]);
        } catch (ValidationException $e) {
            Log::warning('BLOG STORE VALIDATION', ['errors' => $e->errors()]);
            return back()->withErrors($e->errors())->withInput()
                ->with('error', 'Please fix the errors highlighted below.');
        }

        DB::beginTransaction();
        try {
            $slug = !empty($validated['slug'])
                ? $this->makeUniqueSlug($validated['slug'])
                : $this->makeUniqueSlug($validated['title']);

            $articleCategoryId = $this->resolveCategory(
                $request->input('new_article_category'),
                $request->filled('article_category_id') ? (int) $request->input('article_category_id') : null
            );

            [$publishedAt, $scheduledAt, $expiresAt] = $this->computeScheduleFields(
                $request,
                null,
                null,
                null,
                true,
                $publishing
            );

            // If saving as draft, clear publish/schedule timestamps
            if ($isDraft) {
                $publishedAt = null;
                $scheduledAt = null;
                // $expiresAt = null; // uncomment if drafts should never have unpublish timers
            }

            $article = new Article();
            $article->title   = $validated['title'];
            $article->slug    = $slug;
            $article->excerpt = $validated['excerpt'] ?? null;
            $article->body    = $validated['body'];

            // Featured image: prefer Gallery URL; else process upload → WebP
            $galleryUrl = trim((string) $request->input('featured_image_url', ''));
            if ($galleryUrl !== '') {
                $article->featured_image = $this->normalizeStorageValue($galleryUrl);
            } elseif ($request->hasFile('featured_image')) {
                $article->featured_image = $this->processUploadedImageToWebp(
                    $request->file('featured_image'),
                    'articles',
                    $validated['title'] ?? null
                ); // returns a relative disk path already
            } else {
                $article->featured_image = null;
            }

            $article->tags = $validated['tags'] ?? null;

            // mark as published when user hits Publish (even if scheduled)
            $article->status = $publishing ? 'published' : 'draft';

            // Datetimes
            $article->published_at            = $publishedAt;
            $article->scheduled_publish_date  = $scheduledAt;
            $article->expires_at              = $expiresAt;

            $article->article_category_id = $articleCategoryId;

            // Author/creator
            $user = Auth::user();
            $article->author          = 'Astoria Plaza';
            $article->created_by      = $user?->id;
            $article->created_by_name = $user->name ?? $user->email ?? null;

            // Align created_at ONLY for immediate/backdated publish (no schedule)
            if (
                $publishing
                && $publishedAt
                && !$scheduledAt
                && $request->boolean('align_created_to_published_on_create')
            ) {
                $article->setCreatedAt($publishedAt);
            }

            $article->save();

            DB::commit();

            Log::info('BLOG STORE SAVED', [
                'id' => $article->id,
                'slug' => $article->slug,
                'published_at' => optional($article->published_at)->toIso8601String(),
                'scheduled_publish_date' => optional($article->scheduled_publish_date)->toIso8601String(),
                'expires_at' => optional($article->expires_at)->toIso8601String(),
                'status' => $article->status,
            ]);

            // Accurate messaging incl. backdating
            $nowUtcMinute = IlluminateCarbon::now('UTC')->startOfMinute();
            if ($publishing) {
                if ($scheduledAt) {
                    $msg = 'Blog scheduled.';
                } elseif ($publishedAt && $publishedAt->lt($nowUtcMinute)) {
                    $msg = 'Blog backdated.';
                } else {
                    $msg = 'Blog published.';
                }
            } else {
                $msg = 'Blog saved as draft.';
            }

            // ===== Detailed notifications (PH + UTC) =====
            $infoLines    = [];
            $warningLines = [];

            if ($publishing && $publishedAt) {
                $line = $this->fmtBothLine('Published at', $publishedAt);
                if ($line) $infoLines[] = $line;
            }
            if ($scheduledAt) {
                $line = $this->fmtBothLine('Scheduled for', $scheduledAt);
                if ($line) $infoLines[] = $line;
            }
            if ($expiresAt) {
                $line = $this->fmtBothLine('Unpublishes on', $expiresAt);
                if ($line) $warningLines[] = $line;
            }

            $redirectUrl = route('admin.blogs.edit', $article) . '?show=edit-blog#edit-blog';

            return $this->flashOrJson($request, 'success', $msg, $redirectUrl, [
                'info'                     => implode(' ', $infoLines) ?: null,
                'warning'                  => implode(' ', $warningLines) ?: null,

                // programmatic extras
                'article_id'               => $article->id,
                'status'                   => $article->status,
                'published_at'             => optional($publishedAt)->toIso8601String(),
                'scheduled_publish_date'   => optional($scheduledAt)->toIso8601String(),
                'expires_at'               => optional($expiresAt)->toIso8601String(),
                'notify' => [
                    'ph'  => [
                        'published_at'             => $publishedAt?->copy()->timezone($this->uiTz)->toIso8601String(),
                        'scheduled_publish_date'   => $scheduledAt?->copy()->timezone($this->uiTz)->toIso8601String(),
                        'expires_at'               => $expiresAt?->copy()->timezone($this->uiTz)->toIso8601String(),
                    ],
                    'utc' => [
                        'published_at'             => $publishedAt?->copy()->timezone('UTC')->toIso8601String(),
                        'scheduled_publish_date'   => $scheduledAt?->copy()->timezone('UTC')->toIso8601String(),
                        'expires_at'               => $expiresAt?->copy()->timezone('UTC')->toIso8601String(),
                    ],
                    'lines' => [
                        'info'    => $infoLines,
                        'warning' => $warningLines,
                    ],
                ],
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Blog store failed', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Could not save the blog.');
        }
    }

    public function edit(Article $article)
    {
        $articles = Article::with('creator', 'category')
            ->latest('created_at')
            ->paginate(10, ['*'], 'articles_page');

        $trashed = Article::onlyTrashed()
            ->with(['category', 'creator'])
            ->latest('deleted_at')
            ->paginate(10, ['*'], 'trash_page');

        $stats = [
            // Use the LIVE scope so scheduled/expired aren’t counted
            'blogs'   => Article::live()->count(),
            'careers' => Schema::hasTable('careers') ? DB::table('careers')->where('status', 'published')->count() : 0,
            'promos'  => Schema::hasTable('promos')  ? DB::table('promos')->where('status', 'published')->count() : 0,
        ];

        $categories = Schema::hasTable((new ArticleCategory)->getTable())
            ? ArticleCategory::orderBy('name')->get(['id','name','slug'])
            : collect();
        $gallery = $this->gatherGallery();

        return view('admin.dashboard', compact('articles', 'stats', 'categories'))
            ->with([
                'editArticle'     => $article,
                'trashedArticles' => $trashed,
                'trashed'         => $trashed,
                'gallery'         => $gallery,
            ]);
    }

    public function update(Request $request, Article $article)
    {
        Log::info('BLOG UPDATE', [
            'id'            => $article->id,
            'input_preview' => $request->except(['body', 'featured_image']),
            'has_file'      => $request->hasFile('featured_image'),
            'has_url'       => (bool) $request->filled('featured_image_url'),
        ]);

        if ($this->isEmptyRichText($request->input('body'))) {
            $request->merge(['body' => null]);
        }

        // Intent (button) vs mode (radio)
        $action     = $request->input('action', 'draft');        // 'draft' | 'publish'
        $publishing = $action === 'publish';
        $isDraft    = $action === 'draft';
        $isSchedule = $request->input('publish_mode', $request->input('status_intent', 'now')) === 'schedule';
        $bodyRule   = $isDraft ? ['nullable', 'string'] : ['required', 'string'];

        try {
            $validated = $request->validate([
                'title'   => ['required', 'string', 'max:255'],
                'slug'    => ['nullable', 'string', 'max:255', Rule::unique('articles','slug')->ignore($article->id)],
                'excerpt' => ['nullable', 'string'],
                'body'    => $bodyRule,

                'featured_image'     => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,gif', 'max:4096'],
                'featured_image_url' => ['nullable', 'string', 'max:4096', 'starts_with:http://,https://,/storage/'],
                'tags'               => ['nullable', 'string', 'max:255'],

                'article_category_id'  => [
                    'nullable','integer',
                    Rule::exists((new ArticleCategory)->getTable(), 'id'),
                ],
                'new_article_category' => ['nullable', 'string', 'max:120'],

                // Scheduling inputs
                'publish_mode'            => ['nullable', 'in:now,schedule'],
                'scheduled_publish_date'  => $isDraft
                    ? ['nullable', 'date']
                    : ($isSchedule ? ['required', 'date'] : ['nullable', 'date']),
                // Legacy fallback (if old Blade still posts this)
                'published_at'            => ['nullable', 'date'],

                'expires_at'              => ['nullable', 'date', 'after_or_equal:scheduled_publish_date'],

                'action'                  => ['nullable', 'in:draft,publish'],
            ]);
        } catch (ValidationException $e) {
            Log::warning('BLOG UPDATE VALIDATION', ['id' => $article->id, 'errors' => $e->errors()]);
            return back()->withErrors($e->errors())->withInput()
                ->with('error', 'Please fix the errors highlighted below.');
        }

        DB::beginTransaction();
        try {
            $desiredBase = $validated['slug'] ?? $article->slug ?? $validated['title'];
            $slug = $this->makeUniqueSlug($desiredBase, $article->id);

            $articleCategoryId = $this->resolveCategory(
                $request->input('new_article_category'),
                $request->filled('article_category_id')
                    ? (int) $request->input('article_category_id')
                    : $article->article_category_id
            );

            $oldScheduled = $article->scheduled_publish_date ? IlluminateCarbon::instance($article->scheduled_publish_date) : null;
            $oldExpires   = $article->expires_at ? IlluminateCarbon::instance($article->expires_at) : null;
            $oldPublished = $article->published_at ? IlluminateCarbon::instance($article->published_at) : null;

            [$publishedAt, $scheduledAt, $expiresAt] = $this->computeScheduleFields(
                $request,
                $article->published_at ? IlluminateCarbon::instance($article->published_at) : null,
                $article->scheduled_publish_date ? IlluminateCarbon::instance($article->scheduled_publish_date) : null,
                $article->expires_at ? IlluminateCarbon::instance($article->expires_at) : null,
                false,
                $publishing
            );

            // If saving as draft, clear publish/schedule timestamps
            if ($isDraft) {
                $publishedAt = null;
                $scheduledAt = null;
                // $expiresAt = null; // uncomment if drafts should never have unpublish timers
            }

            // Featured image: prefer gallery URL; else new upload → WebP; else keep existing
            $incomingUrl = trim((string) $request->input('featured_image_url', ''));
            if ($incomingUrl !== '') {
                $normalizedIncoming = $this->normalizeStorageValue($incomingUrl);
                if (!empty($article->featured_image) && $article->featured_image !== $normalizedIncoming) {
                    $this->deleteStoredImage($article->featured_image);
                }
                $article->featured_image = $normalizedIncoming;
            } elseif ($request->hasFile('featured_image')) {
                $newPath = $this->processUploadedImageToWebp(
                    $request->file('featured_image'),
                    'articles',
                    $validated['title'] ?? null
                );
                $this->deleteStoredImage($article->featured_image);
                $article->featured_image = $newPath;
            }

            $article->title   = $validated['title'];
            $article->slug    = $slug;
            $article->excerpt = $validated['excerpt'] ?? null;
            $article->body    = $validated['body'];
            $article->tags    = $validated['tags'] ?? null;

            if ($publishing) {
                $article->status = 'published';
            } elseif ($isDraft) {
                $article->status = 'draft';
            }

            $article->published_at            = $publishedAt;
            $article->scheduled_publish_date  = $scheduledAt;
            $article->expires_at              = $expiresAt;

            $article->article_category_id = $articleCategoryId;
            $article->author              = 'Astoria Plaza';

            $article->save();

            DB::commit();

            Log::info('BLOG UPDATE SAVED', [
                'id' => $article->id,
                'slug' => $article->slug,
                'published_at' => optional($article->published_at)->toIso8601String(),
                'scheduled_publish_date' => optional($article->scheduled_publish_date)->toIso8601String(),
                'expires_at' => optional($article->expires_at)->toIso8601String(),
                'status' => $article->status,
            ]);

            $scheduledChanged = (optional($scheduledAt)?->toIso8601String()) !== (optional($oldScheduled)?->toIso8601String());
            $expiresChanged   = (optional($expiresAt)?->toIso8601String())   !== (optional($oldExpires)?->toIso8601String());
            $publishedChanged = (optional($publishedAt)?->toIso8601String()) !== (optional($oldPublished)?->toIso8601String());

            // Accurate messaging incl. backdating
            $nowUtcMinute = IlluminateCarbon::now('UTC')->startOfMinute();

            if ($publishing) {
                if ($scheduledAt) {
                    $msg = $scheduledChanged ? 'Blog rescheduled.' : 'Blog update scheduled.';
                } else {
                    if ($publishedAt && $publishedAt->lt($nowUtcMinute) && $publishedChanged) {
                        $msg = ($article->wasChanged() ? 'Blog backdated & updated.' : 'Blog backdated.');
                    } else {
                        $msg = 'Blog updated & published.';
                    }
                }
            } elseif ($isDraft) {
                $msg = 'Blog updated (saved as draft).';
            } else {
                $msg = $scheduledChanged ? 'Blog rescheduled.' : 'Blog updated.';
            }

            // ===== Detailed notifications (PH + UTC) =====
            $infoLines    = [];
            $warningLines = [];

            if ($publishing && $publishedAt) {
                $line = $this->fmtBothLine('Published at', $publishedAt);
                if ($line) $infoLines[] = $line;
            }

            if ($scheduledChanged) {
                if ($scheduledAt) {
                    $line = $this->fmtBothLine('Scheduled for', $scheduledAt);
                    if ($line) $infoLines[] = $line;
                } else {
                    $infoLines[] = 'Schedule cleared.';
                }
            } elseif ($scheduledAt) {
                $line = $this->fmtBothLine('Schedule remains at', $scheduledAt);
                if ($line) $infoLines[] = $line;
            }

            if ($expiresChanged) {
                if ($expiresAt) {
                    $line = $this->fmtBothLine('Unpublishes on', $expiresAt);
                    if ($line) $warningLines[] = $line;
                } else {
                    $warningLines[] = 'Unpublish date cleared.';
                }
            } elseif ($expiresAt) {
                $line = $this->fmtBothLine('Will unpublish on', $expiresAt);
                if ($line) $warningLines[] = $line;
            }

            $redirectUrl = route('admin.blogs.edit', $article) . '?show=edit-blog#edit-blog';

            return $this->flashOrJson($request, 'success', $msg, $redirectUrl, [
                'info'                     => implode(' ', $infoLines) ?: null,
                'warning'                  => implode(' ', $warningLines) ?: null,

                'article_id'               => $article->id,
                'status'                   => $article->status,
                'published_at'             => optional($publishedAt)->toIso8601String(),
                'scheduled_publish_date'   => optional($scheduledAt)->toIso8601String(),
                'expires_at'               => optional($expiresAt)->toIso8601String(),

                'notify' => [
                    'ph'  => [
                        'published_at'             => $publishedAt?->copy()->timezone($this->uiTz)->toIso8601String(),
                        'scheduled_publish_date'   => $scheduledAt?->copy()->timezone($this->uiTz)->toIso8601String(),
                        'expires_at'               => $expiresAt?->copy()->timezone($this->uiTz)->toIso8601String(),
                    ],
                    'utc' => [
                        'published_at'             => $publishedAt?->copy()->timezone('UTC')->toIso8601String(),
                        'scheduled_publish_date'   => $scheduledAt?->copy()->timezone('UTC')->toIso8601String(),
                        'expires_at'               => $expiresAt?->copy()->timezone('UTC')->toIso8601String(),
                    ],
                    'lines' => [
                        'info'    => $infoLines,
                        'warning' => $warningLines,
                    ],
                ],
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Blog update failed', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Could not update the blog.');
        }
    }

    /* ==================== Deletes / Trash (single + bulk) ==================== */

    public function destroy(Article $article)
    {
        $title = $article->title;
        $article->delete();

        if (request()->expectsJson() || request()->wantsJson()) {
            return response()->json(['ok' => true, 'type' => 'success', 'message' => "“{$title}” moved to trash."]);
        }

        return redirect()->route('admin.dashboard', ['show' => 'blogs'])
            ->with('success', "“{$title}” moved to trash.");
    }

    /**
     * POST /admin/blogs/bulk-trash  (soft delete)
     */
    public function bulkTrash(Request $request)
    {
        $ids = $this->collectIdsFromRequest($request);

        if ($ids->isEmpty()) {
            $msg = 'No blogs selected.';
            return $request->wantsJson()
                ? response()->json(['ok' => false, 'type' => 'warning', 'message' => $msg], 422)
                : back()->with('warning', $msg);
        }

        $articles = Article::whereIn('id', $ids)->get();
        if ($articles->isEmpty()) {
            $msg = 'No matching blogs found.';
            return $request->wantsJson()
                ? response()->json(['ok' => false, 'type' => 'warning', 'message' => $msg], 404)
                : back()->with('warning', $msg);
        }

        $trashedIds = [];
        foreach ($articles as $a) {
            try {
                $a->delete(); // soft-delete
                $trashedIds[] = $a->id;
            } catch (\Throwable $e) {
                Log::warning('Bulk trash failed for blog', ['id' => $a->id, 'err' => $e->getMessage()]);
            }
        }

        $count = count($trashedIds);
        $msg = "{$count} selected blog(s) moved to Trash.";

        if ($request->wantsJson()) {
            return response()->json([
                'ok'    => true,
                'type'  => 'success',
                'count' => $count,
                'ids'   => $trashedIds,
                'message' => $msg,
            ]);
        }

        return back()->with('success', $msg);
    }

    public function restore(Request $request, int $id)
    {
        $article = Article::withTrashed()->findOrFail($id);
        $article->restore();
        $article->status                 = 'draft';
        $article->published_at           = null;
        $article->scheduled_publish_date = null; // clear stale schedules on restore
        $article->save();

        if ($request->wantsJson()) {
            return response()->json([
                'ok'      => true,
                'type'    => 'success',
                'id'      => $article->id,
                'status'  => $article->status,
                'message' => 'Blog restored as draft.',
            ]);
        }

        return back()->with('success', 'Blog restored as draft.');
    }

    public function forceDelete($id)
    {
        $article = Article::withTrashed()->findOrFail($id);
        $title   = $article->title;

        $this->deleteStoredImage($article->featured_image);
        $article->forceDelete();

        if (request()->expectsJson() || request()->wantsJson()) {
            return response()->json(['ok' => true, 'type' => 'success', 'message' => "“{$title}” permanently deleted."]);
        }

        return redirect()->route('admin.dashboard', ['show' => 'blogs'])
            ->with('success', "“{$title}” permanently deleted.");
    }

    /**
     * POST /admin/blogs/bulk-restore  (restore trashed → draft, clear schedule/publish)
     */
    public function bulkRestore(Request $request)
    {
        $ids = $this->collectIdsFromRequest($request);

        if ($ids->isEmpty()) {
            $msg = 'No blogs selected.';
            return $request->wantsJson()
                ? response()->json(['ok' => false, 'type' => 'warning', 'message' => $msg], 422)
                : back()->with('warning', $msg);
        }

        $items = Article::withTrashed()->whereIn('id', $ids)->get();
        if ($items->isEmpty()) {
            $msg = 'No matching blogs found.';
            return $request->wantsJson()
                ? response()->json(['ok' => false, 'type' => 'warning', 'message' => $msg], 404)
                : back()->with('warning', $msg);
        }

        $restored = [];
        foreach ($items as $a) {
            if (!$a->trashed()) continue;
            try {
                $a->restore();
                $a->status                 = 'draft';
                $a->published_at           = null;
                $a->scheduled_publish_date = null;
                $a->save();
                $restored[] = $a->id;
            } catch (\Throwable $e) {
                Log::warning('Bulk restore failed for blog', ['id' => $a->id, 'err' => $e->getMessage()]);
            }
        }

        $count = count($restored);
        $msg = "{$count} selected blog(s) restored as draft.";

        if ($request->wantsJson()) {
            return response()->json([
                'ok'    => true,
                'type'  => 'success',
                'count' => $count,
                'ids'   => $restored,
                'message' => $msg,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * POST /admin/blogs/bulk-force  (permanent delete selected trashed)
     */
    public function bulkForceDelete(Request $request)
    {
        $ids = $this->collectIdsFromRequest($request);

        if ($ids->isEmpty()) {
            $msg = 'No blogs selected.';
            return $request->wantsJson()
                ? response()->json(['ok' => false, 'type' => 'warning', 'message' => $msg], 422)
                : back()->with('warning', $msg);
        }

        $items = Article::withTrashed()->whereIn('id', $ids)->get();
        if ($items->isEmpty()) {
            $msg = 'No matching blogs found.';
            return $request->wantsJson()
                ? response()->json(['ok' => false, 'type' => 'warning', 'message' => $msg], 404)
                : back()->with('warning', $msg);
        }

        $deleted = [];
        foreach ($items as $a) {
            if (!$a->trashed()) continue; // safety: only permanently delete trashed rows
            try {
                $this->deleteStoredImage($a->featured_image);
                $a->forceDelete();
                $deleted[] = $a->id;
            } catch (\Throwable $e) {
                Log::warning('Bulk force delete failed for blog', ['id' => $a->id, 'err' => $e->getMessage()]);
            }
        }

        $count = count($deleted);
        $msg = "{$count} selected blog(s) permanently deleted.";

        if ($request->wantsJson()) {
            return response()->json([
                'ok'    => true,
                'type'  => 'success',
                'count' => $count,
                'ids'   => $deleted,
                'message' => $msg,
            ]);
        }

        return back()->with('success', $msg);
    }

    /* ================= Category manager (AJAX) ================= */

    public function storeCategory(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:120']]);

        $name = trim($data['name']);
        $slug = Str::slug($name);

        try {
            $cat = ArticleCategory::firstOrCreate(['slug' => $slug], ['name' => $name]);
        } catch (QueryException $e) {
            $cat = ArticleCategory::where('slug', $slug)->firstOrFail();
        }

        if ($request->expectsJson()) {
            return response()->json([
                'id'   => $cat->id,
                'name' => $cat->name,
                'slug' => $cat->slug,
            ], 201);
        }

        return back()->with('success', "Category “{$cat->name}” added.");
    }

    public function destroyCategory(ArticleCategory $category)
    {
        Article::where('article_category_id', $category->id)->update(['article_category_id' => null]);

        $name = $category->name;
        $category->delete();

        if (request()->expectsJson()) {
            return response()->json(['ok' => true, 'deleted' => $name]);
        }

        return back()->with('success', "Category “{$name}” deleted.");
    }

    /* =================== TinyMCE image upload ================== */

    public function tinymce(Request $request)
    {
        $request->validate([
            'file' => 'required|image|mimes:jpeg,jpg,png,webp,gif|max:4096',
        ]);

        // process upload → WebP (fallback to original if processing fails)
        $stored = $this->processUploadedImageToWebp($request->file('file'), 'tinymce', null, 85);

        $publicUrl = Storage::url($stored);
        return response()->json([
            'location' => $publicUrl,
            'url'      => $publicUrl, // extra key some plugins expect
        ]);
    }

    private function normalizeStorageValue(string $value): string
    {
        $v = trim($value);
        if ($v === '') return $v;

        $base = rtrim(config('app.url'), '/');

        // Full app URL → relative storage path
        if (Str::startsWith($v, $base.'/storage/')) {
            return ltrim(Str::after($v, $base.'/storage/'), '/');
        }

        // Already a /storage/... path
        if (Str::startsWith($v, '/storage/')) {
            return ltrim(Str::after($v, '/storage/'), '/');
        }

        return $v; // external URL or already-relative disk path
    }
}
