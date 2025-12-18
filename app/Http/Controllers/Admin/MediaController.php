<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    /**
     * Library page.
     */
    public function index()
    {
        return view('admin.media.index');
    }

    /**
     * Return a JSON list of files from the public disk under allowed roots.
     */
    public function list(Request $request)
    {
        $disk = Storage::disk('public');

        // Folders under storage/app/public/*
        $roots = [
            'uploads',   // canonical
            'articles',  // optional legacy
            'promos',
            'images',    // include only if you keep images here
        ];

        $files = [];
        foreach ($roots as $root) {
            if (!$disk->exists($root)) {
                continue;
            }

            foreach ($disk->allFiles($root) as $path) {
                // skip hidden files + sidecar meta files
                if (
                    Str::startsWith(basename($path), '.') ||
                    Str::endsWith($path, '.meta.json')
                ) {
                    continue;
                }

                $mime    = $disk->mimeType($path) ?: 'application/octet-stream';
                $isImage = Str::startsWith($mime, 'image/');
                $url     = $disk->url($path);

                // read sidecar meta (if present)
                $meta = $this->readMeta($disk, $path);

                $files[] = [
                    'name'  => basename($path),
                    'path'  => $path,             // relative to public disk root (e.g. "uploads/2025/10/foo.jpg")
                    'url'   => $url,              // e.g. /storage/uploads/...
                    'thumb' => $isImage ? $url : null,
                    'mime'  => $mime,
                    'size'  => $disk->size($path),
                    'mtime' => $disk->lastModified($path), // seconds
                    'alt'   => $meta['alt'] ?? null,
                ];
            }
        }

        // newest first
        usort($files, fn ($a, $b) => ($b['mtime'] ?? 0) <=> ($a['mtime'] ?? 0));

        return response()
            ->json(['files' => $files])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    /**
     * Handle upload to storage/app/public/uploads/YYYY/MM/* (max 10MB).
     * Optionally accepts "alt" and stores it as sidecar meta.
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file' => [
                'required', 'file', 'max:10240',
                'mimetypes:image/jpeg,image/png,image/gif,image/webp,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ],
        ]);

        $disk = Storage::disk('public');

        // store under storage/app/public/uploads/YYYY/MM/*
        $dir  = 'uploads/' . now('Asia/Manila')->format('Y/m');
        $orig = $request->file('file')->getClientOriginalName();
        $ext  = strtolower($request->file('file')->getClientOriginalExtension());
        $base = pathinfo($orig, PATHINFO_FILENAME);
        $safe = Str::slug(Str::limit($base, 80, '')) . '-' . Str::random(6) . '.' . $ext;

        $stored = $request->file('file')->storeAs($dir, $safe, 'public');

        $url  = $disk->url($stored);
        $mime = $disk->mimeType($stored) ?: 'application/octet-stream';

        // optional: persist alt meta at upload time
        if ($request->filled('alt')) {
            $this->writeMeta($disk, $stored, ['alt' => (string) $request->input('alt')]);
        }

        return response()->json([
            'name'  => basename($stored),
            'path'  => $stored,
            'url'   => $url,                                   // /storage/uploads/...
            'thumb' => Str::startsWith($mime, 'image/') ? $url : null,
            'mime'  => $mime,
            'alt'   => $request->input('alt'),                 // echo back if provided
        ], 201);
    }

    /**
     * (Optional) Old-style destroy by path param, keeps compatibility if you still route to it.
     * Not used by the new UI but harmless to keep.
     */
    public function destroy(string $path)
    {
        $disk = Storage::disk('public');

        // sanitize user-supplied path (must be relative to public disk)
        $path = ltrim(str_replace(['..', '\\'], '', $path), '/');

        if (!$path || !$disk->exists($path)) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $disk->delete($path);
        // also remove sidecar meta
        $disk->delete($this->metaPath($path));

        return response()->json(['message' => 'Deleted']);
    }

    /**
     * Permanent delete via form data:
     * Accepts any of: id / uuid (DB-backed), or path / url (filesystem-only).
     * Returns 204 No Content on success.
     */
    public function delete(Request $request)
    {
        // If you use policies, you can re-enable this:
        // $this->authorize('delete-media');

        $id   = $request->input('id') ?? $request->input('uuid');
        $path = $request->input('path');
        $url  = $request->input('url');

        // ---- Branch 1: DB-backed deletion (if you have an \App\Models\Media)
        if ($id && class_exists(\App\Models\Media::class)) {
            $m = \App\Models\Media::withTrashed()->findOrFail($id);

            $diskName  = $m->disk ?? 'public';
            $diskFs    = Storage::disk($diskName);
            $storePath = ltrim((string)($m->path ?? $m->filepath ?? ''), '/');

            if ($storePath !== '') {
                $diskFs->delete($storePath);
                // if it's on 'public', also drop any sidecar (harmless if none)
                if ($diskName === 'public') {
                    $diskFs->delete($this->metaPath($storePath));
                }
            }

            // PERMANENT delete if model uses SoftDeletes; else regular delete is already permanent
            if (method_exists($m, 'forceDelete')) {
                $m->forceDelete();
            } else {
                $m->delete();
            }

            return response()->noContent(); // 204
        }

        // ---- Branch 2: Filesystem-only deletion by path or url
        $publicPath = $path ?: (parse_url($url ?? '', PHP_URL_PATH) ?: '');

        // Normalize "/storage/foo" or "public/foo" to disk('public') relative path "foo"
        $publicPath = ltrim($publicPath, '/');
        $publicPath = preg_replace('#^storage/#', '', $publicPath);
        $publicPath = preg_replace('#^public/#',  '', $publicPath);

        if ($publicPath === '') {
            return response()->json(['message' => 'No valid identifier received (id/uuid/path/url).'], 422);
        }

        $disk = Storage::disk('public');
        $disk->delete($publicPath);
        $disk->delete($this->metaPath($publicPath));

        // If you keep DB rows keyed by path, you can remove them here:
        // if (class_exists(\App\Models\Media::class)) {
        //     \App\Models\Media::where('path', $publicPath)->forceDelete();
        // }

        return response()->noContent(); // 204
    }

    /**
     * Rename/move and/or update metadata.
     *
     * Accepts:
     *  - id / uuid (optional, if you have a DB model)
     *  - path / url (filesystem-only)
     *  - name: desired full file name (we preserve the current extension)
     *  - rename: "1" or "true" if you want to physically move/rename
     *  - target_name: sanitized filename from FE (same extension), used when rename=1
     *  - alt: optional (sidecar meta for FS branch, DB field for model branch)
     *
     * Returns JSON with the canonical name/url/thumb actually saved.
     */
    public function update(Request $request)
    {
        // Optional: $this->authorize('update-media');

        $disk = Storage::disk('public');

        // Identify the file (DB-backed or filesystem)
        $id   = $request->input('id') ?? $request->input('uuid');
        $path = $request->input('path');
        $url  = $request->input('url');

        // ----- Branch 1: DB-backed media (if you have a model)
        if ($id && class_exists(\App\Models\Media::class)) {
            /** @var \App\Models\Media $m */
            $m = \App\Models\Media::query()->findOrFail($id);
            $mediaDisk   = $m->disk ?: 'public';
            $mediaDiskFs = Storage::disk($mediaDisk);

            $current = ltrim((string)($m->path ?? ''), '/');
            if ($current === '' || !$mediaDiskFs->exists($current)) {
                return response()->json(['message' => 'Current file not found.'], 404);
            }

            // Alt text (only if client sends it)
            if ($request->has('alt')) {
                $m->alt = $request->string('alt')->toString();
            }

            $newPath = $current;

            // Physical rename if requested
            if ($request->boolean('rename')) {
                $targetName = (string) $request->input('target_name', $request->input('name', ''));
                // Preserve current extension if FE forgot it
                if (!str_contains($targetName, '.')) {
                    $targetName .= '.' . pathinfo($current, PATHINFO_EXTENSION);
                }
                $dir     = trim(dirname($current), '/');
                $dir     = $dir === '.' ? '' : $dir;
                $newPath = ($dir ? $dir.'/' : '') . $targetName;

                if ($newPath !== $current) {
                    if ($mediaDiskFs->exists($newPath)) {
                        $newPath = $this->uniquePath($newPath);
                    }
                    // Ensure dir exists (moving within same dir usually OK)
                    if ($dir) {
                        $mediaDiskFs->makeDirectory($dir);
                    }
                    $mediaDiskFs->move($current, $newPath);
                    $m->path = $newPath;
                }
            }

            // Always store display name
            if ($request->filled('name')) {
                $m->name = $request->string('name')->toString();
            }
            $m->save();

            $finalUrl  = $mediaDiskFs->url($m->path);
            $mime      = $mediaDiskFs->mimeType($m->path) ?: 'application/octet-stream';
            $isImage   = str_starts_with($mime, 'image/');

            return response()->json([
                'name'  => basename($m->path),
                'path'  => $m->path,
                'url'   => $finalUrl,
                'thumb' => $isImage ? $finalUrl : null,
                'alt'   => $m->alt ?? null,
            ]);
        }

        // ----- Branch 2: Filesystem-only (your current setup)
        // Derive current public-disk path from path or url
        $current = ltrim((string) $path, '/');
        if (!$current && $url) {
            $p = parse_url($url, PHP_URL_PATH) ?: '';
            $p = ltrim($p, '/');
            $p = preg_replace('#^storage/#', '', $p);
            $p = preg_replace('#^public/#',  '', $p);
            $current = $p;
        }
        if ($current === '' || !$disk->exists($current)) {
            return response()->json(['message' => 'Current file not found.'], 404);
        }

        // Alt text (will be persisted to a sidecar meta file)
        $alt = $request->exists('alt') ? (string) $request->input('alt') : null;

        $newPath = $current;

        // Physical rename/move if requested (and move sidecar if present)
        if ($request->boolean('rename')) {
            $targetName = (string) $request->input('target_name', $request->input('name', ''));
            // Preserve extension if FE omitted it
            if (!str_contains($targetName, '.')) {
                $targetName .= '.' . pathinfo($current, PATHINFO_EXTENSION);
            }
            $dir     = trim(dirname($current), '/');
            $dir     = $dir === '.' ? '' : $dir;
            $newPath = ($dir ? $dir.'/' : '') . $targetName;

            if ($newPath !== $current) {
                if ($disk->exists($newPath)) {
                    $newPath = $this->uniquePath($newPath);
                }
                if ($dir) {
                    $disk->makeDirectory($dir);
                }
                $disk->move($current, $newPath);

                // move sidecar meta if it exists
                $oldMeta = $this->metaPath($current);
                $newMeta = $this->metaPath($newPath);
                $metaDir = trim(dirname($newMeta), '/');
                if ($metaDir && $metaDir !== '.') {
                    $disk->makeDirectory($metaDir);
                }
                if ($disk->exists($oldMeta)) {
                    $disk->move($oldMeta, $newMeta);
                }
            }
        }

        // Persist/merge meta (alt) against *new* path
        if ($alt !== null) {
            $meta = $this->readMeta($disk, $newPath);
            $meta['alt'] = $alt;
            $this->writeMeta($disk, $newPath, $meta);
        }

        // Build response using the *new* path
        $finalUrl = $disk->url($newPath);
        $mime     = $disk->mimeType($newPath) ?: 'application/octet-stream';
        $isImage  = str_starts_with($mime, 'image/');

        return response()->json([
            'name'  => basename($newPath),
            'path'  => $newPath,
            'url'   => $finalUrl,
            'thumb' => $isImage ? $finalUrl : null,
            'alt'   => $alt,
        ]);
    }

    /**
     * Ensure a unique path by adding -1, -2, ... suffix before the extension.
     */
    protected function uniquePath(string $path): string
    {
        $disk = Storage::disk('public');
        if (!$disk->exists($path)) {
            return $path;
        }

        $dir  = Str::of($path)->beforeLast('/');
        $file = Str::of($path)->afterLast('/');
        $base = Str::of($file)->beforeLast('.');
        $ext  = Str::of($file)->afterLast('.');

        $i = 1;
        do {
            $candidate = ($dir->isEmpty() ? '' : (string) $dir . '/') . (string) $base . '-' . $i . ($ext->isEmpty() ? '' : '.' . $ext);
            if (!$disk->exists($candidate)) {
                return $candidate;
            }
            $i++;
        } while ($i < 5000);

        // Last-resort fallback
        return ($dir->isEmpty() ? '' : (string) $dir . '/') . (string) $base . '-' . uniqid() . ($ext->isEmpty() ? '' : '.' . $ext);
    }

    /**
     * Sidecar meta path for a given public-disk file (e.g. "uploads/2025/10/foo.jpg.meta.json").
     */
    protected function metaPath(string $filePath): string
    {
        return rtrim($filePath, '/') . '.meta.json';
    }

    /**
     * Read meta (returns [] if none). $disk is a Filesystem for the public disk.
     */
    protected function readMeta($disk, string $filePath): array
    {
        $metaPath = $this->metaPath($filePath);
        if (!$disk->exists($metaPath)) {
            return [];
        }
        try {
            $raw = $disk->get($metaPath);
            $arr = json_decode($raw, true, flags: JSON_THROW_ON_ERROR);
            return is_array($arr) ? $arr : [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Write meta (merges/overwrites fully).
     */
    protected function writeMeta($disk, string $filePath, array $data): void
    {
        $metaPath = $this->metaPath($filePath);
        $dir = trim(dirname($metaPath), '/');
        if ($dir && $dir !== '.') {
            $disk->makeDirectory($dir);
        }
        $disk->put($metaPath, json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
}
