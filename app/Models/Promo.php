<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Promo extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    /** --- Constants --- */
    public const STATUS_DRAFT     = 'draft';
    public const STATUS_PUBLISHED = 'published';

    /** --- Defaults --- */
    protected $attributes = [
        'status' => self::STATUS_DRAFT,
    ];

    /** --- Mass assignment --- */
        protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'body',
        'featured_image',
        'status',                   // 'draft' | 'published'
        'published_at',
        'expires_at',
        'starts_at',
        'ends_at',
        'created_by',
        'created_by_name',
        'scheduled_publish_date',
    ];


    /** --- Casting --- */
    protected $casts = [
        'created_at'              => 'datetime',
        'updated_at'              => 'datetime',
        'deleted_at'              => 'datetime',
        'published_at'            => 'datetime',
        'expires_at'              => 'datetime',
        'starts_at'               => 'datetime',
        'ends_at'                 => 'datetime',
        'scheduled_publish_date'  => 'datetime',
    ];

    /** --- Model hooks --- */
    protected static function booted(): void
    {
        // Any restore path (single or bulk) -> force DRAFT and clear publish/schedule dates.
        static::restoring(function (self $promo) {
            $promo->status                 = self::STATUS_DRAFT;
            $promo->published_at           = null;
            $promo->scheduled_publish_date = null;
            // NOTE: We intentionally keep expires_at/starts_at/ends_at as-is.
        });
    }

    /** --- Activity Log --- */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('promo')
            ->logOnly([
                'title','slug','excerpt','body','featured_image',
                'status',
                'published_at','expires_at','starts_at','ends_at',
                'scheduled_publish_date',
                'created_by','created_by_name',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }


    /** --- Relationships --- */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** --- Accessors / Helpers --- */

    /**
     * Fallback to creator->name if created_by_name is null.
     */
    public function getCreatedByNameAttribute($value): ?string
    {
        return $value ?: optional($this->creator)->name;
    }

    /**
     * Resolve featured image full URL (supports absolute URLs or public storage).
     */
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
     * Effective publish datetime (published_at → scheduled_publish_date → created_at).
     */
    public function getEffectivePublishAtAttribute(): ?Carbon
    {
        return $this->published_at
            ?? $this->scheduled_publish_date
            ?? $this->created_at;
    }

    /** --- Scopes --- */

    /**
     * Status is explicitly 'published' (does not check dates).
     */
    public function scopePublished($q)
    {
        return $q->where('status', self::STATUS_PUBLISHED);
    }

    /**
     * Not expired yet (or no expiry). Backward compatible with your previous "active".
     */
    public function scopeActive($q)
    {
        $now = now();
        return $q->where(function ($qq) use ($now) {
            $qq->whereNull('expires_at')->orWhere('expires_at', '>=', $now);
        });
    }

    /**
     * Fully "live" promos for the site:
     * - status = published
     * - scheduled_publish_date is null or in the past
     * - starts_at is null or in the past
     * - ends_at / expires_at are null or in the future
     */
    public function scopeLive($q)
    {
        $now = now();

        return $q->where('status', self::STATUS_PUBLISHED)
            ->where(function ($qq) use ($now) {
                $qq->whereNull('scheduled_publish_date')
                   ->orWhere('scheduled_publish_date', '<=', $now);
            })
            ->where(function ($qq) use ($now) {
                $qq->whereNull('starts_at')
                   ->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($qq) use ($now) {
                $qq->whereNull('ends_at')
                   ->orWhere('ends_at', '>=', $now);
            })
            ->where(function ($qq) use ($now) {
                $qq->whereNull('expires_at')
                   ->orWhere('expires_at', '>=', $now);
            });
    }


    /**
     * Sort by the most meaningful publish-ish timestamp.
     */
    public function scopeOrdered($q)
    {
        return $q->orderByRaw('COALESCE(published_at, scheduled_publish_date, created_at) DESC');
    }
}
