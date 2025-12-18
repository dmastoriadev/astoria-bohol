<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promo;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PromoController extends Controller
{
    public function index(Request $request)
    {
        // existing filters/pagination as you already have on dashboard…
        $promos = Promo::query()
            ->when($request->promo_q, fn($q,$v)=>$q->where('title','like',"%{$v}%"))
            ->when($request->promo_category, fn($q,$v)=>$q->where('category',$v))
            ->when($request->promo_status, function($q,$v){
                if ($v === 'draft')     $q->where('status','draft');
                if ($v === 'published') $q->where('status','published');
                if ($v === 'scheduled') $q->whereNotNull('scheduled_publish_date');
            })
            ->when(($request->promo_sort ?? 'latest') === 'oldest', fn($q)=>$q->oldest(), fn($q)=>$q->latest())
            ->paginate(12);

        return view('admin.promos.index', compact('promos'));
    }

    public function create()
    {
        return view('admin.promos.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'                   => ['required','string','max:255'],
            'slug'                    => ['nullable','string','max:255'],
            'body'                    => ['nullable','string'],
            'category'                => ['nullable','in:regular,premium'],
            'publish_mode'            => ['required','in:now,schedule'],
            'scheduled_publish_date'  => ['nullable','date'],
            'expires_at'              => ['nullable','date'],
            'action'                  => ['nullable','in:draft,publish'],
            'timezone'                => ['nullable','string'], // e.g. Asia/Manila
        ]);

        $tz = $request->input('timezone', 'Asia/Manila');

        // Convert local -> UTC (Option A)
        $scheduledAt = $request->filled('scheduled_publish_date')
            ? Carbon::parse($request->input('scheduled_publish_date'), $tz)->timezone('UTC')
            : null;

        $expiresAt = $request->filled('expires_at')
            ? Carbon::parse($request->input('expires_at'), $tz)->timezone('UTC')
            : null;

        $status = 'draft';
        $publishedAt = null;

        if ($data['publish_mode'] === 'now' && ($data['action'] ?? 'draft') === 'publish') {
            $status = 'published';
            $publishedAt = now('UTC');
            $scheduledAt = null; // clear any schedule if publishing now
        }

        $slug = $data['slug'] ?: Str::slug($data['title']);

        $promo = Promo::create([
            'title'                   => $data['title'],
            'slug'                    => $slug,
            'body'                    => $data['body'] ?? null,
            'category'                => $data['category'] ?? null,
            'status'                  => $status,
            'published_at'            => $publishedAt,
            'expires_at'              => $expiresAt,
            'scheduled_publish_date'  => $scheduledAt,
            'created_by_name'         => optional($request->user())->name ?: 'AVLCI',
        ]);

        return redirect()
            ->route('admin.promos.edit', $promo)
            ->with('success', $status === 'published' ? 'Promo published.' : ($scheduledAt ? 'Promo scheduled.' : 'Draft saved.'));
    }

    public function edit(Promo $promo)
    {
        return view('admin.promos.edit', compact('promo'));
    }

    public function update(Request $request, Promo $promo)
    {
        $data = $request->validate([
            'title'                   => ['required','string','max:255'],
            'slug'                    => ['nullable','string','max:255'],
            'body'                    => ['nullable','string'],
            'category'                => ['nullable','in:regular,premium'],
            'publish_mode'            => ['required','in:now,schedule'],
            'scheduled_publish_date'  => ['nullable','date'],
            'expires_at'              => ['nullable','date'],
            'action'                  => ['nullable','in:draft,publish'],
            'timezone'                => ['nullable','string'],
        ]);

        $tz = $request->input('timezone', 'Asia/Manila');

        $scheduledAt = $request->filled('scheduled_publish_date')
            ? Carbon::parse($request->input('scheduled_publish_date'), $tz)->timezone('UTC')
            : null;

        $expiresAt = $request->filled('expires_at')
            ? Carbon::parse($request->input('expires_at'), $tz)->timezone('UTC')
            : null;

        // Update base fields
        $promo->title    = $data['title'];
        $promo->slug     = $data['slug'] ? Str::slug($data['slug']) : Str::slug($data['title']);
        $promo->body     = $data['body'] ?? null;
        $promo->category = $data['category'] ?? null;
        $promo->expires_at = $expiresAt;

        if ($data['publish_mode'] === 'schedule') {
            // Keep status as draft until scheduler time; show as "Scheduled" in UI via effectiveStatus
            $promo->scheduled_publish_date = $scheduledAt;
            if ($scheduledAt && $scheduledAt->isFuture()) {
                $promo->status = 'draft';
                // keep published_at as-is if it was already published; if you want to unschedule published items:
                if (($data['action'] ?? null) !== 'publish') {
                    $promo->published_at = null;
                }
            }
        } else {
            // NOW mode
            $promo->scheduled_publish_date = null;
            if (($data['action'] ?? 'draft') === 'publish') {
                $promo->status = 'published';
                $promo->published_at = now('UTC');
            } else {
                $promo->status = 'draft';
            }
        }

        $promo->save();

        return back()->with('success',
            $promo->status === 'published'
                ? 'Promo updated & published.'
                : ($promo->scheduled_publish_date ? 'Promo updated & scheduled.' : 'Draft updated.')
        );
    }

    public function destroy(Promo $promo)
    {
        // If SoftDeletes:
        // $promo->delete();
        // return back()->with('success','Promo moved to trash.');

        $promo->delete(); // if hard-delete is desired
        return back()->with('success', 'Promo deleted.');
    }
}
