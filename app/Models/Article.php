<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;

// Spatie activitylog
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Article extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'articles';

    /**
     * Default attributes.
     */
    protected $attributes = [
        'status' => 'draft',
    ];

    /**
     * Only the fields we expect to mass-assign from forms.
     * Intentionally exclude: author, created_by, created_by_name.
     */
    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'body',
        'featured_image',
        'tags',
        'status',
        'article_category_id',
        'published_at',
        'scheduled_publish_date',
        'expires_at',
        'category',
    ];

    /**
     * Cast datetimes so Carbon methods work in Blade.
     */
    protected $casts = [
        'created_at'             => 'datetime',
        'updated_at'             => 'datetime',
        'published_at'           => 'datetime',
        'expires_at'             => 'datetime',
        'scheduled_publish_date' => 'datetime',
        'deleted_at'             => 'datetime', // for SoftDeletes
    ];

    /* =========================
     | Activity Log (Spatie)
     * ========================= */

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('articles')
            ->logOnly([
                'title',
                'slug',
                'excerpt',
                'body',
                'featured_image',
                'tags',
                'status',
                'article_category_id',
                'published_at',
                'scheduled_publish_date',
                'expires_at',
                // snapshot-ish fields if present:
                'author',
                'created_by',
                'created_by_name',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Back-compat for older Spatie versions (safe to keep even on v5)
    public static $logAttributes = [
        'title',
        'slug',
        'excerpt',
        'body',
        'featured_image',
        'tags',
        'status',
        'article_category_id',
        'published_at',
        'scheduled_publish_date',
        'expires_at',
        'author',
        'created_by',
        'created_by_name',
    ];
    public static $logOnlyDirty = true;
    public static $submitEmptyLogs = false;
    public static $logName = 'articles';

    public function getDescriptionForEvent(string $eventName): string
    {
        return match ($eventName) {
            'created'  => 'Created',
            'updated'  => 'Updated',
            'deleted'  => 'Deleted',
            'restored' => 'Restored',
            default    => ucfirst($eventName),
        };
    }

    /* =========================
     | Relationships
     * ========================= */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ArticleCategory::class, 'article_category_id');
    }

    /* =========================
     | Accessors / Helpers
     * ========================= */

    // Fallback to related user's name if snapshot column is null
    public function getCreatedByNameAttribute($value): ?string
    {
        return $value ?: optional($this->creator)->name;
    }

    // Human-friendly status for tables: $article->status_label
    public function getStatusLabelAttribute(): string
    {
        return strtolower((string) $this->status) === 'published' ? 'Published' : 'Draft';
    }

    // Public URL for the stored/absolute featured image: $article->featured_image_url
    public function getFeaturedImageUrlAttribute(): ?string
    {
        if (!$this->featured_image) {
            return null;
        }

        if (preg_match('#^https?://#i', $this->featured_image)) {
            return $this->featured_image;
        }

        return Storage::disk('public')->url($this->featured_image);
    }

    /**
     * Effective go-live moment for sorting/links (scheduled first, then published, then created).
     */
    public function getEffectivePublishAtAttribute(): ?Carbon
    {
        $v = $this->scheduled_publish_date ?: $this->published_at ?: $this->created_at;
        return $v instanceof Carbon ? $v->copy() : ($v ? Carbon::parse($v, 'UTC') : null);
    }

    /**
     * Convenience: whether it’s currently considered publicly visible (time windows included).
     * Uses UTC for comparisons (store in UTC; display in PH).
     */
    public function isPublished(): bool
    {
        $status = strtolower((string)($this->status ?? ''));
        if ($status !== 'published') {
            return false;
        }

        $nowUtc = Carbon::now('UTC');

        // If there's a schedule, it must be in the past.
        if ($this->scheduled_publish_date instanceof Carbon) {
            if ($this->scheduled_publish_date->greaterThan($nowUtc)) {
                return false;
            }
        } elseif (!empty($this->scheduled_publish_date)) {
            if (Carbon::parse($this->scheduled_publish_date, 'UTC')->gt($nowUtc)) {
                return false;
            }
        } else {
            // No schedule: published_at must be <= now (or null = immediate legacy)
            if ($this->published_at instanceof Carbon) {
                if ($this->published_at->greaterThan($nowUtc)) {
                    return false;
                }
            } elseif (!empty($this->published_at) && Carbon::parse($this->published_at, 'UTC')->gt($nowUtc)) {
                return false;
            }
        }

        // Not expired
        if ($this->expires_at instanceof Carbon) {
            if ($this->expires_at->lte($nowUtc)) {
                return false;
            }
        } elseif (!empty($this->expires_at) && Carbon::parse($this->expires_at, 'UTC')->lte($nowUtc)) {
            return false;
        }

        return true;
    }

    /* =========================
     | Scopes
     * ========================= */

    /**
     * LIVE scope: publicly visible now.
     *
     * Logic:
     *  - status === 'published'
     *  - if scheduled_publish_date IS NOT NULL => it must be <= now
     *    (and this takes precedence over published_at)
     *  - else (no schedule) => published_at IS NULL OR published_at <= now
     *  - expires_at IS NULL OR expires_at > now
     */
    public function scopeLive(Builder $q): Builder
    {
        $nowUtc = Carbon::now('UTC');

        return $q
            // status == published (case-insensitive)
            ->whereRaw('LOWER(COALESCE(status, "")) = ?', ['published'])

            // go-live window
            ->where(function (Builder $qq) use ($nowUtc) {
                $qq
                    // scheduled path: must be in the past (visible now)
                    ->where(function (Builder $s) use ($nowUtc) {
                        $s->whereNotNull('scheduled_publish_date')
                          ->where('scheduled_publish_date', '<=', $nowUtc);
                    })
                    // OR no schedule -> published_at null (immediate legacy) or <= now
                    ->orWhere(function (Builder $p) use ($nowUtc) {
                        $p->whereNull('scheduled_publish_date')
                          ->where(function (Builder $pp) use ($nowUtc) {
                              $pp->whereNull('published_at')
                                 ->orWhere('published_at', '<=', $nowUtc);
                          });
                    });
            })

            // not expired
            ->where(function (Builder $qq) use ($nowUtc) {
                $qq->whereNull('expires_at')
                   ->orWhere('expires_at', '>', $nowUtc);
            });
    }

    /**
     * Back-compat alias. Your old calls to ->published() now include schedule/expiry semantics.
     */
    public function scopePublished(Builder $q): Builder
    {
        return $this->scopeLive($q);
    }

    public function scopeDraft(Builder $q): Builder
    {
        return $q->whereRaw('LOWER(COALESCE(status, "")) = ?', ['draft']);
    }

    /**
     * Handy sorter for listings that want the "effective" date (schedule > published > created).
     */
        public function scopeOrderByEffectivePublished(Builder $q, string $direction = 'desc'): Builder
    {
        $dir = strtolower($direction) === 'asc' ? 'ASC' : 'DESC';
        return $q->orderByRaw("COALESCE(scheduled_publish_date, published_at, created_at) {$dir}");
    }

    /**
     * Normalized payload for search results.
     * This is what your search.blade.php picks up via ->toSearchArray().
     */
    public function toSearchArray(): array
    {
        // Use your effective publish date (schedule > published_at > created_at)
        $published = $this->effective_publish_at
            ?? $this->published_at
            ?? $this->created_at;

        // Fallback in case something is null
        if (!$published instanceof Carbon) {
            $published = $this->created_at instanceof Carbon
                ? $this->created_at
                : Carbon::now('UTC');
        }

        // Build the correct /YYYY/MM/DD/slug URL for the article
        $url = route('articles.show', [
            'year'  => $published->format('Y'),
            'month' => $published->format('m'),
            'day'   => $published->format('d'),
            'slug'  => $this->slug,
        ]);

        return [
            'type'     => 'blog',
            'title'    => $this->title,
            'url'      => $url,
            'excerpt'  => $this->excerpt
                ?: \Illuminate\Support\Str::limit(strip_tags((string) $this->body), 200),
            'category' => optional($this->category)->name,
        ];
    }
}
