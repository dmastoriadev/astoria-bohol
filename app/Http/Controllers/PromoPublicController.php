<?php

namespace App\Http\Controllers;

use App\Models\Promo;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon as IlluminateCarbon;

class PromoPublicController extends Controller
{
    public function index(Request $request)
    {
        // Compare in UTC (your forms normalize to UTC on save)
        $nowUtc = IlluminateCarbon::now('UTC');

        $promos = Promo::query()
            ->where('status', 'published')
            ->where(function ($q) use ($nowUtc) {
                $q
                // Path 1: scheduled publish has arrived
                ->where(function ($qq) use ($nowUtc) {
                    $qq->whereNotNull('scheduled_publish_date')
                       ->where('scheduled_publish_date', '<=', $nowUtc);
                })
                // OR Path 2: no schedule; rely on published_at (legacy/immediate)
                ->orWhere(function ($qq) use ($nowUtc) {
                    $qq->whereNull('scheduled_publish_date')
                       ->where(function ($qqq) use ($nowUtc) {
                           $qqq->whereNull('published_at')   // legacy immediate
                               ->orWhere('published_at', '<=', $nowUtc);
                       });
                });
            })
            // Not expired
            ->where(function ($q) use ($nowUtc) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', $nowUtc);
            })
            // Sort by effective go-live (scheduled -> published -> created)
            ->orderByRaw('COALESCE(scheduled_publish_date, published_at, created_at) DESC')
            ->paginate(9)
            ->withQueryString();

        return view('promos', compact('promos'));
    }

    public function show(Promo $promo)
    {
        $nowUtc = IlluminateCarbon::now('UTC');

        $liveByScheduled = $promo->scheduled_publish_date
            ? $promo->scheduled_publish_date->timezone('UTC')->lte($nowUtc)
            : false;

        $liveByPublished = $promo->published_at
            ? $promo->published_at->timezone('UTC')->lte($nowUtc)
            : false;

        $notExpired = !$promo->expires_at || $promo->expires_at->timezone('UTC')->gt($nowUtc);

        $isLive = $promo->status === 'published' && ($liveByScheduled || $liveByPublished) && $notExpired;

        abort_unless($isLive, 404);

        // Optional: related promos (live only)
        $others = Promo::query()
            ->where('id', '!=', $promo->id)
            ->where('status', 'published')
            ->where(function ($q) use ($nowUtc) {
                $q->whereNotNull('scheduled_publish_date')->where('scheduled_publish_date', '<=', $nowUtc)
                  ->orWhere(function ($qq) use ($nowUtc) {
                      $qq->whereNull('scheduled_publish_date')
                         ->where(function ($qqq) use ($nowUtc) {
                             $qqq->whereNull('published_at')->orWhere('published_at', '<=', $nowUtc);
                         });
                  });
            })
            ->where(function ($q) use ($nowUtc) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', $nowUtc);
            })
            ->orderByRaw('COALESCE(scheduled_publish_date, published_at, created_at) DESC')
            ->get();

        return view('promos.show', compact('promo', 'others'));
    }
}
