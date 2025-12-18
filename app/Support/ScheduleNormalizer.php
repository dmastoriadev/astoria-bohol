<?php

namespace App\Support;

use Carbon\Carbon;
use DateTimeInterface;

/**
 * ScheduleNormalizer
 *
 * Normalizes <input type="datetime-local"> values that arrive from the browser
 * (which are naive, without a timezone) into UTC timestamps before saving.
 *
 * Usage in a controller:
 *   $data = $request->validate([...]);
 *   $data = \App\Support\ScheduleNormalizer::fromRequest($data, 'Asia/Manila');
 *   // ... then create/update your model with $data
 *
 * You can also convert a single field:
 *   $utc = \App\Support\ScheduleNormalizer::toUtc($request->published_at, 'Asia/Manila');
 */
class ScheduleNormalizer
{
    /**
     * Convert a local datetime string or DateTime into a UTC Carbon instance.
     *
     * @param  string|\DateTimeInterface|null $value
     * @param  string $assumedLocalTz  The timezone that the user's browser is assuming,
     *                                 usually your app timezone (e.g. 'Asia/Manila').
     * @return \Carbon\Carbon|null
     */
    public static function toUtc(string|\DateTimeInterface|null $value, string $assumedLocalTz = 'Asia/Manila'): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof DateTimeInterface) {
            return Carbon::instance($value)->clone()->utc();
        }

        $v = trim((string) $value);

        // Try the common formats from <input type="datetime-local">
        $formats = [
            'Y-m-d\TH:i',     // 2025-08-21T14:30
            'Y-m-d H:i',      // 2025-08-21 14:30
            'Y-m-d\TH:i:s',   // 2025-08-21T14:30:00
            'Y-m-d H:i:s',    // 2025-08-21 14:30:00
        ];

        foreach ($formats as $fmt) {
            try {
                $dt = Carbon::createFromFormat($fmt, $v, $assumedLocalTz);
                // createFromFormat may return falsey on failure, but will throw on invalid
                if ($dt !== false) {
                    return $dt->utc();
                }
            } catch (\Throwable $e) {
                // keep trying other formats
            }
        }

        // Fallback to Carbon::parse with the assumed local timezone
        try {
            return Carbon::parse($v, $assumedLocalTz)->utc();
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Mass-normalize selected datetime keys from an array (e.g. validated request data).
     *
     * @param  array  $in
     * @param  string $assumedLocalTz
     * @param  array  $keys
     * @return array
     */
    public static function fromRequest(array $in, string $assumedLocalTz = 'Asia/Manila', array $keys = ['published_at','expires_at','starts_at','ends_at']): array
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $in)) {
                $utc = self::toUtc($in[$key], $assumedLocalTz);
                $in[$key] = $utc?->toDateTimeString();
            }
        }
        return $in;
    }
}
