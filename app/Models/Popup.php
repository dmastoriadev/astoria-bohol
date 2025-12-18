<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class Popup extends Model
{
    protected $fillable = [
        'title',
        'description',
        'image_path',
        'image_gallery',      // JSON/Text array of extra images
        'cta1_label', 'cta1_url',
        'cta2_label', 'cta2_url',
        'cta3_label', 'cta3_url',
        'trigger_on_click',
        'trigger_click_class',
        'trigger_on_load',
        'trigger_load_delay_seconds',
        'trigger_on_scroll',
        'trigger_scroll_direction',
        'trigger_scroll_percent',
        'target_scope',
        'target_paths',
        'is_active',
        'is_draft',
        'created_by',
    ];

    protected $casts = [
        'trigger_on_click'   => 'boolean',
        'trigger_on_load'    => 'boolean',
        'trigger_on_scroll'  => 'boolean',
        'is_active'          => 'boolean',
        'is_draft'           => 'boolean',
        'image_gallery'      => 'array',   // cast JSON/text → array
    ];

    protected static function booted(): void
    {
        static::creating(function (self $popup) {
            if (
                auth()->check()
                && ! $popup->created_by
                && Schema::hasColumn($popup->getTable(), 'created_by')
            ) {
                $popup->created_by = auth()->id();
            }
        });
    }

    /**
     * Admin who created the pop-up.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Convenience accessor for author display name.
     */
    public function getAuthorNameAttribute(): ?string
    {
        return optional($this->author)->name;
    }

    public function getStatusAttribute(): string
    {
        if ($this->is_draft) {
            return 'draft';
        }

        return $this->is_active ? 'active' : 'inactive';
    }

    /**
     * All images for this pop-up (primary + extras), de-duplicated.
     */
    public function getAllImagesAttribute(): array
    {
        $images = [];

        if (! empty($this->image_path)) {
            $images[] = trim((string) $this->image_path);
        }

        if (is_array($this->image_gallery)) {
            foreach ($this->image_gallery as $url) {
                $url = trim((string) $url);
                if ($url !== '') {
                    $images[] = $url;
                }
            }
        }

        return collect($images)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Computed special class for on-click trigger.
     * Example: "js-popup-12"
     */
    public function getClickClassAttribute(): string
    {
        if (! $this->trigger_on_click) {
            return '';
        }

        if (! empty($this->trigger_click_class)) {
            return $this->trigger_click_class;
        }

        return $this->id ? 'js-popup-'.$this->id : '';
    }

    /**
     * Does this pop-up apply to a given request path?
     * Path examples: "about", "blog/my-post"
     */
    public function appliesToPath(string $path): bool
    {
        $path = trim($path, '/');

        if ($this->target_scope === 'all' || ! $this->target_paths) {
            return true;
        }

        $patterns = collect(
            preg_split('/[\r\n,]+/', (string) $this->target_paths)
        )
            ->map(fn ($p) => trim($p))
            ->filter();

        if ($patterns->isEmpty()) {
            return true;
        }

        $matches = $patterns->contains(function ($pattern) use ($path) {
            if ($pattern === '*') {
                return true;
            }

            $pattern = trim($pattern);
            $pattern = trim($pattern, '/');

            $regex = '#^' . str_replace('\*', '.*', preg_quote($pattern, '#')) . '$#i';

            return (bool) preg_match($regex, $path);
        });

        if ($this->target_scope === 'include') {
            return $matches;
        }

        // exclude
        return ! $matches;
    }

    /**
     * Find the first active pop-up that applies to this request.
     */
    public static function forRequest(Request $request): ?self
    {
        $path = $request->path();

        return static::query()
            ->where('is_active', true)
            ->where('is_draft', false)
            ->orderByDesc('id')
            ->get()
            ->first(fn (self $popup) => $popup->appliesToPath($path));
    }
}
