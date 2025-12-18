{{-- resources/views/admin/dashboard.blade.php --}}
@extends('admin.layout')
@section('page_title','Dashboard')
@push('modals')
  


@section('content')
<section id="admin-dashboard"
  x-data="dashPage()"
  x-init="init()"
  x-cloak
  class="space-y-6"
  @dash:navigate.window="navTo($event.detail.show)">

@php
  use Illuminate\Support\Carbon;
  use Illuminate\Support\Str;
  use Illuminate\Support\Facades\Route;
  
   $mediaListUrl = Route::has('admin.media.list')
      ? route('admin.media.list')
      : url('/admin/media/list');

  /* ================== SAFEGUARDS & NORMALIZATION ================== */
 // Accept any alias the controller might send for trashed BLOGS paginator
  $promos   = $promos   ?? collect();
  $articles = $articles ?? collect();

  // Accept any alias the controller might send for trashed BLOGS paginator
  $trashedArticles = $trashedArticles
      ?? ($trashed ?? ($trashArticles ?? ($articlesTrash ?? collect())));

  // Accept any alias the controller might send for trashed PROMOS paginator
  $trashedPromos = $trashedPromos
      ?? ($trashedPromo ?? ($promosTrash ?? ($trashPromos ?? collect())));

  $stats      = $stats      ?? ['blogs'=>0,'careers'=>0,'promos'=>0];
  $categories = $categories ?? collect();

  // Preselect IDs (blogs)
  $selectedAddCat  = (string) old('article_category_id', '');
  $selectedEditCat = isset($editArticle)
      ? (string) old('article_category_id', $editArticle->article_category_id ?? '')
      : '';

  // ===== BADGES: split counts per module =====
  // Blogs
  $activeBlogsTotal = (is_object($articles) && method_exists($articles,'total')) ? $articles->total()
                      : (is_countable($articles) ? count($articles) : 0);
  $trashBlogsTotal  = (is_object($trashedArticles) && method_exists($trashedArticles,'total')) ? $trashedArticles->total()
                      : (is_countable($trashedArticles) ? count($trashedArticles) : 0);
  $activeBlogsCount   = $activeBlogsTotal;
  $trashedBlogsCount  = $trashBlogsTotal;

  // Promos
  $activePromosTotal = (is_object($promos) && method_exists($promos,'total')) ? $promos->total()
                      : (is_countable($promos) ? count($promos) : 0);
  $trashPromosTotal  = (is_object($trashedPromos) && method_exists($trashedPromos,'total')) ? $trashedPromos->total()
                      : (is_countable($trashedPromos) ? count($trashedPromos) : 0);
  $activePromosCount  = $activePromosTotal;
  $trashedPromosCount = $trashPromosTotal;

  // Back-compat: keep old names for BLOGS (used by Blogs list accordion)
  $activeCount  = $activeBlogsCount;
  $trashedCount = $trashedBlogsCount;



// Toasts
$flashToasts = [];

// success & status (keep both, deduper will collapse)
$succ = collect([session('success'), session('status')])->filter(fn($m)=>filled($m))->unique()->values();
foreach ($succ as $m) { $flashToasts[] = ['type'=>'success','title'=>'Success','message'=>$m]; }

// errors
if (session('error')) $flashToasts[] = ['type'=>'error','title'=>'Error','message'=>session('error')];
if ($errors->any()) {
  foreach ($errors->all() as $e) {
    if (trim($e) !== trim((string) session('error'))) {
      $flashToasts[] = ['type'=>'error','title'=>'Validation','message'=>$e];
    }
  }
}

// optional extras
foreach (['warning'=>'Warning','info'=>'Notice'] as $k=>$ttl) {
  if (session($k)) $flashToasts[] = ['type'=>$k,'title'=>$ttl,'message'=>session($k)];
}

/* ---------- NORMALIZE/DEDUP (destructive collapse) ---------- */
$__anyDestructive = false;

$flashToasts = collect($flashToasts)->map(function($t) use (&$__anyDestructive){
    $raw = (string)($t['message'] ?? '');
    $msg = mb_strtolower(trim($raw));
    $msgNorm = preg_replace('/[“”"\'‘’]+/u', '', $msg);
    $msgNorm = preg_replace('/\s+/u', ' ', $msgNorm);

    $matchesDeleteRegex = (bool) preg_match(
        '/\b(?:promo|promos|blog|blogs|article|articles|post|posts|page|pages)?\s*' .
        '(?:was|were|has\s+been|have\s+been)?\s*' .
        '(?:soft[-\s]?|permanently\s+)?' .
        '(?:deleted|removed)\b/u',
        $msgNorm
    );

    $isDestructive =
        str_contains($msgNorm, 'moved to trash') ||
        str_contains($msgNorm, 'permanently deleted') ||
        str_contains($msgNorm, 'delete forever') ||
        str_contains($msgNorm, 'force deleted') ||
        str_contains($msgNorm, 'promo deleted') ||
        str_contains($msgNorm, 'deleted promo') ||
        $matchesDeleteRegex;

    if ($isDestructive) {
        $__anyDestructive = true;
        // canonical signature = 1 toast max
        $t['__sig']   = 'destructive:delete:any';
        $t['variant'] = 'danger-solid';
        $t['type']    = 'success'; // use the success channel for consistency
        $t['title']   = $t['title'] ?? (str_contains($msgNorm, 'promo') ? 'Promo Deleted' : 'Deleted');
        // optional: normalize the message text to avoid dupes with different phrasing
        $t['message'] = trim($t['message']) ?: 'Deleted.';
    } else {
        $t['__sig'] = ($t['type'] ?? 'info') . '|' . ($t['variant'] ?? '') . '|' . $msgNorm;
    }

    return $t;
})
->reverse()
->unique(fn($t) => $t['__sig'] ?? '')
->map(function($t){ unset($t['__sig']); return $t; })
->values();

// if anything was destructive, kill the centered popup entirely
$flashSuccessPopup = $__anyDestructive ? null : ($flashSuccessPopup ?? null);


  /* ================== FILTER QUERIES ================== */
  // Blogs
  $blogSearch  = request('blog_q', '');
  $blogCat     = request('blog_category', '');
  $blogStatus  = request('blog_status', '');
  $blogCreator = request('blog_creator', '');
  $blogSort    = request('blog_sort', 'latest');

  // Promos
  $promoSearch  = request('promo_q', '');
  $promoStatus  = request('promo_status', '');
  $promoCreator = request('promo_creator', '');
  $promoSort    = request('promo_sort', 'latest');

  // Creator options (based on current page items)
  $blogItems = is_object($articles) && method_exists($articles,'items') ? $articles->items() : (is_iterable($articles) ? $articles : []);
  $blogCreators = collect($blogItems)
      ->map(fn($a) => $a->created_by_name ?? optional($a->creator)->name)
      ->filter()->unique()->sort()->values();

  $promoItems = is_object($promos) && method_exists($promos,'items') ? $promos->items() : (is_iterable($promos) ? $promos : []);
  $promoCreators = collect($promoItems)
      ->map(fn($p) => $p->created_by_name ?: 'Astoria Palawan')
      ->filter()->unique()->sort()->values();

  // ================== TIMEZONE HELPERS ==================
  $tz  = 'Asia/Manila';
  $now = Carbon::now($tz);

  // Always convert (potentially UTC) values to PH display
  $toPH = function($val) use ($tz) {
      if (empty($val)) return null;
      if ($val instanceof \Carbon\Carbon) {
          return $val->copy()->timezone($tz);
      }
      // IMPORTANT: parse with UTC baseline, then show in PH
      return Carbon::parse($val, 'UTC')->timezone($tz);
  };

  // Drafts on page
  $draftBlogsOnPage = collect($blogItems)->filter(fn($a) => strtolower($a->status ?? '') !== 'published')->count();

  // SCHEDULED count uses scheduled_publish_date first, then published_at for backward compat
  $scheduledBlogsOnPage = collect($blogItems)->filter(function($a) use ($now, $toPH) {
      $sched = !empty($a->scheduled_publish_date) ? $toPH($a->scheduled_publish_date) : null;
      if ($sched && $sched->greaterThan($now)) return true;

      $pub = !empty($a->published_at) ? $toPH($a->published_at) : null;
      return $pub ? $pub->greaterThan($now) : false;
  })->count();

  $newBlogsThisWeekOnPage = collect($blogItems)->filter(function($a) use ($now, $toPH) {
      $dt = !empty($a->created_at) ? $toPH($a->created_at) : null;
      return $dt ? $dt->greaterThanOrEqualTo($now->copy()->startOfWeek()) : false;
  })->count();

  $draftPromosOnPage = collect($promoItems)->filter(fn($p) => strtolower($p->status ?? '') !== 'published')->count();

  // ✅ Promo scheduled count now respects scheduled_publish_date like blogs
  $scheduledPromosOnPage = collect($promoItems)->filter(function($p) use ($now, $toPH) {
      $sched = !empty($p->scheduled_publish_date) ? $toPH($p->scheduled_publish_date) : null;
      if ($sched && $sched->greaterThan($now)) return true;

      $pub = !empty($p->published_at) ? $toPH($p->published_at) : null;
      return $pub ? $pub->greaterThan($now) : false;
  })->count();

  // 14-day activity arrays (counts by created_at on current page only)
  $labels = [];
  $blogSeries = [];
  $promoSeries = [];
  for ($i = 13; $i >= 0; $i--) {
      $d = $now->copy()->startOfDay()->subDays($i);
      $labels[] = $d->format('M j');
      $blogSeries[] = collect($blogItems)->filter(function($a) use ($d, $toPH) {
          $dt = !empty($a->created_at) ? $toPH($a->created_at) : null;
          return $dt ? $dt->isSameDay($d) : false;
      })->count();
      $promoSeries[] = collect($promoItems)->filter(function($p) use ($d, $toPH) {
          $dt = !empty($p->created_at) ? $toPH($p->created_at) : null;
          return $dt ? $dt->isSameDay($d) : false;
      })->count();
  }

  // Helper: derive an EFFECTIVE status from DB status + dates
  // Returns ['slug' => 'draft|scheduled|published', 'label' => 'Draft|Scheduled|Published', 'class' => css]
$effectiveStatus = function($item) use ($now, $toPH) {
    $raw   = strtolower($item->status ?? '');

    $sched = !empty($item->scheduled_publish_date) ? $toPH($item->scheduled_publish_date) : null;
    $pub   = !empty($item->published_at)           ? $toPH($item->published_at)           : null;
    $exp   = !empty($item->expires_at)             ? $toPH($item->expires_at)             : null;

    //  Expired always wins
    if ($exp && $exp->lessThanOrEqualTo($now)) {
        return ['slug'=>'expired','label'=>'Expired','class'=>'bg-rose-50 text-rose-700 border-rose-200'];
    }

    // Any future time means Scheduled
    if (($sched && $sched->greaterThan($now)) || ($pub && $pub->greaterThan($now))) {
        return ['slug'=>'scheduled','label'=>'Scheduled','class'=>'bg-amber-50 text-amber-700 border-amber-200'];
    }

    // Any past/now publish OR schedule time means Published
    if (($pub && $pub->lessThanOrEqualTo($now)) || ($sched && $sched->lessThanOrEqualTo($now))) {
        return ['slug'=>'published','label'=>'Published','class'=>'bg-emerald-50 text-emerald-700 border-emerald-200'];
    }

    // Otherwise rely on raw status
    if ($raw === 'draft' || blank($item->status)) {
        return ['slug'=>'draft','label'=>'Draft','class'=>'bg-gray-50 text-gray-700 border-gray-200'];
    }

    return ['slug'=>'published','label'=>'Published','class'=>'bg-emerald-50 text-emerald-700 border-emerald-200'];
};




  $extractActivity = function($src){
      if (!$src) return null;
      if (is_array($src)) {
          if (isset($src['labels'])) {
              return [
                  'labels' => array_values($src['labels'] ?? []),
                  'blogs'  => array_map('intval', $src['blogs']  ?? []),
                  'promos' => array_map('intval', $src['promos'] ?? []),
              ];
          }
      } elseif (is_object($src) && isset($src->labels)) {
          return [
              'labels' => array_values($src->labels ?? []),
              'blogs'  => array_map('intval', $src->blogs  ?? []),
              'promos' => array_map('intval', $src->promos ?? []),
          ];
      }
      return null;
  };

  $chartPayload = [
      'labels' => $labels,       // your current page-only labels
      'blogs'  => $blogSeries,   // your current page-only series
      'promos' => $promoSeries,
  ];

  foreach (['activity14','analytics14','activity','analytics'] as $_k) {
      if (isset($$_k)) {
          $_p = $extractActivity($$_k);
          if ($_p) { $chartPayload = $_p; break; }
      }
  }


    /**
   * Fetch up to N activities for a subject using the configured activity model
   * and the model’s morph class (works with or without morphMap).
   */
  $fetchActivitiesFor = function ($subject, int $limit = 50) {
    if (!$subject instanceof \Illuminate\Database\Eloquent\Model) {
        return collect();
    }

    $activityModelClass = config('activitylog.activity_model', \Spatie\Activitylog\Models\Activity::class);

    try {
        // Spatie v4/v5 scope
        if (method_exists($activityModelClass, 'forSubject')) {
            return $activityModelClass::forSubject($subject)
                ->with(['causer'])              // eager load to avoid lazy-loading issues
                ->latest()
                ->limit($limit)
                ->get();
        }

        // Fallback: match BOTH morph alias and FQCN to cover mixed historical data
        $morph = $subject->getMorphClass();
        $fqcn  = get_class($subject);

        return $activityModelClass::query()
            ->where('subject_id', $subject->getKey())
            ->where(function ($q) use ($morph, $fqcn) {
                $q->where('subject_type', $morph)
                  ->orWhere('subject_type', $fqcn);
            })
            ->with(['causer'])
            ->latest()
            ->limit($limit)
            ->get();
    } catch (\Throwable $e) {
        return collect();
    }
};


  /* ================== HISTORY (optional) ================== */
  // Flexible history normalizer. Controller can pass:
  // - $blogHistory / $articleHistory / $editArticleHistory or $editArticle->history / ->activities
  // - $promoHistory / $editPromoHistory / $promoEditHistory or $editPromo->history / ->activities
  $historyFieldLabel = function(string $key){
      $map = [
        'title'                   => 'Page Title',
        'slug'                    => 'Slug',
        'article_category_id'     => 'Category',
        'category'                => 'Category',
        'status'                  => 'Status',
        'published_at'            => 'Published/Scheduled (PH)',
        'scheduled_publish_date'  => 'Published/Scheduled (PH)',
        'expires_at'              => 'Unpublish (PH)',
        'author'                  => 'Author',
        'created_by'              => 'Created By',
        'created_by_name'         => 'Created By',
        'updated_by_name'         => 'Updated By',
        'tags'                    => 'Hashtags',
        'excerpt'                 => 'Excerpt',
        'body'                    => 'Body',
        'featured_image'          => 'Image',
        'image'                   => 'Image',
      ];
      // prettify unknown keys
      return $map[$key] ?? \Illuminate\Support\Str::of($key)->replace('_',' ')->headline()->toString();
  };


       $normalizeHistoryItems = function ($items) use ($toPH, $historyFieldLabel) {
      $rows = [];
      $iter = collect(is_iterable($items) ? $items : []);

      // robust array-like to array
      $toArr = function ($v) {
          if (is_array($v)) return $v;
          if ($v instanceof \Illuminate\Support\Collection) return $v->toArray();
          if ($v instanceof \ArrayObject) return $v->getArrayCopy();
          if (is_object($v) && method_exists($v, 'toArray')) return $v->toArray();
          return [];
      };

      // fields we never want to show as “updated what”
      $ignore = [
          'id','created_at','updated_at','deleted_at',
          'user_id','user_name','updated_by','updated_by_name'
      ];

      foreach ($iter as $h) {
          /* ---------- WHO ---------- */
          $by = '—';
          if (is_object($h)) {
              $by = $h->updated_by_name
                ?? $h->user_name
                ?? optional($h->user)->name
                ?? optional($h->causer)->name
                ?? optional($h->causer)->email
                ?? $by;
          } else {
              $by = $h['updated_by_name']
                ?? $h['user_name']
                ?? (is_array($h['user'] ?? null) ? ($h['user']['name'] ?? null) : ($h['user'] ?? null))
                ?? ($h['causer']['name'] ?? null)
                ?? ($h['causer']['email'] ?? null)
                ?? $by;
          }

          /* ---------- WHEN (PH) ---------- */
          $whenRaw = is_object($h) ? ($h->updated_at ?? $h->created_at ?? null)
                                   : ($h['updated_at'] ?? ($h['created_at'] ?? null));
          $when = $whenRaw ? $toPH($whenRaw)->format('M d, Y • g:i A') : '—';

          /* ---------- WHAT (diff) ---------- */
          $changed = [];

          // 1) Spatie v5+: $activity->changes (object/collection-like)
          if (is_object($h) && isset($h->changes)) {
              $chg = $toArr($h->changes);
              $new = $toArr($chg['attributes'] ?? []);
              $old = $toArr($chg['old'] ?? []);
              $keys = array_unique(array_merge(array_keys($new), array_keys($old)));
              foreach ($keys as $k) {
                  if (in_array($k, $ignore, true)) continue;
                  $a = $new[$k] ?? null; $b = $old[$k] ?? null;
                  if ($a instanceof \DateTimeInterface) $a = $a->format('c');
                  if ($b instanceof \DateTimeInterface) $b = $b->format('c');
                  if ($a !== $b) $changed[] = $k;
              }
          }

          // 2) Spatie legacy: ->properties holds attributes/old
          if (!$changed && is_object($h) && isset($h->properties)) {
              $props = $toArr($h->properties);
              $new = $toArr($props['attributes'] ?? []);
              $old = $toArr($props['old'] ?? []);
              $keys = array_unique(array_merge(array_keys($new), array_keys($old)));
              foreach ($keys as $k) {
                  if (in_array($k, $ignore, true)) continue;
                  $a = $new[$k] ?? null; $b = $old[$k] ?? null;
                  if ($a instanceof \DateTimeInterface) $a = $a->format('c');
                  if ($b instanceof \DateTimeInterface) $b = $b->format('c');
                  if ($a !== $b) $changed[] = $k;
              }
          }

          // 3) Plain arrays (revisionable-like / custom payloads)
          if (!$changed && is_array($h)) {
              $new = $toArr($h['changes']['attributes'] ?? $h['attributes'] ?? $h['new_values'] ?? []);
              $old = $toArr($h['changes']['old'] ?? $h['old'] ?? $h['old_values'] ?? []);
              $keys = array_unique(array_merge(array_keys($new), array_keys($old)));
              foreach ($keys as $k) {
                  if (in_array($k, $ignore, true)) continue;
                  $a = $new[$k] ?? null; $b = $old[$k] ?? null;
                  if ($a instanceof \DateTimeInterface) $a = $a->format('c');
                  if ($b instanceof \DateTimeInterface) $b = $b->format('c');
                  if ($a !== $b) $changed[] = $k;
              }
              if (!$changed && (isset($h['key']) || isset($h['field']))) {
                  $k = (string) ($h['key'] ?? $h['field']);
                  if (!in_array($k, $ignore, true)) $changed[] = $k;
              }
              if (!$changed && isset($h['changed_fields']) && is_array($h['changed_fields'])) {
                  foreach ($h['changed_fields'] as $k) {
                      if (!in_array($k, $ignore, true)) $changed[] = (string) $k;
                  }
              }
              if (!$changed && isset($h['what'])) {
                  $raw = $h['what'];
                  foreach ((array) $raw as $k) {
                      if (!in_array($k, $ignore, true)) $changed[] = (string) $k;
                  }
              }
          }

          // 4) “what” property on an object
          if (!$changed && is_object($h) && isset($h->what)) {
              foreach ((array) $h->what as $k) {
                  if (!in_array($k, $ignore, true)) $changed[] = (string) $k;
              }
          }

          // Map to friendly labels and tidy up
          $changed = array_values(array_unique(array_map('strval', $changed)));
          $labels  = array_map(fn ($k) => $historyFieldLabel($k), $changed);

          // Fallback: show event/description
          if (!count($labels)) {
              $desc = is_object($h) ? ($h->description ?? null) : ($h['description'] ?? null);
              $evt  = is_object($h) ? ($h->event ?? null)       : ($h['event'] ?? null);
              $labels = [ $desc ? ucfirst($desc) : ($evt ? ucfirst($evt) : 'Updated') ];
          }

          $rows[] = ['by' => $by ?: '—', 'what' => implode(', ', $labels), 'when' => $when];
      }

      return $rows;
  };



  // Prepare history rows for edit contexts; add forms show empty history
 
  $blogHistoryRows = [];
  $blogHistoryCount = 0;
  if (isset($editArticle)) {
      // try any provided collections first
      $blogHistorySource = collect([
          $blogHistory ?? null,
          $articleHistory ?? null,
          $editArticleHistory ?? null,
          $editArticle->history ?? null,
          $editArticle->activities ?? null,
      ])->first(function ($c) {
          if (!$c) return false;
          if ($c instanceof \Illuminate\Support\Collection) return $c->isNotEmpty();
          if (is_array($c)) return !empty($c);
          if (is_object($c) && method_exists($c, 'count')) { try { return $c->count() > 0; } catch (\Throwable $e) { return false; } }
          if (is_iterable($c)) { foreach ($c as $_) { return true; } }
          return false;
      });

      // If nothing was passed, actively query Spatie activitylog
      if (!$blogHistorySource) {
    $blogHistorySource = $fetchActivitiesFor($editArticle, 50);
}

      $blogHistoryRows  = $normalizeHistoryItems($blogHistorySource ?? []);
      $blogHistoryCount = count($blogHistoryRows);
  }


    $promoHistoryRows = [];
  $promoHistoryCount = 0;
  if (isset($editPromo)) {
      $promoHistorySource = collect([
          $promoHistory ?? null,
          $editPromoHistory ?? null,
          $promoEditHistory ?? null,
          $editPromo->history ?? null,
          $editPromo->activities ?? null,
      ])->first(function ($c) {
          if (!$c) return false;
          if ($c instanceof \Illuminate\Support\Collection) return $c->isNotEmpty();
          if (is_array($c)) return !empty($c);
          if (is_object($c) && method_exists($c, 'count')) { try { return $c->count() > 0; } catch (\Throwable $e) { return false; } }
          if (is_iterable($c)) { foreach ($c as $_) { return true; } }
          return false;
      });

      if (!$promoHistorySource) {
    $promoHistorySource = $fetchActivitiesFor($editPromo, 50);
}

      $promoHistoryRows  = $normalizeHistoryItems($promoHistorySource ?? []);
      $promoHistoryCount = count($promoHistoryRows);
  }


  $firstNonEmpty = function (...$candidates) {
      foreach ($candidates as $c) {
          if (!$c) continue;

          if (is_array($c) && count($c)) return $c;

          if ($c instanceof \Illuminate\Support\Collection) {
              if ($c->count()) return $c;
              continue;
          }

          if (is_object($c) && method_exists($c, 'count')) {
              if ($c->count() > 0) return $c;
          } elseif (is_iterable($c)) {
              foreach ($c as $_) { return $c; } // has at least one item
          }
      }
      return [];
  };


  $tinymceUploadUrl = \Illuminate\Support\Facades\Route::has('admin.tinymce.upload')
    ? route('admin.tinymce.upload')
    : url('/admin/uploads/tinymce'); // fallback path — adjust if needed

//popup related
      $activePopupCount = \App\Models\Popup::where('is_active', true)
    ->where('is_draft', false)
    ->count();
@endphp

<style>
[x-cloak]{ display:none !important; }
/* Fix: center popup should never stretch full width */
#result-modal-card{ width:min(92vw, 22rem); }
.tinymce-content h1{font-size:2rem;font-weight:700;margin:1.2em 0 .6em}
.tinymce-content h2{font-size:1.5rem;font-weight:700;margin:1.1em 0 .55em}
.tinymce-content h3{font-size:1.25rem;font-weight:600;margin:1em 0 .5em}
.tinymce-content p {margin:.75em 0}

.gallery-panel { width:min(96vw,1100px); max-height:88vh; }
@supports not (aspect-ratio: 16/9) {
  .ar-16-9 { position:relative; }
  .ar-16-9::before { content:""; display:block; padding-top:56.25%; }
  .ar-16-9 > * { position:absolute; inset:0; }
}
  /* put the highest practical z-index */
  .gallery-backdrop{ z-index:2147483646 !important; }
  .gallery-modal{ z-index:2147483647 !important; }
  /* optional: dim TinyMCE toolbars when gallery is open */
  html.gallery-open .tox, html.gallery-open .mce-content-body { pointer-events:none; }
  .lock-scroll { overflow: hidden !important; }
  .gallery-scroll { -webkit-overflow-scrolling: touch; overscroll-behavior: contain; }
  #admin-dashboard { scroll-behavior: smooth; }
  #main, #blogs, #promos { scroll-margin-top: 72px; }

  /* no offset on forms so they align flush to the top */
  #add-blog, #edit-blog, #add-promo, #edit-promo { scroll-margin-top: 0; }
</style>


<script>
// mark when Alpine boots
document.addEventListener('alpine:init', () => { window.__ALPINE_OK__ = true; }, { once:true });

// always uncloak after a grace period if Alpine didn't finish
setTimeout(() => {
  if (!window.__ALPINE_OK__) {
    document
      .querySelectorAll('[x-cloak]:not([data-critical-cloak])')
      .forEach(n => n.removeAttribute('x-cloak'));
  }
}, 4000);

</script>


<script>
/* ================== Toast store (Alpine) ================== */
(function () {
  function installToastStore () {
    if (!window.Alpine) return;
    if (Alpine.store('toasts')) return;

    Alpine.store('toasts', {
      list: [],
      _id: 0,
      push(typeOrObj, message, title){
        const id = ++this._id;
        let t;
        if (typeof typeOrObj === 'object' && typeOrObj) {
          const o = typeOrObj;
          t = {
            id, show: true,
            type:  o.type || 'info',
            title: o.title || (o.type==='success'?'Success': o.type==='error'?'Error': o.type==='warning'?'Warning':'Notice'),
            message: o.message || '',
            variant: o.variant || ''
          };
        } else {
          const type = typeOrObj || 'info';
          t = {
            id, show: true,
            type,
            title: title || (type==='success'?'Success': type==='error'?'Error': type==='warning'?'Warning':'Notice'),
            message: message || '',
            variant: ''
          };
        }
        this.list.push(t);
        setTimeout(() => this.remove(id), 5000);
        return id;
      },

      remove(id){
        const i = this.list.findIndex(x => x.id===id);
        if (i>-1){
          this.list[i].show = false;
          setTimeout(() => this.list.splice(i,1), 180);
        }
      },
      clear(){ this.list = []; }
    });
  }
  if (window.Alpine) installToastStore();
  document.addEventListener('alpine:init', installToastStore, { once: true });
})();
</script>

<script>
/* Proper $store.result that opens the modal (with optional auto-close) */
(function () {
  function installResultStore () {
    if (!window.Alpine) return;
    Alpine.store('result', {
      open: false,
      type: 'success',
      title: '',
      message: '',
      timerId: null,

      show(opts = {}) {
        // clear any pending auto-close
        if (this.timerId) { clearTimeout(this.timerId); this.timerId = null; }

        this.type = opts.type || 'success';
        this.title = opts.title || (this.type === 'success' ? 'Success' : 'Notice');
        this.message = opts.message || '';
        this.open = true;

        // optional auto close: pass { autoMs: 1800 }
        const ms = Number(opts.autoMs || 0);
        if (ms > 0) this.timerId = setTimeout(() => this.close(), ms);
      },
      close(){
        this.open = false;
        if (this.timerId) { clearTimeout(this.timerId); this.timerId = null; }
      }
    });
  }
  if (window.Alpine) installResultStore();
  document.addEventListener('alpine:init', installResultStore, { once: true });
})();
</script>



<script>
/* ================== Confirm modal store ================== */
(function () {
  function installConfirmStore () {
    if (!window.Alpine) return;
    if (Alpine.store('confirm')) return;

    Alpine.store('confirm', {
      open: false,
      title: 'Are you sure?', message: '', variant: '',
      actionLabel: 'Confirm', cancelLabel: 'Cancel', onConfirm: null,
      ask(opts = {}){
        this.title       = opts.title || 'Are you sure?';
        this.message     = opts.message || '';
        this.variant     = opts.variant || '';
        this.actionLabel = opts.actionLabel || 'Confirm';
        this.cancelLabel = opts.cancelLabel || 'Cancel';
        this.onConfirm   = (typeof opts.onConfirm === 'function') ? opts.onConfirm : null;
        this.open = true;
        setTimeout(() => document.getElementById('confirm-primary-btn')?.focus(), 20);
      },
      confirm(){
        const fn = this.onConfirm;
        this.open = false;
        this.onConfirm = null;
        try { fn && fn(); } catch(_) {}
      },
      cancel(){
        this.open = false;
        this.onConfirm = null;
      }
    });
  }
  if (window.Alpine) installConfirmStore();
  document.addEventListener('alpine:init', installConfirmStore, { once: true });
})();
</script>

<script>
/* ================== Seed flashes (resilient) ================== */
(function(){
  const flashes = @json($flashToasts);
  const successPopup = @json($flashSuccessPopup);

  function seedNow(){
    try{
      if (window.__DASH_FLASHES_SEEDED__) return true;
      if (!window.Alpine || !Alpine.store) return false;

      const T = Alpine.store('toasts');
      const R = Alpine.store('result');
      if (!T || !R) return false;

      (flashes || []).forEach(f => T.push(f));
      if (successPopup) R.show({ type:'success', title:'Success', message: successPopup, autoMs: 1800 });

      window.__DASH_FLASHES_SEEDED__ = true;
      return true;
    } catch { return false; }
  }

  // Try immediately
  if (!seedNow()){
    // Keep trying a bit longer (4s) in case Alpine/stores are late
    let tries = 0;
    const iv = setInterval(() => { if (seedNow() || ++tries > 80) clearInterval(iv); }, 50);

    // Fire on Alpine boot and on full load as well
    document.addEventListener('alpine:init', () => setTimeout(seedNow, 0), { once:true });
    window.addEventListener('load', () => setTimeout(seedNow, 0), { once:true });
  }
})();
</script>
<!-- ==== GALLERY STORE (single) ==== -->
<script>
(function () {
  function installGalleryStore () {
    if (!window.Alpine) return;

    Alpine.store('gallery', {
      // state
      isOpen: false,
      loading: false,
      q: '',
      files: [],
      fetchUrl: @json($mediaListUrl),
      _lastActive: null,

      // options
      onlyImages: true,
      allowedImageExt: ['jpg','jpeg','png','webp','gif','bmp','svg'],
      _onSelect: null,

      // open/close
      open(opts = {}) {
        // apply options
        this.onlyImages = (opts.onlyImages ?? true);
        this._onSelect  = (typeof opts.onSelect === 'function') ? opts.onSelect : null;

        // open modal
        this.isOpen = true;
        this._lastActive = document.activeElement;

        // scroll lock
        const y = window.scrollY || document.documentElement.scrollTop || 0;
        document.documentElement.dataset.galleryScrollY = y;
        document.body.style.position = 'fixed';
        document.body.style.top = `-${y}px`;
        document.body.style.left = '0';
        document.body.style.right = '0';
        document.body.style.width = '100%';
        document.documentElement.classList.add('gallery-open');

        if (!this.files.length) this.load();
        setTimeout(() => document.getElementById('gallery-search')?.focus(), 20);
      },

      close() {
        this.isOpen = false;

        // restore scroll
        const y = parseInt(document.documentElement.dataset.galleryScrollY || '0', 10);
        document.body.style.position = '';
        document.body.style.top = '';
        document.body.style.left = '';
        document.body.style.right = '';
        document.body.style.width = '';
        window.scrollTo(0, y);
        document.documentElement.classList.remove('gallery-open');

        this.q = '';
        if (this._lastActive?.focus) setTimeout(() => this._lastActive.focus(), 20);
      },

      // data
      async load() {
        this.loading = true;
        try {
          const r = await fetch(this.fetchUrl, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
          });

          let d = null;
          try { d = await r.json(); } catch { d = null; }

          // accept several shapes
          const raw = Array.isArray(d) ? d
                    : Array.isArray(d?.files)   ? d.files
                    : Array.isArray(d?.data)    ? d.data
                    : Array.isArray(d?.items)   ? d.items
                    : Array.isArray(d?.payload) ? d.payload
                    : [];

          const toAbs = (u) => {
            if (!u) return '';
            try { new URL(u); return u; } catch {}
            if (u.startsWith('//')) return location.protocol + u;
            if (u.startsWith('/'))  return location.origin + u;
            return u;
          };

          this.files = raw.map((f, i) => {
            const fullRaw  = f?.url ?? f?.original ?? f?.location ?? f?.path ?? '';
            const thumbRaw = f?.thumb ?? f?.thumbnail ?? f?.thumbnail_url ?? f?.preview ?? f?.small ?? '';
            const full  = toAbs(String(fullRaw));
            const thumb = toAbs(String(thumbRaw || fullRaw));
            const name  = String(f?.name || full.split('/').pop() || `file_${i}`);
            const ext   = (name.split('.').pop() || '').toLowerCase();
            const isImage = this.allowedImageExt.includes(ext) || (String(f?.mime || '')).startsWith('image/');
            return {
              ...f,
              name, ext, isImage,
              full, thumb,
              url: full,                    // keep url for backwards compat
              __key: f?.id ?? f?.uuid ?? f?.path ?? f?.url ?? `idx_${i}`,
            };
          });

        } catch (_) {
          Alpine.store('toasts')?.push('error','Failed to load gallery.');
        } finally {
          this.loading = false;
        }
      },

      filtered() {
        const q = this.q.trim().toLowerCase();
        let list = this.files;
        if (this.onlyImages) list = list.filter(f => !!f.isImage);
        if (!q) return list;
        return list.filter(f =>
          (f.name  || '').toLowerCase().includes(q) ||
          (f.full  || '').toLowerCase().includes(q) ||
          (f.thumb || '').toLowerCase().includes(q)
        );
      },

      // helpers
      copy(url) {
        (async () => {
          try {
            if (navigator.clipboard && window.isSecureContext) {
              await navigator.clipboard.writeText(url);
              Alpine.store('toasts')?.push('success','Copied URL.');
              return;
            }
          } catch {}
          const ta = Object.assign(document.createElement('textarea'), { value: url });
          ta.style.position = 'fixed'; ta.style.left = '-9999px';
          document.body.appendChild(ta); ta.select(); document.execCommand('copy'); ta.remove();
          Alpine.store('toasts')?.push('success','Copied URL.');
        })();
      },

      select(urlOrFile) {
        const f = typeof urlOrFile === 'string'
          ? (this.files.find(x => x.full === urlOrFile) || { full: urlOrFile, name: '', isImage: true })
          : (urlOrFile || {});

        if (this.onlyImages && f.isImage === false) {
          Alpine.store('toasts')?.push('warning','Please pick an image file.');
          return;
        }

        const detail = { url: f.full, file: f }; // keep url for compatibility
        try {
          if (this._onSelect) this._onSelect(detail);
          window.dispatchEvent(new CustomEvent('gallery:select', { detail }));
          Alpine.store('toasts')?.push('success','Thumbnail selected.');
          this.close();
        } catch {
          Alpine.store('toasts')?.push('error','Failed to select thumbnail.');
        }
      }
    });
  }

  if (window.Alpine) installGalleryStore();
  document.addEventListener('alpine:init', installGalleryStore, { once: true });
})();
</script>




<!-- ==== GALLERY MODAL (teleport to body) ==== -->
<template x-teleport="body">
  <div x-data x-cloak id="gallery-modal-wrapper" data-critical-cloak>
    <!-- Backdrop -->
    <div
      x-show="$store.gallery && $store.gallery.isOpen"
      x-transition.opacity
      class="fixed inset-0 bg-black/50"
      style="z-index:2147483646">
    </div>

    <!-- Panel -->
    <div
      x-show="$store.gallery && $store.gallery.isOpen"
      class="fixed inset-0 p-4 grid place-items-center min-h-0"
      @keydown.escape.window="$store.gallery && $store.gallery.close()"
      @click.self="$store.gallery && $store.gallery.close()"
      style="z-index:2147483647">


      <div
      x-show="$store.gallery && $store.gallery.isOpen"
      class="gallery-panel w-full max-w-6xl rounded-2xl bg-white shadow-2xl ring-1 ring-black/5 flex flex-col min-h-0 overflow-hidden"
      role="dialog" aria-modal="true" aria-labelledby="gallery-title"
      style="max-height:88vh">


        <div class="px-5 py-4 border-b flex items-center gap-3">
          <h3 id="gallery-title" class="text-lg font-semibold text-slate-900">Storage Gallery</h3>
          <div class="ml-auto flex items-center gap-2">
            <input id="gallery-search" type="search" x-model="$store.gallery.q"
                   placeholder="Filter by name or URL…" class="rounded-lg border px-3 py-2 w-56 md:w-72">
            <button type="button" class="rounded-lg border px-3 py-2 hover:bg-gray-50"
                    @click="$store.gallery.load()" :disabled="$store.gallery.loading">
              <i class="fa-solid fa-rotate-right mr-1"></i> Refresh
            </button>
            <button type="button" class="rounded-lg border px-3 py-2 hover:bg-gray-50"
                    @click="$store.gallery.close()">
              <i class="fa-solid fa-xmark mr-1"></i> Close
            </button>
          </div>
        </div>

        <div class="p-4 flex-1 min-h-0 overflow-y-auto gallery-scroll">
          <template x-if="$store.gallery.loading">
            <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
              <template x-for="i in 8" :key="'skel_'+i">
                <div class="rounded-xl border bg-white overflow-hidden">
                  <!-- 16:9 skeleton using padding-bottom trick -->
                  <div class="relative bg-gray-100 animate-pulse">
                    <div class="w-full" style="padding-bottom:56.25%"></div>
                  </div>
                  <div class="p-3 space-y-2">
                    <div class="h-3 bg-gray-100 rounded w-3/4"></div>
                    <div class="h-3 bg-gray-100 rounded w-1/2"></div>
                  </div>
                </div>
              </template>
            </div>
          </template>

          <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4" x-show="!$store.gallery.loading">
            <template x-for="f in $store.gallery.filtered()" :key="f.__key">
              <div class="rounded-xl border overflow-hidden bg-white flex flex-col" x-data="{ copied:false, t:null }">
                <!-- CROPPED THUMBNAIL (no aspect-ratio prop) -->
                <div class="relative overflow-hidden bg-gray-100">
                  <!-- 16:9 spacer -->
                  <div class="w-full" style="padding-bottom:56.25%"></div>
                  <!-- image absolutely covers the box -->
                  <img
                    :src="f.thumb"
                    :alt="f.name || 'Image'"
                    loading="lazy"
                    decoding="async"
                    class="absolute inset-0 w-full h-full object-cover object-center">
                </div>

                <div class="p-3 space-y-1">
                  <div class="text-sm font-medium truncate" x-text="f.name || '—'"></div>
                  <div class="text-xs text-slate-500 truncate" x-text="f.full"></div>
                  <div class="mt-2 flex items-center gap-2">
                    <a :href="f.full" target="_blank" class="inline-flex items-center gap-1 rounded border px-2 py-1 text-sm hover:bg-gray-50">
                      <i class="fa-regular fa-eye"></i> Open
                    </a>
                    <button type="button" class="inline-flex items-center gap-1 rounded border px-2 py-1 text-sm hover:bg-emerald-50"
                            @click="$store.gallery.select(f.full)">
                      <i class="fa-regular fa-image"></i> Use
                    </button>
                    <button type="button" :disabled="copied"
                            @click="$store.gallery.copy(f.full); copied=true; clearTimeout(t); t=setTimeout(()=>copied=false,1200)"
                            class="inline-flex items-center gap-1 rounded border px-2 py-1 text-sm hover:bg-gray-50"
                            :class="copied ? 'bg-emerald-600 text-white hover:bg-emerald-700' : ''">
                      <template x-if="!copied"><span><i class="fa-regular fa-copy"></i> Copy</span></template>
                      <template x-if="copied"><span><i class="fa-solid fa-check"></i> Copied!</span></template>
                    </button>
                  </div>
                </div>
              </div>
            </template>
          </div>

          <div x-show="!$store.gallery.loading && !$store.gallery.filtered().length"
               class="py-12 text-center text-slate-600">No images found.</div>
        </div>
      </div>
    </div>
  </div>
</template>



<!-- ================== Toasts UI ================== -->
<div x-data x-cloak data-critical-cloak class="fixed top-4 right-4 z-[200] space-y-3 pointer-events-none" id="toast-root">
  <template x-for="t in ($store.toasts ? $store.toasts.list : [])" :key="t.id">
    <div
      x-show="t.show" x-transition.opacity
      class="pointer-events-auto w-80 max-w-[92vw] rounded-xl border shadow-lg ring-1 ring-black/5 p-4"
      :class="{
        'bg-red-600 text-white border-red-700': t.variant==='danger-solid',
        'bg-white border-l-4 border-l-emerald-600 text-slate-900': t.variant!=='danger-solid' && t.type==='success',
        'bg-white border-l-4 border-l-amber-600 text-slate-900'  : t.variant!=='danger-solid' && t.type==='warning',
        'bg-white border-l-4 border-l-red-600 text-slate-900'    : t.variant!=='danger-solid' && t.type==='error',
        'bg-white border-l-4 border-l-slate-600 text-slate-900'  : t.variant!=='danger-solid' && !['success','warning','error'].includes(t.type)
            }"
      role="status" aria-live="polite"
    >
      <div class="flex items-start gap-3">
        <div class="mt-0.5">
          <i class="fa-solid"
          :class="{
            'fa-circle-xmark text-white'        : t.variant==='danger-solid',
            'fa-circle-check text-emerald-600'  : t.variant!=='danger-solid' && t.type==='success',
            'fa-triangle-exclamation text-amber-600': t.variant!=='danger-solid' && t.type==='warning',
            'fa-circle-xmark text-red-600'      : t.variant!=='danger-solid' && t.type==='error',
            'fa-circle-info text-slate-600'     : t.variant!=='danger-solid' && !['success','warning','error'].includes(t.type)
          }"></i>
        </div>
        <div class="min-w-0">
          <p class="font-semibold truncate"
          :class="t.variant==='danger-solid' ? 'text-white' : 'text-slate-900'"
          x-text="t.title"></p>
        <p class="text-sm break-words"
          :class="t.variant==='danger-solid' ? 'text-white/90' : 'text-slate-700'"
          x-text="t.message"></p>

        </div>
        <button type="button" class="ml-auto"
        :class="t.variant==='danger-solid' ? 'text-white/80 hover:text-white' : 'text-slate-400 hover:text-slate-600'"
                @click="$store.toasts && $store.toasts.remove(t.id)">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
    </div>
  </template>
</div>

<!-- ================== Center “Result” Popup UI (Success) ================== -->
<div x-data x-cloak data-critical-cloak>
  <div x-show="$store.result && $store.result.open" x-transition.opacity class="fixed inset-0 bg-black/40 z-[295]"></div>
  <div x-show="$store.result && $store.result.open" x-transition
       class="fixed inset-0 z-[296] grid place-items-center p-4" @keydown.escape.window="$store.result && $store.result.close()">
    <div id="result-modal-card" role="dialog" aria-modal="true"
         class="rounded-2xl bg-white shadow-2xl ring-1 ring-black/5 overflow-hidden">
      <div class="p-6 text-center">
        <div class="mx-auto mb-3 w-12 h-12 grid place-items-center rounded-full ring-2"
             :class="{
               'bg-emerald-100 text-emerald-700 ring-emerald-200': $store.result && $store.result.type==='success',
               'bg-amber-100 text-amber-700 ring-amber-200'    : $store.result && $store.result.type==='warning',
               'bg-red-100 text-red-700 ring-red-200'          : $store.result && $store.result.type==='danger',
               'bg-slate-100 text-slate-700 ring-slate-200'    : $store.result && $store.result.type==='info'
             }">
          <i class="fa-solid"
             :class="{
               'fa-circle-check'         : $store.result && $store.result.type==='success',
               'fa-triangle-exclamation' : $store.result && $store.result.type==='warning',
               'fa-circle-xmark'         : $store.result && $store.result.type==='danger',
               'fa-circle-info'          : $store.result && $store.result.type==='info'
             }"></i>
        </div>
        <h3 class="text-lg text-slate-900" x-text="$store.result ? $store.result.title : ''"></h3>
        <p class="mt-1 text-sm text-slate-600" x-text="$store.result ? $store.result.message : ''"></p>
        <div class="mt-5">
          <!-- in the Result modal -->
        <button id="result-ok-btn" type="button"
                class="inline-flex items-center justify-center rounded-lg bg-emerald-600 text-white px-4 py-2 text-sm font-semibold hover:bg-emerald-700"
                @click="$store.result && $store.result.close()">
          <i class="fa-solid fa-circle-check mr-2"></i> OK
        </button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ================== Confirm Modal UI ================== -->
<div x-data x-cloak data-critical-cloak>
  <div x-show="$store.confirm && $store.confirm.open" x-transition.opacity class="fixed inset-0 bg-black/40 z-[300]"></div>
  <div x-show="$store.confirm && $store.confirm.open" x-transition
       class="fixed inset-0 z-[301] grid place-items-center p-4" @keydown.escape.window="$store.confirm && $store.confirm.cancel()">
    <div role="dialog" aria-modal="true" aria-labelledby="confirm-title" aria-describedby="confirm-desc"
         class="w-[min(92vw,28rem)] rounded-2xl bg-white shadow-2xl ring-1 ring-black/5">
      <div class="p-5">
        <div class="flex items-start gap-3">
          <div class="flex-shrink-0 rounded-full p-2"
               :class="{
                 'bg-red-100 text-red-700'        : $store.confirm && $store.confirm.variant==='danger',
                 'bg-amber-100 text-amber-700'    : $store.confirm && $store.confirm.variant==='warning',
                 'bg-emerald-100 text-emerald-700': $store.confirm && $store.confirm.variant==='success',
                 'bg-slate-100 text-slate-700'    : !($store.confirm && $store.confirm.variant)
               }">
            <i class="fa-solid"
               :class="{
                 'fa-triangle-exclamation': $store.confirm && ($store.confirm.variant==='danger' || $store.confirm.variant==='warning'),
                 'fa-rotate-left'        : $store.confirm && $store.confirm.variant==='success',
                 'fa-circle-question'    : !($store.confirm && $store.confirm.variant)
               }"></i>
          </div>
          <div class="min-w-0">
            <h3 id="confirm-title" class="text-lg font-semibold text-slate-900" x-text="$store.confirm ? $store.confirm.title : ''"></h3>
            <p id="confirm-desc" class="mt-1 text-sm text-slate-600" x-text="$store.confirm ? $store.confirm.message : ''"></p>
          </div>
          <button class="ml-auto text-slate-400 hover:text-slate-600" @click="$store.confirm && $store.confirm.cancel()">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>

        <div class="mt-6 flex items-center justify-end gap-3">
          <button type="button"
                  class="inline-flex items-center justify-center rounded-lg border px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                  @click="$store.confirm && $store.confirm.cancel()"
                  x-text="$store.confirm ? ($store.confirm.cancelLabel || 'Cancel') : 'Cancel'"></button>

          <button type="button" id="confirm-primary-btn"
                  class="inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-semibold text-white"
                  :class="{
                    'bg-red-600 hover:bg-red-700'        : $store.confirm && $store.confirm.variant==='danger',
                    'bg-amber-600 hover:bg-amber-700'    : $store.confirm && $store.confirm.variant==='warning',
                    'bg-emerald-600 hover:bg-emerald-700': $store.confirm && $store.confirm.variant==='success',
                    'bg-slate-800 hover:bg-slate-900'    : !($store.confirm && $store.confirm.variant)
                  }"
                  @click="$store.confirm && $store.confirm.confirm()"
                  x-text="$store.confirm ? ($store.confirm.actionLabel || 'Confirm') : 'Confirm'"></button>
        </div>
      </div>
    </div>
  </div>
</div>
@endpush

@push('scripts')
<script>
/* ============== Blog/Promo form helpers ============== */
function blogForm(initial){
  return {
    title:      initial.title ?? '',
    slug:       initial.slug ?? '',
    slugEdited: initial.slugEdited ?? false,
    thumbUrl:   initial.thumbUrl ?? null,
    baseUrl:    (initial.baseUrl || window.location.origin).replace(/\/+$/,''),
    prefixPath: (initial.prefixPath || '').replace(/^\/+|\/+$/g,''),
    _blobUrl:   null,

    // --- URL preview helpers ---
    ymdFromPicker(v){
      // accepts 'YYYY-MM-DDTHH:mm' or 'YYYY-MM-DD'
      if(!v) return null;
      const d = String(v).split('T')[0] || '';
      if(!/^\d{4}-\d{2}-\d{2}$/.test(d)) return null;
      const [Y,M,D] = d.split('-');
      return `${Y}/${M}/${D}`;
    },
    ymdToday(){
      const now = new Date();
      const Y = now.getFullYear();
      const M = String(now.getMonth()+1).padStart(2,'0');
      const D = String(now.getDate()).padStart(2,'0');
      return `${Y}/${M}/${D}`;
    },
    plannedYmd(){
      // reads confirmed scheduled date from hidden input set by pubBox()
      const el = this.$root?.querySelector('input[name="scheduled_publish_date"]');
      const picked = this.ymdFromPicker(el?.value || '');
      return picked || this.ymdToday();
    },
    urlPreview(){
      const s = (this.slug || this.slugify(this.title) || 'your-slug').replace(/^\/+|\/+$/g,'');
      const pre = this.prefixPath ? `/${this.prefixPath}` : '';
      return `${this.baseUrl}${pre}/${this.plannedYmd()}/${s}`;
    },

    init(){
      this._handler = (e) => this._onGallerySelect(e);
      window.addEventListener('gallery:select', this._handler);
    },
    _onGallerySelect(e){
      const url  = e?.detail?.url || e?.detail?.file?.full || null;
      const file = e?.detail?.file || {};
      if (!url) return;

      // guard: must be image (store enforces this, but double-check)
      if (file && file.isImage === false) {
        Alpine.store('toasts')?.push('warning', 'Please select an image.');
        return;
      }

      this.thumbUrl = url;

      // write hidden input for backend
      if (this.$refs?.featured_from_gallery) {
        this.$refs.featured_from_gallery.value = url;
      }

      // ensure local <input type="file"> is cleared so we don’t upload both
      const up = this.$root?.querySelector('input[type=file][name="featured_image"]');
      if (up) up.value = '';
    },


    onTitleInput(e){
      this.title = e.target.value;
      if (!this.slugEdited) this.slug = this.slugify(this.title);
    },
    onSlugInput(e){
      this.slugEdited = true;
      this.slug = e.target.value;
    },
    slugify(s){
      return (s || '')
        .toString().trim().toLowerCase()
        .replace(/[^a-z0-9\s-]/g,'')
        .replace(/\s+/g,'-')
        .replace(/-+/g,'-')
        .replace(/^-|-$/g,'');
    },
    previewThumb(e){
      const file = e?.target?.files?.[0] ?? null;
      if (!file) {
        if (this._blobUrl) { URL.revokeObjectURL(this._blobUrl); this._blobUrl = null; }
        this.thumbUrl = initial.thumbUrl ?? null;
        return;
      }
      // user picked a local file → clear any Gallery URL so controller uses the upload
      if (this.$refs?.featured_from_gallery) {
        this.$refs.featured_from_gallery.value = '';
      }
      try {
        const reader = new FileReader();
        reader.onload = () => { this.thumbUrl = reader.result; };
        reader.readAsDataURL(file);
      } catch(_) {
        try {
          if (this._blobUrl) URL.revokeObjectURL(this._blobUrl);
          this._blobUrl = URL.createObjectURL(file);
          this.thumbUrl = this._blobUrl;
        } catch(__) { this.thumbUrl = null; }
      }
    }

  }
}

/* ============== Dashboard state (Alpine) ============== */
function dashPage(){
  return {
    openMain: true,
    openBlogsList: false,
    openAdd: false,
    openEdit: false,
    openPromosList: false,
    openPromoAdd: false,
    openPromoEdit: false,

    csrf: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',

    init(){
      this.syncFromUrl();
      window.addEventListener('popstate', ()=>this.syncFromUrl());
      // make dash methods callable from any nested Alpine component
      window.__dash = this;
    },

    copyToClipboard(text){
      try{
        const doToast = (type,msg)=> (window.Alpine?.store && Alpine.store('toasts')?.push(type, msg));
        if (navigator.clipboard && window.isSecureContext){
          navigator.clipboard.writeText(text).then(() => { doToast?.('success','Site URL copied to clipboard.'); })
          .catch(() => { const ta = document.createElement('textarea'); ta.value = text; document.body.appendChild(ta); ta.select(); document.execCommand('copy'); ta.remove(); doToast?.('success','Site URL copied to clipboard.'); });
        } else {
          const ta = document.createElement('textarea'); ta.value = text; document.body.appendChild(ta); ta.select(); document.execCommand('copy'); ta.remove(); doToast?.('success','Site URL copied to clipboard.');
        }
      }catch(e){ window.Alpine?.store('toasts')?.push('error','Failed to copy URL.'); }
    },

    syncFromUrl(){
      // accept both ?show=… and #hash
      let show = new URLSearchParams(location.search).get('show');
      const hash = (location.hash || '').replace('#','');
      const known = new Set(['main','blogs','add-blog','edit-blog','promos','add-promo','edit-promo']);

      if (!show && known.has(hash)) show = hash;

      const hasEditBlog  = {{ isset($editArticle) ? 'true' : 'false' }};
      const hasEditPromo = {{ isset($editPromo)   ? 'true' : 'false' }};

      this.reset();

      switch (show) {
        case 'blogs':      this.openBlogsList = true; break;
        case 'add-blog':   this.openAdd = true;       break;
        case 'edit-blog':  this.openEdit = true;      break;
        case 'promos':     this.openPromosList = true;break;
        case 'add-promo':  this.openPromoAdd = true;  break;
        case 'edit-promo': this.openPromoEdit = true; break;
        default:           this.openMain = true;
      }

      if (show==='edit-blog' && !hasEditBlog)   { this.reset(); this.openBlogsList = true; }
      if (show==='edit-promo' && !hasEditPromo) { this.reset(); this.openPromosList = true; }

      const id =
        this.openEdit        ? 'edit-blog'   :
        this.openAdd         ? 'add-blog'    :
        this.openBlogsList   ? 'blogs'       :
        this.openPromoEdit   ? 'edit-promo'  :
        this.openPromoAdd    ? 'add-promo'   :
        this.openPromosList  ? 'promos'      : 'main';

      this.$nextTick(() => {
        const el = document.getElementById(id);
        if (!el) return;
        el.scrollIntoView({ behavior: 'smooth', block: 'start', inline: 'nearest' });
      });
    },


    navTo(show){
      const url = new URL(location);
      url.searchParams.set('show', show);
      url.hash = '#'+show;
      history.pushState(null,'',url);
      this.syncFromUrl();
    },

    goMain(){ this.navTo('main'); },
    goBlogs(){ this.navTo('blogs'); },
    goAddBlog(){ this.navTo('add-blog'); },
    goPromos(){ this.navTo('promos'); },
    goAddPromo(){ this.navTo('add-promo'); },

    reset(){
      this.openMain=false; this.openBlogsList=false; this.openAdd=false; this.openEdit=false;
      this.openPromosList=false; this.openPromoAdd=false; this.openPromoEdit=false;
    }
  }
}

/* ============== Category Manager (AJAX) ============== */
(() => {
  async function tryJson(res) { try { return await res.json(); } catch (_) { return null; } }
  window.catMgr = function catMgr(opts){
    return {
      cats: Array.isArray(opts.cats) ? opts.cats.slice() : [],
      selectedId: opts.selectedId ? String(opts.selectedId) : '',
      newName: '',
      loading: false,
      flash: '',
      flashType: 'success',
      storeUrl: opts.storeUrl,
      destroyUrlTpl: opts.destroyUrlTpl,
      csrf: opts.csrf,

      get canCreate(){
        const name = (this.newName || '').trim().toLowerCase();
        if (!name) return false;
        return !this.cats.some(c => (c.name || '').trim().toLowerCase() === name);
      },

      async createCategory(){
        if (!this.canCreate || this.loading) return;
        this.loading = true; this.flash = '';
        try {
          const res = await fetch(this.storeUrl, {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': this.csrf,
              'Accept': 'application/json',
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({ name: this.newName })
          });

          if (res.ok) {
            const data = await tryJson(res);
            const createdName = (data && data.name) ? data.name : (this.newName || 'Category');
            if (data && data.id) {
              this.cats.push({ id: data.id, name: data.name, slug: data.slug || '' });
              this.selectedId = String(data.id);
              this.newName = '';
              this.flash = 'Category created.'; this.flashType = 'success';
            } else {
              this.flash = 'Created, but response unexpected.'; this.flashType = 'success';
            }
            Alpine.store('toasts')?.push('success', `“${createdName}” created.`);
            Alpine.store('result')?.show({ type:'success', title:'Saved', message:`${createdName} created successfully.`, autoMs:1500 });
          } else {
            const text = (await res.text()).slice(0, 200);
            this.flash = text || 'Failed to create category.'; this.flashType = 'error';
            Alpine.store('toasts')?.push('error', this.flash);
          }
        } catch (e) {
          this.flash = 'Network error. Please try again.'; this.flashType = 'error';
          Alpine.store('toasts')?.push('error', this.flash);
        } finally {
          this.loading = false;
        }
      },

      async deleteCategory(id, name){
        if (this.loading) return;

        const proceed = async () => {
          this.loading = true; this.flash = '';
          try {
            const url = this.destroyUrlTpl.replace('__ID__', id);
            const form = new FormData();
            form.append('_token', this.csrf);
            form.append('_method', 'DELETE');

            const res = await fetch(url, { method: 'POST', headers: { 'Accept': 'application/json' }, body: form });

            if (res.ok) {
              this.cats = this.cats.filter(c => String(c.id) !== String(id));
              if (String(this.selectedId) === String(id)) this.selectedId = '';
              this.flash = 'Category removed.'; this.flashType = 'success';
              Alpine.store('toasts')?.push('success', 'Category removed.');
              Alpine.store('result')?.show({ type:'success', title:'Deleted', message:'Category removed successfully.', autoMs:1500 });
            } else {
              const text = (await res.text()).slice(0, 200);
              this.flash = text || 'Failed to delete category.'; this.flashType = 'error';
              Alpine.store('toasts')?.push('error', this.flash);
            }
          } catch (e) {
            this.flash = 'Network error. Please try again.'; this.flashType = 'error';
            Alpine.store('toasts')?.push('error', this.flash);
          } finally {
            this.loading = false;
          }
        };

        const S = window.Alpine?.store?.('confirm');
        if (S?.ask) {
          S.ask({
            title: 'Delete category?',
            message: `Delete “${name}”? Articles using it will be set to “None”.`,
            variant: 'danger',
            actionLabel: 'Delete',
            onConfirm: proceed
          });
        } else if (confirm(`Delete “${name}”? Articles using it will be set to “None”.`)) {
          proceed();
        }
      }
    };
  };
})();
</script>

<script>
/* ================== Tag Manager (CSV-backed) ================== */
(() => {
  const toArray = (v) => {
    if (Array.isArray(v)) return v;
    if (typeof v === 'string') {
      return v.split(/[,;\n]+/).map(s => s.trim()).filter(Boolean);
    }
    return [];
  };
  const normalize = (s) => (s || '').replace(/^#+/, '').trim(); // strip leading #, spaces
  const uniqCI = (arr) => {
    const seen = new Set(), out = [];
    for (const t of arr) {
      const key = t.toLowerCase();
      if (!seen.has(key)) { seen.add(key); out.push(t); }
    }
    return out;
  };

  window.tagMgr = function tagMgr(opts){
    return {
      tags: uniqCI(toArray(opts.initial || [])),
      newTag: '',
      loading: false,
      flash: '',
      flashType: 'success',

      init(){ this.syncHidden(); },

      get canAdd(){
        const n = normalize(this.newTag);
        if (!n) return false;
        return !this.tags.some(t => t.toLowerCase() === n.toLowerCase());
      },

      syncHidden(){
        if (this.$refs.hiddenTags) {
          this.$refs.hiddenTags.value = this.tags.join(', ');
        }
      },

      addToken(raw){
        const n = normalize(raw);
        if (!n) return;
        if (this.tags.some(t => t.toLowerCase() === n.toLowerCase())) {
          this.flash = 'Tag already added.'; this.flashType='warning';
          Alpine.store('toasts')?.push('warning','Tag already added.');
          return;
        }
        this.tags.push(n);
        this.syncHidden();
      },

      addFromInput(){
        const n = normalize(this.newTag);
        if (!n) return;
        this.addToken(n);
        this.newTag = '';
      },

      addTag(){ this.addFromInput(); },

      onKeydown(e){
        if (e.key === 'Enter' || e.key === ',') {
          e.preventDefault();
          this.addFromInput();
        }
      },

      onBlur(){
        this.addFromInput(); // add any lingering token
      },

      onPaste(e){
        const text = (e.clipboardData?.getData('text') || '').trim();
        if (!text) return;
        e.preventDefault();
        const parts = text.split(/[,;\n]+/).map(normalize).filter(Boolean);
        for (const p of parts) this.addToken(p);
        this.newTag = '';
      },

      removeTag(i){
        this.tags.splice(i,1);
        this.syncHidden();
      }
    };
  };
})();
</script>
@endpush

<div class="space-y-6">
  {{-- ====================== MAIN ====================== --}}
  <div x-show="openMain" x-transition id="main" class="space-y-6">
    <div class="flex items-center justify-between gap-2 flex-wrap">
      <div class="flex items-center gap-3">
        <h2 class="text-xl font-bold">Overview</h2>
      </div>
      <div class="flex items-center gap-2">
        <a href="{{ route('admin.dashboard') }}?show=blogs#blogs"
           @click.prevent="goBlogs"
           class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-300 hover:bg-gray-50">
          <i class="fa-regular fa-list-alt"></i> View Blogs
        </a>
        <a href="{{ route('admin.dashboard') }}?show=promos#promos"
           @click.prevent="goPromos"
           class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-300 hover:bg-gray-50">
          <i class="fa-solid fa-tags"></i> View Promos
        </a>
      </div>
    </div>

    {{-- KPIs --}}
    <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
      <div class="rounded-xl border bg-white p-5 flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg grid place-items-center bg-emerald-100 text-emerald-700">
          <i class="fa-regular fa-newspaper"></i>
        </div>
        <div>
          <div class="text-xs uppercase tracking-wide text-gray-500">Published Blogs</div>
          <div class="text-2xl font-bold">{{ $stats['blogs'] ?? 0 }}</div>
        </div>
      </div>

      <div class="rounded-xl border bg-white p-5 flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg grid place-items-center bg-sky-100 text-sky-700">
          <i class="fa-regular fa-file-lines"></i>
        </div>
        <div>
          <div class="text-xs uppercase tracking-wide text-gray-500">Published Promos</div>
          <div class="text-2xl font-bold">{{ $stats['promos'] ?? 0 }}</div>
        </div>
      </div>

      <div class="rounded-xl border bg-white p-5 flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg grid place-items-center bg-amber-100 text-amber-700">
          <i class="fa-regular fa-folder-open"></i>
        </div>
        <div>
          <div class="text-xs uppercase tracking-wide text-gray-500">Drafts (this page)</div>
          <div class="text-2xl font-bold">{{ $draftBlogsOnPage + $draftPromosOnPage }}</div>
        </div>
      </div>

      <div class="rounded-xl border bg-white p-5 flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg grid place-items-center bg-violet-100 text-violet-700">
          <i class="fa-regular fa-clock"></i>
        </div>
        <div>
          <div class="text-xs uppercase tracking-wide text-gray-500">Scheduled (this page)</div>
          <div class="text-2xl font-bold">{{ $scheduledBlogsOnPage + $scheduledPromosOnPage }}</div>
        </div>
      </div>
    </section>

    

    {{-- More KPIs --}}
    <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
  {{-- In Trash --}}
  <div class="rounded-xl border bg-white p-5 flex items-center gap-4">
    <div class="w-10 h-10 rounded-lg grid place-items-center bg-rose-100 text-rose-700">
      <i class="fa-regular fa-trash-can"></i>
    </div>
    <div>
      <div class="text-xs uppercase tracking-wide text-gray-500">In Trash</div>
      <div class="text-2xl font-bold">
        {{ ($trashedBlogsCount ?? 0) + ($trashedPromosCount ?? 0) }}
      </div>
    </div>
  </div>

  {{-- New This Week (page) --}}
  <div class="rounded-xl border bg-white p-5 flex items-center gap-4">
    <div class="w-10 h-10 rounded-lg grid place-items-center bg-indigo-100 text-indigo-700">
      <i class="fa-solid fa-calendar-week"></i>
    </div>
    <div>
      <div class="text-xs uppercase tracking-wide text-gray-500">New This Week (page)</div>
      <div class="text-2xl font-bold">{{ $newBlogsThisWeekOnPage }}</div>
    </div>
  </div>

  {{-- Blog Categories --}}
  <div class="rounded-xl border bg-white p-5 flex items-center gap-4">
    <div class="w-10 h-10 rounded-lg grid place-items-center bg-teal-100 text-teal-700">
      <i class="fa-solid fa-tag"></i>
    </div>
    <div>
      <div class="text-xs uppercase tracking-wide text-gray-500">Blog Categories</div>
      <div class="text-2xl font-bold">
        {{ is_countable($categories) ? count($categories) : 0 }}
      </div>
    </div>
  </div>

  {{-- Active Pop-ups --}}
  <div class="rounded-xl border bg-white p-5 flex items-center gap-4">
    <div class="w-10 h-10 rounded-lg grid place-items-center bg-amber-100 text-amber-700">
      <i class="fa-regular fa-window-restore"></i>
    </div>
    <div>
      <div class="text-xs uppercase tracking-wide text-gray-500">Active Pop-ups</div>
      <div class="text-2xl font-bold">
        {{ $activePopupCount }}
      </div>
    </div>
  </div>
</section>


    {{-- Analytics + Tools --}}
    <section class="grid gap-4 lg:grid-cols-3">
      {{-- Activity chart --}}
      <div class="lg:col-span-2 rounded-xl border bg-white p-5">
        <div class="flex items-center justify-between">
          <h3 class="text-sm font-semibold text-slate-900">Activity (last 14 days)</h3>
          <span class="text-xs text-slate-500">{{ \Carbon\Carbon::now('Asia/Manila')->format('M d, Y') }}</span>
        </div>
        <div class="mt-4">
          <canvas id="analyticsChart" height="110"></canvas>
        </div>
        <script id="analytics-data" type="application/json">
    {!! json_encode($chartPayload) !!}
    </script>
      </div>

      {{-- Helpful Tools --}}
      <div class="rounded-xl border bg-white p-5">
        <h3 class="text-sm font-semibold text-slate-900">Helpful Tools</h3>
        <div class="mt-3 grid gap-2">
          <button type="button"
                  class="inline-flex items-center justify-between rounded-lg border px-3 py-2 hover:bg-gray-50"
                  @click="copyToClipboard('{{ url('/') }}')">
            <span class="inline-flex items-center gap-2"><i class="fa-regular fa-copy"></i> Copy site URL</span>
            <span class="text-xs text-slate-500">{{ url('/') }}</span>
          </button>

          <a href="{{ route('admin.dashboard') }}?show=blogs#blogs"
             @click.prevent="goBlogs"
             class="inline-flex items-center justify-between rounded-lg border px-3 py-2 hover:bg-gray-50">
            <span class="inline-flex items-center gap-2"><i class="fa-regular fa-newspaper"></i> Manage Blogs</span>
            <i class="fa-solid fa-arrow-right"></i>
          </a>
          <a href="{{ route('admin.dashboard') }}?show=promos#promos"
             @click.prevent="goPromos"
             class="inline-flex items-center justify-between rounded-lg border px-3 py-2 hover:bg-gray-50">
            <span class="inline-flex items-center gap-2"><i class="fa-solid fa-tags"></i> Manage Promos</span>
            <i class="fa-solid fa-arrow-right"></i>
          </a>
        </div>
      </div>


      {{-- Recent items (this page) --}}
      <div class="col-span-full w-full basis-full rounded-xl border bg-white p-5">
          <h3 class="text-sm font-semibold text-slate-900">Recent Items (this page)</h3>

          <div class="mt-3 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            {{-- Recent Blogs --}}
            <div>
              <h4 class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Blogs</h4>
              <ul class="space-y-2">
                @forelse(collect($blogItems ?? [])->sortByDesc(fn($i)=>$i->created_at)->take(8) as $b)
                  @php
                    $d = !empty($b->created_at) ? $toPH($b->created_at)->format('M d, Y • g:i A') : '—';
                  @endphp
                  <li class="flex items-center justify-between rounded-lg border px-3 py-2">
                    <div class="min-w-0 flex items-center gap-2">
                      <span class="w-6 h-6 grid place-items-center rounded bg-emerald-100 text-emerald-700">
                        <i class="fa-regular fa-newspaper text-[10px]"></i>
                      </span>
                      <div class="truncate">
                        <div class="text-sm font-medium text-slate-800 truncate">{{ $b->title }}</div>
                        <div class="text-xs text-slate-500">Blog • {{ $d }}</div>
                      </div>
                    </div>
                    <a href="{{ route('admin.blogs.edit', $b) }}?show=edit-blog#edit-blog"
                      class="text-xs text-emerald-700 hover:text-emerald-800">Edit</a>
                  </li>
                @empty
                  <li class="text-sm text-slate-600">No blogs on this page.</li>
                @endforelse
              </ul>
            </div>

            {{-- Recent Promos --}}
            <div>
              <h4 class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Promos</h4>
              <ul class="space-y-2">
                @forelse(collect($promoItems ?? [])->sortByDesc(fn($i)=>$i->created_at)->take(8) as $p)
                  @php
                    $d = !empty($p->created_at) ? $toPH($p->created_at)->format('M d, Y • g:i A') : '—';
                  @endphp
                  <li class="flex items-center justify-between rounded-lg border px-3 py-2">
                    <div class="min-w-0 flex items-center gap-2">
                      <span class="w-6 h-6 grid place-items-center rounded bg-sky-100 text-sky-700">
                        <i class="fa-solid fa-tag text-[10px]"></i>
                      </span>
                      <div class="truncate">
                        <div class="text-sm font-medium text-slate-800 truncate">{{ $p->title }}</div>
                        <div class="text-xs text-slate-500">Promo • {{ $d }}</div>
                      </div>
                    </div>
                    <a href="{{ route('admin.promos.edit', $p) }}?show=edit-promo#edit-promo"
                      class="text-xs text-emerald-700 hover:text-emerald-800">Edit</a>
                  </li>
                @empty
                  <li class="text-sm text-slate-600">No promos on this page.</li>
                @endforelse
              </ul>
            </div>

            {{-- NEW: Recent Pop-ups --}}
            <div>
              <h4 class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Pop-ups</h4>
              <ul class="space-y-2">
                @forelse(collect($popupItems ?? [])->sortByDesc(fn($i)=>$i->created_at)->take(8) as $pop)
                  @php
                    $d = !empty($pop->created_at) ? $toPH($pop->created_at)->format('M d, Y • g:i A') : '—';
                  @endphp
                  <li class="flex items-center justify-between rounded-lg border px-3 py-2">
                    <div class="min-w-0 flex items-center gap-2">
                      <span class="w-6 h-6 grid place-items-center rounded bg-amber-100 text-amber-700">
                        <i class="fa-regular fa-bell text-[10px]"></i>
                      </span>
                      <div class="truncate">
                        <div class="text-sm font-medium text-slate-800 truncate">{{ $pop->title }}</div>
                        <div class="text-xs text-slate-500">Pop-up • {{ $d }}</div>
                      </div>
                    </div>
                    <a href="{{ route('admin.popups.edit', $pop) }}"
                      class="text-xs text-emerald-700 hover:text-emerald-800">
                      Edit
                    </a>
                  </li>
                @empty
                  <li class="text-sm text-slate-600">No pop-ups yet.</li>
                @endforelse
              </ul>
            </div>
          </div>
        </div>


    </section>



  </div>

  {{-- ====================== BLOGS: LIST ====================== --}}
  <div
      x-show="openBlogsList" x-transition id="blogs" class="space-y-4"
      x-data="blogsList({
        blogQuery:  @js($blogSearch),
        blogCat:    @js($blogCat),
        blogStatus: @js($blogStatus),
        blogCreator:@js($blogCreator),
        blogSort:   @js($blogSort)
      })"
      x-init="init()"
    >

    <div class="flex items-center justify-between gap-3 flex-wrap">
      <h2 class="text-xl font-bold">Blogs</h2>

      {{-- BLOG FILTER BAR --}}
      <form method="GET" action="{{ route('admin.dashboard') }}" class="flex flex-wrap gap-2 items-center" @submit.prevent>

        <input type="hidden" name="show" value="blogs">

        {{-- Search --}}
        <div class="relative">
          <input type="search" name="blog_q" x-model="blogQuery" @input.debounce.300ms="fetch()" placeholder="Search blogs..." class="w-56 md:w-64 rounded-lg border border-gray-300 px-3 py-2 pr-9 focus:outline-none focus:ring-2 focus:ring-emerald-500" />
          <button class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-500" type="button" aria-label="Search blogs"><i class="fa-solid fa-magnifying-glass"></i></button>
        </div>

        {{-- Category --}}
        <select name="blog_category" x-model="blogCat"    @change="fetch()" class="rounded-lg border border-gray-300 px-3 py-2">
          <option value="">All categories</option>
          @foreach($categories as $c)
            <option value="{{ $c->slug }}" @selected($blogCat===$c->slug)>{{ $c->name }}</option>
          @endforeach
          <option value="__none" @selected($blogCat==='__none')>— None —</option>
        </select>

        {{-- Status (includes Scheduled) --}}
        <select name="blog_status"   x-model="blogStatus" @change="fetch()" class="rounded-lg border border-gray-300 px-3 py-2">
          <option value="">Any status</option>
          <option value="published" @selected($blogStatus==='published')>Published</option>
          <option value="scheduled" @selected($blogStatus==='scheduled')>Scheduled</option>
          <option value="expired"   @selected($blogStatus==='expired')>Expired</option>
          <option value="draft"     @selected($blogStatus==='draft')>Draft</option>
        </select>

        {{-- Created By --}}
        <select name="blog_creator"  x-model="blogCreator" @change="fetch()" class="rounded-lg border border-gray-300 px-3 py-2">
          <option value="">Any creator</option>
          @foreach($blogCreators as $name)
            <option value="{{ $name }}" @selected($blogCreator===$name)>{{ $name }}</option>
          @endforeach
        </select>


        {{-- Sort --}}
        @php $blogSortVal = $blogSort; @endphp
        <select name="blog_sort"     x-model="blogSort"   @change="fetch()" class="rounded-lg border border-gray-300 px-3 py-2">
          <option value="latest" @selected($blogSortVal==='latest')>Newest first</option>
          <option value="oldest" @selected($blogSortVal==='oldest')>Oldest first</option>
        </select>

        <button type="button" @click="fetch()" class="inline-flex items-center gap-1 rounded-lg bg-emerald-600 text-white px-3 py-2 font-medium hover:bg-emerald-700">Apply</button>
        <button type="button"
        @click="blogQuery=''; blogCat=''; blogStatus=''; blogCreator=''; blogSort='latest'; fetch()"
        class="inline-flex items-center gap-1 rounded-lg border px-3 py-2 font-medium hover:bg-gray-50">Reset</button>

        <a href="{{ route('admin.dashboard') }}?show=add-blog#add-blog"
           @click.prevent="goAddBlog"
           class="ml-2 inline-flex items-center gap-2 rounded-lg bg-emerald-600 text-white px-3 py-2 hover:bg-emerald-700">
          <i class="fa-solid fa-plus"></i> Add a Blog
        </a>
      </form>
    </div>

    {{-- Active (unchanged) --}}
    <details class="rounded-xl border bg-white overflow-hidden group" open>
      <summary class="flex items-center justify-between gap-3 cursor-pointer select-none px-4 py-3 bg-gray-50 border-b">
        <div class="flex items-center gap-2">
          <span class="font-semibold">Active (Published, Scheduled & Drafts)</span>
          <span class="text-xs rounded-full px-2 py-0.5 border bg-white">{{ $activeCount }}</span>
        </div>
        <i class="fa-solid fa-chevron-down transition-transform duration-200 group-open:rotate-180"></i>
      </summary>

      <div class="relative">
        <div x-show="loading" x-transition.opacity class="absolute inset-0 bg-white/60 grid place-items-center z-10">Loading…</div>
        <div x-ref="blogsTarget">
          @include('admin.dashboard.partials.blogs_table', ['articles' => $articles])
        </div>
      </div>
    </details>

    {{-- Trash (uses the new partial) --}}
    @php
      // Safe fallbacks so the partial always gets what it needs
      $__trashPageCount = (is_object($trashedArticles) && method_exists($trashedArticles, 'count'))
          ? $trashedArticles->count()
          : (is_array($trashedArticles) ? count($trashedArticles) : 0);

      $__trashTotal = isset($trashTotal)
          ? $trashTotal
          : (method_exists($trashedArticles, 'total') ? $trashedArticles->total() : $__trashPageCount);

      $__trashBadge = isset($trashedCount) ? $trashedCount : $__trashPageCount;
    @endphp

    <details class="rounded-xl border bg-white overflow-hidden group">
      <summary class="flex items-center justify-between gap-3 cursor-pointer select-none px-4 py-3 bg-gray-50 border-b">
        <div class="flex items-center gap-2">
          <span class="font-semibold">Trash</span>
          <!-- Badge shows current-page count (like your previous UI); switch to $__trashTotal if you want total across pages -->
          <span class="text-xs rounded-full px-2 py-0.5 border bg-white">{{ $__trashBadge }}</span>
        </div>
        <i class="fa-solid fa-chevron-down transition-transform duration-200 group-open:rotate-180"></i>
      </summary>

      <div class="relative">
        <!-- Reuse the same loading flag. If you prefer a separate one, change to x-show="loadingTrash". -->
        <div x-show="loading" x-transition.opacity class="absolute inset-0 bg-white/60 grid place-items-center z-10">Loading…</div>

        <div x-ref="blogsTrashTarget">
          @include('admin.dashboard.partials.blogs_trashtable', [
            'trashedArticles' => $trashedArticles,
            'trashTotal'      => $__trashTotal,
            'trashedCount'    => $__trashBadge,
          ])
        </div>
      </div>
    </details>

  </div>

  {{-- ====================== BLOGS: ADD ====================== --}}
  @php
    $createInit = [
      'title'      => old('title',''),
      'slug'       => old('slug',''),
      'slugEdited' => old('slug') ? true : false,
      'thumbUrl'   => null,
      'baseUrl'    => rtrim(url('/'), '/'),
    ];
    $catsForMgr   = collect($categories)->map(fn($c)=> (object)['id'=>$c->id,'name'=>$c->name,'slug'=>$c->slug])->values();
    $destroyUrlTpl= route('admin.categories.destroy', ['category' => '__ID__']);
    $storeUrl     = route('admin.categories.store');

    $oldSched = old('scheduled_publish_date');
    $oldExp   = old('expires_at');
  @endphp
  <div x-show="openAdd" x-transition id="add-blog" class="space-y-4">
    <div class="flex items-center justify-between">
  <div class="flex items-center gap-3">
    <h2 class="text-xl font-bold">Add a Blog</h2>
    <span class="inline-flex items-center gap-1.5 text-xs font-semibold rounded-full px-2.5 py-1 border bg-gray-50 text-gray-700 border-gray-200">
      <i class="fa-regular fa-file-lines"></i> Creating a Blog
    </span>
  </div>
  <div class="flex gap-2">
    <a href="{{ route('admin.dashboard') }}?show=blogs#blogs"
       @click.prevent="goBlogs"
       class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-300 hover:bg-gray-50">
      <i class="fa-solid fa-arrow-left"></i> Back to List
    </a>
  </div>
</div>
    <form
    x-data='Object.assign({}, formGuard(), blogForm(@json($createInit)))'
    @submit.prevent="validateAndSubmit($event)"
    method="POST" action="{{ route('admin.blogs.store') }}"
    enctype="multipart/form-data" class="grid lg:grid-cols-3 gap-6" novalidate>
      @csrf
      <input type="hidden" name="timezone" value="Asia/Manila">


      <div class="lg:col-span-2 space-y-6">
        <div class="rounded-xl border bg-white p-5">
          <label class="block text-sm font-semibold mb-1">Article Title</label>
          <input type="text" name="title" data-required="true"
                 x-model="title" @input="onTitleInput($event)"
                 class="w-full rounded-lg border-gray-300" placeholder="Enter title" required>

          <div class="mt-4">
            <label class="block text-sm font-semibold mb-1">Slug</label>
              <input type="text" name="slug"
                    x-model="slug" @input="onSlugInput($event)"
                    class="w-full rounded-lg border-gray-300" placeholder="auto-generated if left blank">
              <p class="text-xs text-gray-500 mt-1">This becomes the URL segment.</p>


              <!-- Planned URL (reflects current slug + confirmed scheduled picker, else today) -->
              <div class="mt-1 text-xs">
                <span class="text-slate-500">Planned URL:</span>
                <a class="font-medium text-emerald-700 hover:underline break-all"
                  :href="urlPreview()" target="_blank" rel="noopener"
                  x-text="urlPreview()"></a>
              </div>

          </div>
        </div>

        <div class="rounded-xl border bg-white p-5">
          <label class="block text-sm font-semibold mb-2">Excerpt</label>
          <textarea name="excerpt" rows="3" class="w-full rounded-lg border-gray-300"
                    placeholder="Short summary (optional)">{{ old('excerpt') }}</textarea>
        </div>

        <div class="rounded-xl border bg-white p-5">
          <div class="flex items-center justify-between">
            <label class="block text-sm font-semibold">Body</label>
            <span class="text-xs text-gray-500">HTML allowed</span>
          </div>
          <textarea id="body-create" name="body" rows="14" data-required-if="publish"
                    class="w-full rounded-lg border-gray-300"
                    placeholder="Write your article here...">{{ old('body') }}</textarea>
        </div>

        {{-- ========== History (Add) ========== --}}
        <details class="rounded-xl border bg-white overflow-hidden group">
          <summary class="flex items-center justify-between gap-3 cursor-pointer select-none px-4 py-3 bg-gray-50 border-b">
            <div class="flex items-center gap-2">
              <span class="font-semibold">History</span>
              <span class="text-xs rounded-full px-2 py-0.5 border bg-white">0</span>
            </div>
            <i class="fa-solid fa-chevron-down transition-transform duration-200 group-open:rotate-180"></i>
          </summary>
          <div class="p-4 text-sm text-slate-600">No history yet — this is a new blog.</div>
        </details>
      </div>

      <aside class="space-y-6">
        <div class="rounded-xl border bg-white p-5">
          <!-- NEW: label + View Gallery button -->
          <div class="flex items-center justify-between mb-2">
            <label class="block text-sm font-semibold">Thumbnail</label>
            <button type="button"
                    class="inline-flex items-center gap-1.5 rounded-lg border px-2.5 py-1.5 text-sm hover:bg-gray-50"
                     @click="$store.gallery && $store.gallery.open({ onlyImages: true })">
              <i class="fa-regular fa-images"></i> View Gallery
            </button>
          </div>

                <div class="aspect-[16/9] bg-gray-100 rounded-lg overflow-hidden mb-3">
                  <img x-show="thumbUrl" :src="thumbUrl" class="w-full h-full object-cover" alt="Preview">
                </div>

                <div class="flex items-center gap-2">
                  <input type="file" name="featured_image" accept="image/*"
                        @change="previewThumb($event)"
                        class="rounded-lg border px-3 py-2">
                  <input type="hidden" name="featured_image_url" x-ref="featured_from_gallery">
                  <button type="button"
                          class="rounded-lg border px-2 py-1 text-sm hover:bg-gray-50"
                          @click="thumbUrl=null; if ($refs.featured_from_gallery) $refs.featured_from_gallery.value=''; const f=$root.querySelector('input[type=file][name=featured_image]'); if (f) f.value='';">
                    Clear
                  </button>
                </div>

                <p class="text-xs text-slate-500 mt-1">
                  Tip: Choose a file <em>or</em> open the gallery and click “Use”. If you pick a file, the gallery URL is cleared (and vice-versa).
                </p>
                <p class="text-xs text-gray-500 mt-2">JPG/PNG/WebP/GIF up to 4MB.</p>

        </div>

        {{-- Category Manager --}}
        <div
          x-data='catMgr({
            cats: @json($catsForMgr),
            selectedId: @json($selectedAddCat),
            destroyUrlTpl: @json($destroyUrlTpl),
            storeUrl: @json($storeUrl),
            csrf: @json(csrf_token()),
          })'
          x-cloak
          class="rounded-xl border bg-white p-5 space-y-5"
        >
          <label class="block text-sm font-semibold mb-1">Category</label>

          <input type="hidden" name="article_category_id" :value="selectedId">

          <!-- Filter  -->
          <div x-data="{ q: '' }" class="space-y-2">
            <input type="search"
                  x-model="q"
                  placeholder="Filter categories…"
                  class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500"/>

            <!-- Jayson requested -- WP-style selectable box: ~5 rows visible then scroll -->
            <div class="rounded-lg border bg-white divide-y max-h-48 overflow-y-auto" role="radiogroup" aria-label="Blog category">
              <!-- None option -->
              <label class="flex items-center gap-2 px-3 py-2 cursor-pointer hover:bg-slate-50"
                    :class="selectedId==='' ? 'bg-emerald-50' : ''">
                <input type="radio" class="rounded" x-model="selectedId" value="">
                <span>— None —</span>
              </label>

              <!-- Categories -->
              <template x-for="c in cats" :key="c.id">
                <label class="flex items-center gap-2 px-3 py-2 cursor-pointer hover:bg-slate-50"
                      x-show="!q || (c.name || '').toLowerCase().includes(q.toLowerCase())"
                      :class="String(selectedId)===String(c.id) ? 'bg-emerald-50' : ''">
                  <input type="radio" class="rounded" x-model="selectedId" :value="String(c.id)">
                  <span x-text="c.name"></span>
                </label>
              </template>
            </div>

            <p class="text-xs text-slate-500">Shows 5 at a time — scroll for more.</p>
          </div>


          <div class="border-t pt-4 space-y-3">
            <div class="flex items-center justify-between">
              <label class="block text-sm font-semibold">Manage Categories</label>
              <span class="text-xs text-gray-500">Add or remove categories</span>
            </div>

            <div class="flex items-center gap-2">
              <input type="text"
                    x-model.trim="newName"
                    @keydown.enter.prevent="createCategory()"
                    class="w-full rounded-lg focus:ring-2"
                    :class="((newName || '').trim() && !canCreate)
                              ? 'border-amber-500 focus:ring-amber-500'
                              : 'border-gray-300 focus:ring-emerald-500'"
                    placeholder="e.g. News, Updates"
                    aria-invalid="((newName || '').trim() && !canCreate) ? 'true' : 'false'">
              <button type="button"
                      @click="createCategory()"
                      :disabled="!canCreate || loading"
                      class="px-3 py-2 rounded-lg text-white"
                      :class="(!canCreate || loading) ? 'bg-gray-400 cursor-not-allowed' : 'bg-emerald-600 hover:bg-emerald-700'">
                Add
              </button>
            </div>

            <!-- inline hint -->
            <p class="text-sm mt-1 text-amber-600"
              x-show="(newName || '').trim() && !canCreate"
              x-cloak>
              Category already exists.
            </p>

            <template x-if="flash">
              <p class="text-xs"
                 :class="flashType==='error' ? 'text-red-600' : 'text-emerald-700'"
                 x-text="flash"></p>
            </template>

            <div class="mt-2 grid gap-2 max-h-60 overflow-y-auto pr-1">
              <template x-for="c in cats" :key="c.id">
                <div class="flex items-center justify-between rounded-lg border px-3 py-2">
                  <div class="text-sm font-medium" x-text="c.name"></div>
                  <button type="button"
                          @click="deleteCategory(c.id, c.name)"
                          :disabled="loading"
                          class="inline-flex items-center gap-1.5 text-sm rounded border px-2 py-1 hover:bg-red-50 text-red-600">
                    <i class="fa-regular fa-trash-can"></i> Remove
                  </button>
                </div>
              </template>
              <p class="text-xs text-gray-500" x-show="!cats.length">No categories yet.</p>
            </div>

          </div>
        </div>

        


                <!-- Tags manager (Add) -->
          <div class="rounded-xl border bg-white p-5"
              x-data='tagMgr({ initial: @json(old("tags","")) })'
              x-init="init()">
            <label class="block text-sm font-semibold mb-2">Hashtags</label>

            <!-- Hidden CSV that the backend reads (article.tags) -->
            <input type="hidden" name="tags" x-ref="hiddenTags">

            <div class="flex items-center gap-2">
              <input type="text"
                    x-model="newTag"
                    @keydown="onKeydown($event)"
                    @blur="onBlur()"
                    @paste="onPaste($event)"
                    class="w-full rounded-lg focus:ring-2"
                    :class="((newTag || '').trim() && !canAdd) ? 'border-amber-500 focus:ring-amber-500' : 'border-gray-300 focus:ring-emerald-500'"
                    placeholder="Type a tag then Enter or comma">
              <button type="button"
                      @click="addTag()"
                      :disabled="!canAdd"
                      class="px-3 py-2 rounded-lg text-white"
                      :class="!canAdd ? 'bg-gray-400 cursor-not-allowed' : 'bg-emerald-600 hover:bg-emerald-700'">
                Add
              </button>
            </div>

            <p class="text-xs mt-1 text-amber-600" x-show="(newTag || '').trim() && !canAdd" x-cloak>
              Tag already added.
            </p>

            <!-- Scrollable list -->
            <div class="mt-3 rounded-lg border bg-white/50">
              <div class="max-h-48 overflow-y-auto p-2 space-y-2">
                <template x-for="(t, i) in tags" :key="t + '_' + i">
                  <div class="flex items-center justify-between rounded-lg border px-3 py-2">
                    <div class="text-sm font-medium" x-text="t"></div>
                    <button type="button"
                            @click="removeTag(i)"
                            class="inline-flex items-center gap-1.5 text-sm rounded border px-2 py-1 hover:bg-red-50 text-red-600">
                      <i class="fa-regular fa-trash-can"></i> Remove
                    </button>
                  </div>
                </template>
                <p class="text-xs text-gray-500" x-show="!tags.length">No tags yet.</p>
              </div>
            </div>

            <p class="text-xs text-slate-500 mt-2">
              Tip: paste a list or type tags separated by commas — we’ll split them automatically.
            </p>
          </div>



        {{-- Publishing (PH time) with confirmations --}}
        <div class="rounded-xl border bg-white p-5 space-y-4"
             x-data="pubBox({ defaultMode: 'now', scheduledAtInit: '{{ $oldSched }}', expiresAtInit: '{{ $oldExp }}' })"
             x-init="init()">

          {{-- always tell backend intent + pass hidden confirmed fields --}}
          <input type="hidden" name="status_intent" x-ref="status_intent" value="now">
          <input type="hidden" name="scheduled_publish_date" x-ref="hidden_sched">
          <input type="hidden" name="expires_at" x-ref="hidden_exp">
          <input type="hidden" name="align_created_to_published_on_create" x-ref="align_on_create" value="1">



          <label class="block text-sm font-semibold">
            Publishing <span class="text-xs text-slate-500">(Asia/Manila, UTC+8)</span>
          </label>

          <div class="space-y-2">
            <label class="inline-flex items-center gap-2">
              <input type="radio" name="publish_mode" value="now" class="rounded" x-model="mode" @change="onModeChange()" checked>
              <span class="text-sm">Publish now (default)</span>
            </label>
            <label class="inline-flex items-center gap-2">
              <input type="radio" name="publish_mode" value="schedule" class="rounded" x-model="mode" @change="onModeChange()">
              <span class="text-sm">Schedule</span>
            </label>
          </div>
    
          <div x-ref="schedWrap"
               x-show="mode==='schedule'"
               x-transition
               x-cloak
               class="grid gap-3">
            <div>
              <label class="block text-xs font-semibold mb-1">Publish at</label>
              <div class="flex items-center gap-2">
                <input type="datetime-local" x-ref="published_at"
                       value="{{ $oldSched }}"
                       :disabled="mode!=='schedule'"
                       class="w-full rounded-lg border-gray-300">
                <button type="button"
                        class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm text-white bg-emerald-600 hover:bg-emerald-700"
                        @click="askConfirmPublish()">
                  <i class="fa-solid fa-check"></i> OK
                </button>
                <button type="button"
                        class="text-xs text-slate-600 underline"
                        x-show="isPublishConfirmed"
                        @click="clearConfirmPublish()">
                  Change
                </button>
              </div>
              <p class="text-xs mt-1"
                 :class="isPublishConfirmed ? 'text-emerald-700' : 'text-slate-500'">
                <template x-if="isPublishConfirmed">
                  <span class="inline-flex items-center gap-1"><i class="fa-solid fa-circle-check"></i> Confirmed</span>
                </template>
                <template x-if="!isPublishConfirmed">
                  <span>Select a time and press <strong>OK</strong> to confirm.</span>
                </template>
              </p>
            </div>

            <div>
              <label class="block text-xs font-semibold mb-1">Unpublish at (expires)</label>
              <div class="flex items-center gap-2">
                <input type="datetime-local" x-ref="expires_at"
                value="{{ $oldExp }}"
                class="w-full rounded-lg border-gray-300">
                <button type="button"
                        class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm text-white bg-amber-600 hover:bg-amber-700"
                        @click="askConfirmExpire()">
                  <i class="fa-solid fa-check"></i> OK
                </button>
                <button type="button"
                        class="text-xs text-slate-600 underline"
                        x-show="isExpireConfirmed"
                        @click="clearConfirmExpire()">
                  Change
                </button>
              </div>
              <p class="text-xs mt-1"
                 :class="isExpireConfirmed ? 'text-emerald-700' : 'text-slate-500'">
                <template x-if="isExpireConfirmed">
                  <span class="inline-flex items-center gap-1"><i class="fa-solid fa-circle-check"></i> Confirmed</span>
                </template>
                <template x-if="!isExpireConfirmed">
                  <span>(Optional) Select a time and press <strong>OK</strong> to confirm expiry.</span>
                </template>
              </p>
            </div>

            <p class="text-xs text-slate-500">
              New posts: <strong>Publish now</strong> makes <code>created_at</code> = publish time.  
              <strong>Schedule</strong> keeps <code>created_at</code> as the creation time.
            </p>
          </div>
        </div>

        <div class="rounded-xl border bg-white p-5">
          <div class="flex gap-2">
            <button type="submit" name="action" value="draft" class="px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-50">Save Draft</button>
            <button type="submit" name="action" value="publish" class="px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700">Publish</button>
          </div>
        </div>
      </aside>
    </form>
  </div>

  {{-- ====================== BLOGS: EDIT ====================== --}}
  
        @isset($editArticle)
        @php
          // Keep the explicit UTC->PH conversion in edit forms
          $schedDT = $editArticle->scheduled_publish_date
        ? Carbon::parse($editArticle->scheduled_publish_date, 'UTC')->timezone('Asia/Manila')
        : null;
      $pubDT = $editArticle->published_at
        ? Carbon::parse($editArticle->published_at, 'UTC')->timezone('Asia/Manila')
        : null;
      $expDT = $editArticle->expires_at
        ? Carbon::parse($editArticle->expires_at, 'UTC')->timezone('Asia/Manila')
        : null;
      $effEdit = $effectiveStatus($editArticle);
      $schedOld = old('scheduled_publish_date', null);
      $expOld   = old('expires_at', null);

      // pick the first non-empty of scheduled_publish_date or published_at
      $schedBase = $schedDT ?: $pubDT; 
      $expBase   = $expDT;

      // seed the input from schedBase, unless there’s an old() override
      $sched = ($schedOld === null || $schedOld === '')
        ? ($schedBase ? $schedBase->format('Y-m-d\TH:i') : null)
        : $schedOld;

      $exp = ($expOld === null || $expOld === '')
        ? ($expBase ? $expBase->format('Y-m-d\TH:i') : null)
        : $expOld;


    $modeDefault = ($schedDT && $schedDT->greaterThan(\Carbon\Carbon::now('Asia/Manila')))
                    || ($pubDT && $pubDT->greaterThan(\Carbon\Carbon::now('Asia/Manila')))
                    ? 'schedule' : 'now';

    $thumbEditUrl = null;
    if (!empty($editArticle->featured_image)) {
      $raw = $editArticle->featured_image;
$thumbEditUrl = Str::startsWith($raw, ['http://','https://'])
    ? $raw
    : (Str::startsWith($raw, '/storage/')
        ? $raw
        : \Illuminate\Support\Facades\Storage::url(ltrim($raw, '/')));
    }

    $editInit = [
      'title'      => old('title', $editArticle->title),
      'slug'       => old('slug',  $editArticle->slug),
      'slugEdited' => true,
      'thumbUrl'   => $thumbEditUrl,
      'baseUrl'    => rtrim(url('/'), '/'),
    ];
  @endphp

  <div x-show="openEdit" x-transition id="edit-blog" class="space-y-4">
    <div class="flex items-center justify-between">
  <div class="flex items-center gap-3">
    <h2 class="text-xl font-bold">Edit: {{ $editArticle->title }}</h2>
    <span class="inline-flex items-center gap-1.5 text-xs font-semibold rounded-full px-2.5 py-1 border {{ $effEdit['class'] }}">
      @if($effEdit['slug']==='published')
        <i class="fa-regular fa-circle-check"></i> {{ $effEdit['label'] }}
      @elseif($effEdit['slug']==='scheduled')
        <i class="fa-regular fa-clock"></i> {{ $effEdit['label'] }}
      @elseif($effEdit['slug']==='expired')
        <i class="fa-regular fa-calendar-xmark"></i> {{ $effEdit['label'] }}
      @else
        <i class="fa-regular fa-file-lines"></i> {{ $effEdit['label'] }}
      @endif
    </span>
  </div>
  <div class="flex gap-2">
    <a href="{{ route('admin.dashboard') }}?show=blogs#blogs"
       @click.prevent="goBlogs"
       class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-300 hover:bg-gray-50">
      <i class="fa-solid fa-arrow-left"></i> Back to List
    </a>
  </div>
</div>

    <form
    x-data='Object.assign({}, formGuard(), blogForm(@json($editInit)))'
    @submit.prevent="validateAndSubmit($event)"
    method="POST"
    action="{{ route('admin.blogs.update', $editArticle) }}"
    enctype="multipart/form-data"
    class="grid lg:grid-cols-3 gap-6" novalidate>
      @csrf
      @method('PUT')
      <input type="hidden" name="timezone" value="Asia/Manila">

      <div class="lg:col-span-2 space-y-6">
        <div class="rounded-xl border bg-white p-5">
          <label class="block text-sm font-semibold mb-1">Article Title</label>
          <input type="text" name="title" data-required="true"
                 x-model="title" @input="onTitleInput($event)"
                 class="w-full rounded-lg border-gray-300">

          <div class="mt-4">
            <label class="block text-sm font-semibold mb-1">Slug</label>
            <input type="text" name="slug"
                  x-model="slug" @input="onSlugInput($event)"
                  class="w-full rounded-lg border-gray-300" placeholder="auto-generated if left blank">
            <p class="text-xs text-gray-500 mt-1">This becomes the URL segment.</p>

            @php
              // Determine current “live” date (PH) for display when published/scheduled
              $liveLabel = null; $liveUrl = null;
              $liveDt = null;
              if (($effEdit['slug'] ?? '') === 'published') {
                  $liveLabel = 'Live URL';
                  $liveDt = $pubDT ?: $schedDT;
              } elseif (($effEdit['slug'] ?? '') === 'scheduled') {
                  $liveLabel = 'Scheduled URL';
                  $liveDt = $schedDT ?: $pubDT;
              }
              if ($liveDt) {
                  $ymd = $liveDt->format('Y/m/d');
                  $liveUrl = rtrim(url('/'), '/') . '/' . $ymd . '/' . ($editArticle->slug ?? '');
              }
            @endphp

            @if($liveUrl)
              <div class="mt-1 text-xs">
                <span class="text-slate-500">{{ $liveLabel }}:</span>
                <a class="font-medium text-emerald-700 hover:underline break-all"
                  href="{{ $liveUrl }}" target="_blank" rel="noopener">{{ $liveUrl }}</a>
              </div>
            @endif

            <!-- Planned URL (reflects current slug + scheduled picker, else today) -->
            <div class="mt-1 text-xs">
              <span class="text-slate-500">Planned URL:</span>
              <a class="font-medium text-emerald-700 hover:underline break-all"
                :href="urlPreview()" target="_blank" rel="noopener"
                x-text="urlPreview()"></a>
            </div>
          </div>

        </div>

        <div class="rounded-xl border bg-white p-5">
          <label class="block text-sm font-semibold mb-2">Excerpt</label>
          <textarea name="excerpt" rows="3"  class="w-full rounded-lg border-gray-300">{{ old('excerpt', $editArticle->excerpt) }}</textarea>
        </div>

        <div class="rounded-xl border bg-white p-5">
          <div class="flex items-center justify-between">
            <label class="block text-sm font-semibold">Body</label>
            <span class="text-xs text-gray-500">HTML allowed</span>
          </div>
          <textarea id="body-edit" name="body" rows="14" class="w-full rounded-lg border-gray-300">{{ old('body', $editArticle->body) }}</textarea>
        </div>

       {{-- ========== History (Edit) ========== --}}
      <details class="rounded-xl border bg-white overflow-hidden group" {{ $blogHistoryCount ? 'open' : '' }}>
        <summary class="flex items-center justify-between gap-3 cursor-pointer select-none px-4 py-3 bg-gray-50 border-b">
          <div class="flex items-center gap-2">
            <span class="font-semibold">History</span>
            <span class="text-xs rounded-full px-2 py-0.5 border bg-white">{{ $blogHistoryCount }}</span>
          </div>
          <i class="fa-solid fa-chevron-down transition-transform duration-200 group-open:rotate-180"></i>
        </summary>

        @php
          // Show the most recent update at the top.
          // This assumes your original $blogHistoryRows is oldest→newest.
          // Using reverse() avoids parsing date strings and is very reliable.
          $historyRows = collect($blogHistoryRows ?? [])->reverse()->values();
        @endphp

        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50 text-xs uppercase text-gray-500">
              <tr>
                <th class="px-4 py-3 text-left font-semibold">Updated by</th>
                <th class="px-4 py-3 text-left font-semibold">Updated what</th>
                <th class="px-4 py-3 text-left font-semibold">Date updated (PH)</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-sm">
              @forelse($historyRows as $row)
                <tr class="hover:bg-gray-50">
                  <td class="px-4 py-3 text-slate-800">{{ $row['by'] }}</td>
                  <td class="px-4 py-3 text-slate-700">{{ $row['what'] }}</td>
                  <td class="px-4 py-3 text-slate-700 whitespace-nowrap">{{ $row['when'] }}</td>
                </tr>
              @empty
                <tr>
                  <td colspan="3" class="px-4 py-6 text-center text-gray-600">No history yet.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </details>

      </div>

      <aside class="space-y-6">
        <div class="rounded-xl border bg-white p-5">
          <!-- NEW: label + View Gallery button -->
          <div class="flex items-center justify-between mb-2">
            <label class="block text-sm font-semibold">Thumbnail</label>
            <button type="button"
                    class="inline-flex items-center gap-1.5 rounded-lg border px-2.5 py-1.5 text-sm hover:bg-gray-50"
                     @click="$store.gallery && $store.gallery.open({ onlyImages: true })">
              <i class="fa-regular fa-images"></i> View Gallery
            </button>
          </div>

          <div class="aspect-[16/9] bg-gray-100 rounded-lg overflow-hidden mb-3 relative">
              <img x-show="thumbUrl" :src="thumbUrl" class="w-full h-full object-cover" alt="Thumbnail">
            </div>

            <div class="flex items-center gap-2">
              <input type="file" name="featured_image" accept="image/*"
                    @change="previewThumb($event)"
                    class="rounded-lg border px-3 py-2">
              <input type="hidden" name="featured_image_url" x-ref="featured_from_gallery">
              <button type="button"
                      class="rounded-lg border px-2 py-1 text-sm hover:bg-gray-50"
                      @click="thumbUrl=null; if ($refs.featured_from_gallery) $refs.featured_from_gallery.value=''; const f=$root.querySelector('input[type=file][name=featured_image]'); if (f) f.value='';">
                Clear
              </button>
            </div>

            <p class="text-xs text-slate-500 mt-1">
              Tip: Choose a file <em>or</em> open the gallery and click “Use”. If you pick a file, the gallery URL is cleared (and vice-versa).
            </p>
            <p class="text-xs text-gray-500 mt-2">JPG/PNG/WebP/GIF up to 4MB.</p>

        </div>

        {{-- Category Manager --}}
        @php
          $catsForMgr   = collect($categories)->map(fn($c)=> (object)['id'=>$c->id,'name'=>$c->name,'slug'=>$c->slug])->values();
          $destroyUrlTpl= route('admin.categories.destroy', ['category' => '__ID__']);
          $storeUrl     = route('admin.categories.store');
        @endphp
        <div
          x-data='catMgr({
            cats: @json($catsForMgr),
            selectedId: @json($selectedEditCat),
            destroyUrlTpl: @json($destroyUrlTpl),
            storeUrl: @json($storeUrl),
            csrf: @json(csrf_token()),
          })'
          x-cloak
          class="rounded-xl border bg-white p-5 space-y-5"
        >
          <label class="block text-sm font-semibold mb-1">Category</label>

            <!-- keep the original field name for form submit -->
            <input type="hidden" name="article_category_id" :value="selectedId">

            <!-- optional tiny filter; remove if you don't want it -->
            <div x-data="{ q: '' }" class="space-y-2">
              <input type="search"
                    x-model="q"
                    placeholder="Filter categories…"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500"/>

              <!-- Jayson requested - WP-style selectable box: ~5 rows visible then scroll -->
              <div class="rounded-lg border bg-white divide-y max-h-48 overflow-y-auto" role="radiogroup" aria-label="Blog category">
                <!-- None option -->
                <label class="flex items-center gap-2 px-3 py-2 cursor-pointer hover:bg-slate-50"
                      :class="selectedId==='' ? 'bg-emerald-50' : ''">
                  <input type="radio" class="rounded" x-model="selectedId" value="">
                  <span>— None —</span>
                </label>

                <!-- Categories -->
                <template x-for="c in cats" :key="c.id">
                  <label class="flex items-center gap-2 px-3 py-2 cursor-pointer hover:bg-slate-50"
                        x-show="!q || (c.name || '').toLowerCase().includes(q.toLowerCase())"
                        :class="String(selectedId)===String(c.id) ? 'bg-emerald-50' : ''">
                    <input type="radio" class="rounded" x-model="selectedId" :value="String(c.id)">
                    <span x-text="c.name"></span>
                  </label>
                </template>
              </div>

              <p class="text-xs text-slate-500">Shows 5 at a time — scroll for more.</p>
            </div>


          <div class="border-t pt-4 space-y-3">
            <div class="flex items-center justify-between">
              <label class="block text-sm font-semibold">Manage Categories</label>
              <span class="text-xs text-gray-500">Add or remove categories</span>
            </div>

            <div class="flex items-center gap-2">
              <input type="text"
                    x-model.trim="newName"
                    @keydown.enter.prevent="createCategory()"
                    class="w-full rounded-lg focus:ring-2"
                    :class="((newName || '').trim() && !canCreate)
                              ? 'border-amber-500 focus:ring-amber-500'
                              : 'border-gray-300 focus:ring-emerald-500'"
                    placeholder="e.g. News, Updates"
                    aria-invalid="((newName || '').trim() && !canCreate) ? 'true' : 'false'">
              <button type="button"
                      @click="createCategory()"
                      :disabled="!canCreate || loading"
                      class="px-3 py-2 rounded-lg text-white"
                      :class="(!canCreate || loading) ? 'bg-gray-400 cursor-not-allowed' : 'bg-emerald-600 hover:bg-emerald-700'">
                Add
              </button>
            </div>

            <!-- inline hint -->
            <p class="text-sm mt-1 text-amber-600"
              x-show="(newName || '').trim() && !canCreate"
              x-cloak>
              Category already exists.
            </p>

        <!-- keep your existing flash line, but let it support “warning” color too -->
        <template x-if="flash">
          <p class="text-xs"
            :class="flashType==='error' ? 'text-red-600' : (flashType==='warning' ? 'text-amber-600' : 'text-emerald-700')"
            x-text="flash"></p>
        </template>


            <div class="mt-2 grid gap-2 max-h-60 overflow-y-auto pr-1">
              <template x-for="c in cats" :key="c.id">
                <div class="flex items-center justify-between rounded-lg border px-3 py-2">
                  <div class="text-sm font-medium" x-text="c.name"></div>
                  <button type="button"
                          @click="deleteCategory(c.id, c.name)"
                          :disabled="loading"
                          class="inline-flex items-center gap-1.5 text-sm rounded border px-2 py-1 hover:bg-red-50 text-red-600">
                    <i class="fa-regular fa-trash-can"></i> Remove
                  </button>
                </div>
              </template>
              <p class="text-xs text-gray-500" x-show="!cats.length">No categories yet.</p>
            </div>

          </div>
        </div>


        


        <!-- Tags manager (Edit) -->
            <div class="rounded-xl border bg-white p-5"
                x-data='tagMgr({ initial: @json(old("tags", $editArticle->tags ?? "")) })'
                x-init="init()">
              <label class="block text-sm font-semibold mb-2">Hashtags</label>
              <input type="hidden" name="tags" x-ref="hiddenTags">

              <div class="flex items-center gap-2">
                <input type="text"
                      x-model="newTag"
                      @keydown="onKeydown($event)"
                      @blur="onBlur()"
                      @paste="onPaste($event)"
                      class="w-full rounded-lg focus:ring-2"
                      :class="((newTag || '').trim() && !canAdd) ? 'border-amber-500 focus:ring-amber-500' : 'border-gray-300 focus:ring-emerald-500'"
                      placeholder="Type a tag then Enter or comma">
                <button type="button"
                        @click="addTag()"
                        :disabled="!canAdd"
                        class="px-3 py-2 rounded-lg text-white"
                        :class="!canAdd ? 'bg-gray-400 cursor-not-allowed' : 'bg-emerald-600 hover:bg-emerald-700'">
                  Add
                </button>
              </div>

              <p class="text-xs mt-1 text-amber-600" x-show="(newTag || '').trim() && !canAdd" x-cloak>
                Tag already added.
              </p>

              <div class="mt-3 rounded-lg border bg-white/50">
                <div class="max-h-48 overflow-y-auto p-2 space-y-2">
                  <template x-for="(t, i) in tags" :key="t + '_' + i">
                    <div class="flex items-center justify-between rounded-lg border px-3 py-2">
                      <div class="text-sm font-medium" x-text="t"></div>
                      <button type="button"
                              @click="removeTag(i)"
                              class="inline-flex items-center gap-1.5 text-sm rounded border px-2 py-1 hover:bg-red-50 text-red-600">
                        <i class="fa-regular fa-trash-can"></i> Remove
                      </button>
                    </div>
                  </template>
                  <p class="text-xs text-gray-500" x-show="!tags.length">No tags yet.</p>
                </div>
              </div>
            </div>




        {{-- Scheduling (PH time) with confirmations --}}
        <div class="rounded-xl border bg-white p-5 space-y-4"
             x-data="pubBox({ defaultMode: '{{ $modeDefault }}', scheduledAtInit: '{{ $sched }}', expiresAtInit: '{{ $exp }}' })"
             x-init="init()">
          <input type="hidden" name="status_intent" x-ref="status_intent" value="{{ $modeDefault }}">
          <input type="hidden" name="scheduled_publish_date" x-ref="hidden_sched">
          <input type="hidden" name="expires_at" x-ref="hidden_exp">

          <label class="block text-sm font-semibold">
            Scheduling <span class="text-xs text-slate-500">(Asia/Manila, UTC+8)</span>
          </label>

          <div class="space-y-2">
            <label class="inline-flex items-center gap-2">
              <input type="radio" name="publish_mode" value="now" class="rounded" x-model="mode" @change="onModeChange()">
              <span class="text-sm">Publish now</span>
            </label>
            <label class="inline-flex items-center gap-2">
              <input type="radio" name="publish_mode" value="schedule" class="rounded" x-model="mode" @change="onModeChange()">
              <span class="text-sm">Schedule</span>
            </label>
          </div>

          <div x-ref="schedWrap" x-show="mode==='schedule'" x-transition x-cloak class="grid gap-3">
            <div>
              <label class="block text-xs font-semibold mb-1">Publish at</label>
              <div class="flex items-center gap-2">
                <input type="datetime-local" x-ref="published_at"
                       value="{{ $sched }}"
                       :disabled="mode!=='schedule'"
                       class="w-full rounded-lg border-gray-300">
                <button type="button"
                        class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm text-white bg-emerald-600 hover:bg-emerald-700"
                        @click="askConfirmPublish()">
                  <i class="fa-solid fa-check"></i> OK
                </button>
                <button type="button"
                        class="text-xs text-slate-600 underline"
                        x-show="isPublishConfirmed"
                        @click="clearConfirmPublish()">
                  Change
                </button>
              </div>
              <p class="text-xs text-slate-500 mt-1" x-show="{{ ($schedDT || $pubDT) ? 'true' : 'false' }}">
                 @php $cur = $schedDT ?: $pubDT; @endphp
                    @if($cur)
                      Current: {{ $cur->format('M d, Y • g:i A') }} PH /
                              {{ $cur->copy()->timezone('UTC')->format('M d, Y • g:i A') }} UTC
                    @endif
              </p>
              <p class="text-xs mt-1"
                 :class="isPublishConfirmed ? 'text-emerald-700' : 'text-slate-500'">
                <template x-if="isPublishConfirmed">
                  <span class="inline-flex items-center gap-1"><i class="fa-solid fa-circle-check"></i> Confirmed</span>
                </template>
                <template x-if="!isPublishConfirmed">
                  <span>Select a time and press <strong>OK</strong> to confirm.</span>
                </template>
              </p>
            </div>
            <div>
              <label class="block text-xs font-semibold mb-1">Unpublish at (expires)</label>
              <div class="flex items-center gap-2">
                <input type="datetime-local" x-ref="expires_at"
                       value="{{ $exp }}" :disabled="mode!=='schedule'"
                       class="w-full rounded-lg border-gray-300">
                <button type="button"
                        class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm text-white bg-amber-600 hover:bg-amber-700"
                        @click="askConfirmExpire()">
                  <i class="fa-solid fa-check"></i> OK
                </button>
                <button type="button"
                        class="text-xs text-slate-600 underline"
                        x-show="isExpireConfirmed"
                        @click="clearConfirmExpire()">
                  Change
                </button>
              </div>
              <p class="text-xs text-slate-500 mt-1" x-show="{{ $exp ? 'true' : 'false' }}">
                @if($expDT)
                  Current: {{ $expDT->format('M d, Y • g:i A') }} PH / {{ $expDT->copy()->timezone('UTC')->format('M d, Y • g:i A') }} UTC
                @endif
              </p>
              <p class="text-xs mt-1"
                 :class="isExpireConfirmed ? 'text-emerald-700' : 'text-slate-500'">
                <template x-if="isExpireConfirmed">
                  <span class="inline-flex items-center gap-1"><i class="fa-solid fa-circle-check"></i> Confirmed</span>
                </template>
                <template x-if="!isExpireConfirmed">
                  <span>(Optional) Select a time and press <strong>OK</strong> to confirm expiry.</span>
                </template>
              </p>
            </div>
            <p class="text-xs text-slate-500">
              Editing: future → <em>Scheduled</em>, past → <em>Published</em>, <code>created_at</code> never changes on edits.
            </p>
          </div>
        </div>

        <div class="rounded-xl border bg-white p-5">
          <div class="flex gap-2">
            <button type="submit" name="action" value="draft"   class="px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-50">Save Draft</button>
            <button type="submit" name="action" value="publish" class="px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700">Update & Publish</button>
          </div>
        </div>
      </aside>
    </form>
  </div>
  @endisset

  {{-- ====================== PROMOS: LIST ====================== --}}
  <div
    x-show="openPromosList" x-transition id="promos" class="space-y-4"
    x-data="promosList({
      promoQuery:  @js($promoSearch),
      promoStatus: @js($promoStatus),
      promoCreator:@js($promoCreator),
      promoSort:   @js($promoSort)
    })"
    x-init="init()"
  >

    <div class="flex items-center justify-between gap-3 flex-wrap">
      <h2 class="text-xl font-bold">Promos</h2>

      {{-- PROMO FILTER BAR --}}
      <form method="GET" action="{{ route('admin.dashboard') }}" class="flex flex-wrap gap-2 items-center" @submit.prevent>

        <input type="hidden" name="show" value="promos">

        {{-- Search --}}
        <div class="relative">
          <input type="search" name="promo_q" x-model="promoQuery" @input.debounce.300ms="fetch()" placeholder="Search Promos..." class="w-56 md:w-64 rounded-lg border border-gray-300 px-3 py-2 pr-9 focus:outline-none focus:ring-2 focus:ring-emerald-500" />
          <button class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-500" type="button" aria-label="Search promos"><i class="fa-solid fa-magnifying-glass"></i></button>

        </div>

        {{-- Status --}}
        <select name="promo_status" x-model="promoStatus"  @change="fetch()" class="rounded-lg border border-gray-300 px-3 py-2">
          <option value="">Any status</option>
          <option value="published" @selected($promoStatus==='published')>Published</option>
          <option value="scheduled" @selected($promoStatus==='scheduled')>Scheduled</option>
          <option value="expired"   @selected($promoStatus==='expired')>Expired</option>
          <option value="draft"     @selected($promoStatus==='draft')>Draft</option>
        </select>

        {{-- Created By --}}
        <select name="promo_creator" x-model="promoCreator"  @change="fetch()" class="rounded-lg border border-gray-300 px-3 py-2">
          <option value="">Any creator</option>
          @foreach($promoCreators as $name)
            <option value="{{ $name }}" @selected($promoCreator===$name)>{{ $name }}</option>
          @endforeach
        </select>


        {{-- Sort --}}
        @php $promoSortVal = $promoSort; @endphp
        <select name="promo_sort" x-model="promoSort"  @change="fetch()" class="rounded-lg border border-gray-300 px-3 py-2">
          <option value="latest" @selected($promoSortVal==='latest')>Newest first</option>
          <option value="oldest" @selected($promoSortVal==='oldest')>Oldest first</option>
        </select>

        <button type="button" @click="fetch()" class="inline-flex items-center gap-1 rounded-lg bg-emerald-600 text-white px-3 py-2 font-medium hover:bg-emerald-700">Apply</button>
       <button type="button"
        @click="promoQuery=''; promoStatus=''; promoCreator=''; promoSort='latest'; fetch()"
        class="inline-flex items-center gap-1 rounded-lg border px-3 py-2 font-medium hover:bg-gray-50">Reset</button>



       <a href="{{ route('admin.dashboard') }}?show=add-promo#add-promo"
          @click.prevent="window.__dash && window.__dash.goAddPromo()"
          class="ml-2 inline-flex items-center gap-2 rounded-lg bg-emerald-600 text-white px-3 py-2 hover:bg-emerald-700">
          <i class="fa-solid fa-plus"></i> Add a Promo
        </a>

      </form>
    </div>

      {{-- Promo Active --}}
      <details class="rounded-xl border bg-white overflow-hidden group" open>
        <summary class="flex items-center justify-between gap-3 cursor-pointer select-none px-4 py-3 bg-gray-50 border-b">
          <div class="flex items-center gap-2">
            <span class="font-semibold">Active (Published, Scheduled & Drafts)</span>
            <span class="text-xs rounded-full px-2 py-0.5 border bg-white">{{ $activePromosCount }}</span>

          </div>
          <i class="fa-solid fa-chevron-down transition-transform duration-200 group-open:rotate-180"></i>
        </summary>

        <div class="relative">
          <div x-show="loading" x-transition.opacity class="absolute inset-0 bg-white/60 grid place-items-center z-10">Loading…</div>
          <div x-ref="promosTarget">
            @include('admin.dashboard.partials.promos_table', ['promos' => $promos])
          </div>
        </div>
      </details>

        @php
          $__promoTrashPageCount = (is_object($trashedPromos) && method_exists($trashedPromos, 'count'))
              ? $trashedPromos->count()
              : (is_array($trashedPromos) ? count($trashedPromos) : 0);

          $__promoTrashTotal = isset($trashPromosTotal)
              ? $trashPromosTotal
              : (method_exists($trashedPromos, 'total') ? $trashedPromos->total() : $__promoTrashPageCount);

          $__promoTrashBadge = isset($trashedPromosCount) ? $trashedPromosCount : $__promoTrashPageCount;
        @endphp

        <details class="rounded-xl border bg-white overflow-hidden group">
          <summary class="flex items-center justify-between gap-3 cursor-pointer select-none px-4 py-3 bg-gray-50 border-b">
            <div class="flex items-center gap-2">
              <span class="font-semibold">Trash</span>
              <span class="text-xs rounded-full px-2 py-0.5 border bg-white">{{ $__promoTrashBadge }}</span>
            </div>
            <i class="fa-solid fa-chevron-down transition-transform duration-200 group-open:rotate-180"></i>
          </summary>

          <div class="relative">
            <div x-show="loading" x-transition.opacity class="absolute inset-0 bg-white/60 grid place-items-center z-10">Loading…</div>
            <div x-ref="promosTrashTarget">
              @include('admin.dashboard.partials.promos_trashtable', [
                'trashedPromos'      => $trashedPromos,
                'trashPromosTotal'   => $__promoTrashTotal,
                'trashedPromosCount' => $__promoTrashBadge,
              ])
            </div>
          </div>
        </details>


    </div>
  </div>

  {{-- ====================== PROMOS: ADD ====================== --}}
  @php
    $promoCreateInit = [
      'title'      => old('title',''),
      'slug'       => old('slug',''),
      'slugEdited' => old('slug') ? true : false,
      'thumbUrl'   => null,
      'baseUrl'    => rtrim(url('/'), '/'),
      'prefixPath' => 'promos', 
    ];
    $promoOldSched = old('scheduled_publish_date');
    $promoOldExp   = old('expires_at');
  @endphp
  <div id="add-promo" x-cloak x-show="openPromoAdd" x-transition class="space-y-4">
    <div class="flex items-center justify-between">
  <div class="flex items-center gap-3">
    <h2 class="text-xl font-bold">Add a Promo</h2>
    <span class="inline-flex items-center gap-1.5 text-xs font-semibold rounded-full px-2.5 py-1 border bg-gray-50 text-gray-700 border-gray-200">
      <i class="fa-regular fa-file-lines"></i> Creating a Promo
    </span>
  </div>
  <div class="flex gap-2">
    <a href="{{ route('admin.dashboard') }}?show=promos#promos"
      @click.prevent="window.__dash && window.__dash.goPromos()"
      class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-300 hover:bg-gray-50">
      <i class="fa-solid fa-arrow-left"></i> Back to List
    </a>

  </div>
</div>


    <form
    x-data='Object.assign({}, formGuard(), blogForm(@json($promoCreateInit)))'
    @submit.prevent="validateAndSubmit($event)"
    method="POST" action="{{ route('admin.promos.store') }}"
    enctype="multipart/form-data" class="grid lg:grid-cols-3 gap-6" novalidate>
      @csrf
      <input type="hidden" name="timezone" value="Asia/Manila">


      <div class="lg:col-span-2 space-y-6">
        <div class="rounded-xl border bg-white p-5">
          <label class="block text-sm font-semibold mb-1">Promo Title</label>
          <input type="text" name="title" data-required="true"
                 x-model="title" @input="onTitleInput($event)"
                 class="w-full rounded-lg border-gray-300" placeholder="Enter title" required>

          <div class="mt-4">
            <label class="block text-sm font-semibold mb-1">Slug</label>
            <input type="text" name="slug"
                  x-model="slug" @input="onSlugInput($event)"
                  class="w-full rounded-lg border-gray-300" placeholder="auto-generated if left blank">
            <p class="text-xs text-gray-500 mt-1">This becomes the URL segment.</p>

            <!-- Planned URL (promos add): /promos/{slug} -->
            @php $base = rtrim(url('/'), '/'); @endphp
            <div class="mt-1 text-xs">
              <span class="text-slate-500">Planned URL:</span>
              <a class="font-medium text-emerald-700 hover:underline break-all"
                :href="(()=>{const s=(slug||'').trim() || (title||'').toString().toLowerCase().trim().replace(/[^a-z0-9]+/g,'-').replace(/(^-|-$)/g,''); return '{{ $base }}/promos/' + s;})()"
                x-text="(()=>{const s=(slug||'').trim() || (title||'').toString().toLowerCase().trim().replace(/[^a-z0-9]+/g,'-').replace(/(^-|-$)/g,''); return '{{ $base }}/promos/' + s;})()"
                target="_blank" rel="noopener"></a>
            </div>

          </div>

        </div>

        <div class="rounded-xl border bg-white p-5">
          <label class="block text-sm font-semibold mb-2">Description</label>
          <textarea id="promo-body-create" name="body" rows="10" class="w-full rounded-lg border-gray-300"
                    placeholder="Describe the promo details...">{{ old('body') }}</textarea>
        </div>

        {{-- ========== History (Add) ========== --}}
        <details class="rounded-xl border bg-white overflow-hidden group">
          <summary class="flex items-center justify-between gap-3 cursor-pointer select-none px-4 py-3 bg-gray-50 border-b">
            <div class="flex items-center gap-2">
              <span class="font-semibold">History</span>
              <span class="text-xs rounded-full px-2 py-0.5 border bg-white">0</span>
            </div>
            <i class="fa-solid fa-chevron-down transition-transform duration-200 group-open:rotate-180"></i>
          </summary>
          <div class="p-4 text-sm text-slate-600">No history yet — this is a new promo.</div>
        </details>
      </div>

      <aside class="space-y-6">
        <div class="rounded-xl border bg-white p-5">
          <!-- NEW: label + View Gallery button -->
          <div class="flex items-center justify-between mb-2">
            <label class="block text-sm font-semibold">Thumbnail</label>
            <button type="button"
                    class="inline-flex items-center gap-1.5 rounded-lg border px-2.5 py-1.5 text-sm hover:bg-gray-50"
                     @click="$store.gallery && $store.gallery.open({ onlyImages: true })">
              <i class="fa-regular fa-images"></i> View Gallery
            </button>
          </div>

          <div class="aspect-[16/9] bg-gray-100 rounded-lg overflow-hidden mb-3">
              <img x-show="thumbUrl" :src="thumbUrl" class="w-full h-full object-cover" alt="Preview">
            </div>

            <div class="flex items-center gap-2">
              <input type="file" name="featured_image" accept="image/*"
                    @change="previewThumb($event)"
                    class="rounded-lg border px-3 py-2">
              <input type="hidden" name="featured_image_url" x-ref="featured_from_gallery">
              <button type="button"
                      class="rounded-lg border px-2 py-1 text-sm hover:bg-gray-50"
                      @click="thumbUrl=null; if ($refs.featured_from_gallery) $refs.featured_from_gallery.value=''; const f=$root.querySelector('input[type=file][name=featured_image]'); if (f) f.value='';">
                Clear
              </button>
            </div>

            <p class="text-xs text-slate-500 mt-1">
              Tip: Choose a file <em>or</em> open the gallery and click “Use”. If you pick a file, the gallery URL is cleared (and vice-versa).
            </p>
            <p class="text-xs text-gray-500 mt-2">JPG/PNG/WebP/GIF up to 4MB.</p>

        </div>

        {{-- Publishing (PH time) with confirmations (Promos) --}}
        <div id="promo-publish"
          class="rounded-xl border bg-white p-5 space-y-4"
          x-data="pubBox({ defaultMode: 'now', scheduledAtInit: '{{ $promoOldSched }}', expiresAtInit: '{{ $promoOldExp }}' })"
          x-init="init()">

          {{-- intent + hidden confirmed fields --}}
          <!-- intent follows current mode even if pubBox helpers fail -->
        <input type="hidden" name="status_intent" x-ref="status_intent" :value="mode">

        <!-- hidden fields that are ALWAYS kept in sync -->
        <input type="hidden" name="scheduled_publish_date" x-ref="hidden_sched">
        <input type="hidden" name="expires_at" x-ref="hidden_exp">

          <label class="block text-sm font-semibold">
            Publishing <span class="text-xs text-slate-500">(Asia/Manila, UTC+8)</span>
          </label>

          <div class="space-y-2">
            <label class="inline-flex items-center gap-2">
              <input type="radio" name="publish_mode" value="now" class="rounded" x-model="mode" @change="onModeChange()" checked>
              <span class="text-sm">Publish now (default)</span>
            </label>
            <label class="inline-flex items-center gap-2">
              <input type="radio" name="publish_mode" value="schedule" class="rounded" x-model="mode" @change="onModeChange()">
              <span class="text-sm">Schedule</span>
            </label>
          </div>

          <div x-ref="schedWrap"
              x-show="mode==='schedule'"
              x-transition
              x-cloak
              class="grid gap-3">

            {{-- PUBLISH AT --}}
            <div>
              <label class="block text-xs font-semibold mb-1">Publish at</label>
              <div class="flex items-center gap-2">
                <input type="datetime-local" x-ref="published_at"
                value="{{ $promoOldSched }}"
                :disabled="mode!=='schedule'"
                step="60"
                @input="$refs.hidden_sched && ($refs.hidden_sched.value = $event.target.value)"
                class="w-full rounded-lg border-gray-300">

                <button type="button"
                        class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm text-white bg-emerald-600 hover:bg-emerald-700"
                        @click="askConfirmPublish()">
                  <i class="fa-solid fa-check"></i> OK
                </button>
                <button type="button"
                        class="text-xs text-slate-600 underline"
                        x-show="isPublishConfirmed"
                        @click="clearConfirmPublish()">
                  Change
                </button>
              </div>
              <p class="text-xs mt-1"
                :class="isPublishConfirmed ? 'text-emerald-700' : 'text-slate-500'">
                <template x-if="isPublishConfirmed">
                  <span class="inline-flex items-center gap-1"><i class="fa-solid fa-circle-check"></i> Confirmed</span>
                </template>
                <template x-if="!isPublishConfirmed">
                  <span>Select a time and press <strong>OK</strong> to confirm.</span>
                </template>
              </p>
            </div>

            {{-- UNPUBLISH AT (EXPIRES) --}}
            <div>
              <label class="block text-xs font-semibold mb-1">Unpublish at (expires)</label>
              <div class="flex items-center gap-2">
                <input type="datetime-local" x-ref="expires_at"
                  value="{{ $promoOldExp }}"
                  step="60"
                  @input="$refs.hidden_exp && ($refs.hidden_exp.value = $event.target.value)"
                  class="w-full rounded-lg border-gray-300">
                <button type="button"
                        class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm text-white bg-amber-600 hover:bg-amber-700"
                        @click="askConfirmExpire()">
                  <i class="fa-solid fa-check"></i> OK
                </button>
                <button type="button"
                        class="text-xs text-slate-600 underline"
                        x-show="isExpireConfirmed"
                        @click="clearConfirmExpire()">
                  Change
                </button>
              </div>
              <p class="text-xs mt-1"
                :class="isExpireConfirmed ? 'text-emerald-700' : 'text-slate-500'">
                <template x-if="isExpireConfirmed">
                  <span class="inline-flex items-center gap-1"><i class="fa-solid fa-circle-check"></i> Confirmed</span>
                </template>
                <template x-if="!isExpireConfirmed">
                  <span>(Optional) Select a time and press <strong>OK</strong> to confirm expiry.</span>
                </template>
              </p>
            </div>

            <p class="text-xs text-slate-500">
              New promos: <strong>Publish now</strong> makes <code>created_at</code> = publish time.
              <strong>Schedule</strong> keeps <code>created_at</code> as the creation time.
            </p>
          </div>
        </div>


        <div class="rounded-xl border bg-white p-5">
          <div class="flex gap-2">
            <button type="submit" name="action" value="draft" class="px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-50">Save Draft</button>
            <button type="submit" name="action" value="publish" class="px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700">Publish</button>
          </div>
        </div>
      </aside>
    </form>
  </div>

  {{-- ====================== PROMOS: EDIT ====================== --}}
  @isset($editPromo)
  @php
  
      // --- EDIT precompute (backdate-safe, no duplicates) ---
      $tz = 'Asia/Manila';

      // Normalize any date (Carbon|string|null) to Carbon in app TZ
      $toTz = function ($value) use ($tz) {
          if (!$value) return null;
          return $value instanceof \Carbon\Carbon
              ? $value->copy()->timezone($tz)
              : \Carbon\Carbon::parse($value, 'UTC')->timezone($tz);
      };

      $pSchedDT = $toTz($editPromo->scheduled_publish_date ?? null);
      $pPubDT   = $toTz($editPromo->published_at ?? null);
      $pExpDT   = $toTz($editPromo->expires_at ?? null);

      $pSchedOld = old('scheduled_publish_date', null);
      $pExpOld   = old('expires_at', null);

      // Display value for the datetime-local inputs
      // Prefer SCHEDULED; if none, fall back to PUBLISHED; keep any backdated old() value.
      $pSched = ($pSchedOld !== null && $pSchedOld !== '')
          ? $pSchedOld
          : ($pSchedDT
              ? $pSchedDT->format('Y-m-d\TH:i')
              : ($pPubDT ? $pPubDT->format('Y-m-d\TH:i') : null));

      $pExp = ($pExpOld !== null && $pExpOld !== '')
          ? $pExpOld
          : ($pExpDT ? $pExpDT->format('Y-m-d\TH:i') : null);

      // UI mode: if ANY scheduled date exists (past or future), keep 'schedule'.
      // Otherwise 'now' (unless published_at is in the future).
      $pModeDefault = $pSchedDT
          ? 'schedule'
          : (($pPubDT && $pPubDT->isFuture()) ? 'schedule' : 'now');


    $promoThumbUrl = null;
    if (!empty($editPromo->featured_image)) {
      $raw = $editPromo->featured_image;
      $promoThumbUrl = \Illuminate\Support\Str::startsWith($raw, ['http://','https://'])
        ? $raw
        : \Illuminate\Support\Facades\Storage::url($raw);
    }

    $promoEditInit = [
      'title'      => old('title', $editPromo->title),
      'slug'       => old('slug',  $editPromo->slug),
      'slugEdited' => true,
      'thumbUrl'   => $promoThumbUrl,
      'baseUrl'    => rtrim(url('/'), '/'),
      'prefixPath' => 'promos',
    ];

    $effPromo = $effectiveStatus($editPromo);
  @endphp

  <div x-show="openPromoEdit" x-transition id="edit-promo" class="space-y-4">
    <div class="flex items-center justify-between">
  <div class="flex items-center gap-3">
    <h2 class="text-xl font-bold">Edit: {{ $editPromo->title }}</h2>
    <span class="inline-flex items-center gap-1.5 text-xs font-semibold rounded-full px-2.5 py-1 border {{ $effPromo['class'] }}">
      @if($effPromo['slug']==='published')
        <i class="fa-regular fa-circle-check"></i> {{ $effPromo['label'] }}
      @elseif($effPromo['slug']==='scheduled')
        <i class="fa-regular fa-clock"></i> {{ $effPromo['label'] }}
      @elseif($effPromo['slug']==='expired')
        <i class="fa-regular fa-calendar-xmark"></i> {{ $effPromo['label'] }}
      @else
        <i class="fa-regular fa-file-lines"></i> {{ $effPromo['label'] }}
      @endif
    </span>
  </div>
  <div class="flex gap-2">
    <a href="{{ route('admin.dashboard') }}?show=promos#promos"
       @click.prevent="goPromos"
       class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-300 hover:bg-gray-50">
      <i class="fa-solid fa-arrow-left"></i> Back to List
    </a>
  </div>
</div>


    <form
    x-data='Object.assign({}, formGuard(), blogForm(@json($promoEditInit)))'
    @submit.prevent="validateAndSubmit($event)"
    method="POST"
    action="{{ route('admin.promos.update', $editPromo) }}"
    enctype="multipart/form-data"
    class="grid lg:grid-cols-3 gap-6" novalidate>
      @csrf
      @method('PUT')
      <input type="hidden" name="timezone" value="Asia/Manila">

      <div class="lg:col-span-2 space-y-6">
        <div class="rounded-xl border bg-white p-5">
          <label class="block text-sm font-semibold mb-1">Promo Title</label>
          <input type="text" name="title" data-required="true"
                 x-model="title" @input="onTitleInput($event)"
                 class="w-full rounded-lg border-gray-300">

          <div class="mt-4">
            <label class="block text-sm font-semibold mb-1">Slug</label>
            <input type="text" name="slug"
                  x-model="slug" @input="onSlugInput($event)"
                  class="w-full rounded-lg border-gray-300" placeholder="auto-generated if left blank">
           <p class="text-xs text-gray-500 mt-1">This becomes the URL segment.</p>

          @php
            $liveLabelPromo = null; $liveUrlPromo = null;
            if (($effPromo['slug'] ?? '') === 'published') {
                $liveLabelPromo = 'Live URL';
            } elseif (($effPromo['slug'] ?? '') === 'scheduled') {
                $liveLabelPromo = 'Scheduled URL';
            }
            if ($liveLabelPromo && ($editPromo->slug ?? null)) {
                $liveUrlPromo = rtrim(url('/'), '/') . '/promos/' . ($editPromo->slug);
            }
          @endphp


          @if($liveUrlPromo)
            <div class="mt-1 text-xs">
              <span class="text-slate-500">{{ $liveLabelPromo }}:</span>
              <a class="font-medium text-emerald-700 hover:underline break-all"
                href="{{ $liveUrlPromo }}" target="_blank" rel="noopener">{{ $liveUrlPromo }}</a>
            </div>
          @endif

          <!-- Planned URL (edit): /promos/{slug} -->
          @php $base = rtrim(url('/'), '/'); @endphp
          <div class="mt-1 text-xs">
            <span class="text-slate-500">Planned URL:</span>
            <a class="font-medium text-emerald-700 hover:underline break-all"
              :href="(()=>{const s=(slug||'').trim() || (title||'').toString().toLowerCase().trim().replace(/[^a-z0-9]+/g,'-').replace(/(^-|-$)/g,''); return '{{ $base }}/promos/' + s;})()"
              x-text="(()=>{const s=(slug||'').trim() || (title||'').toString().toLowerCase().trim().replace(/[^a-z0-9]+/g,'-').replace(/(^-|-$)/g,''); return '{{ $base }}/promos/' + s;})()"
              target="_blank" rel="noopener"></a>
          </div>



          </div>
        </div>

        <div class="rounded-xl border bg-white p-5">
          <label class="block text-sm font-semibold mb-2">Description</label>
          <textarea id="promo-body-edit" name="body" rows="10" 
          class="w-full rounded-lg border-gray-300">{{ old('body', $editPromo->body) }}</textarea>
        </div>

        {{-- ========== History (Edit) ========== --}}
        
        <details class="rounded-xl border bg-white overflow-hidden group" {{ $promoHistoryCount ? 'open' : '' }}>
          <summary class="flex items-center justify-between gap-3 cursor-pointer select-none px-4 py-3 bg-gray-50 border-b">
            <div class="flex items-center gap-2">
              <span class="font-semibold">History</span>
              <span class="text-xs rounded-full px-2 py-0.5 border bg-white">{{ $promoHistoryCount }}</span>
            </div>
            <i class="fa-solid fa-chevron-down transition-transform duration-200 group-open:rotate-180"></i>
          </summary>

          @php
            $rows = collect($promoHistoryRows ?? []);
            if ($rows->count() > 1) {
              $hasTs = $rows->every(function ($r) {
                return is_array($r) && array_key_exists('ts', $r) && is_numeric($r['ts']);
              });
              $rows = $hasTs
                ? $rows->sortByDesc(function ($r) { return (int) $r['ts']; })->values()
                : $rows->reverse()->values();
            }
          @endphp

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                <tr>
                  <th class="px-4 py-3 text-left font-semibold">Updated by</th>
                  <th class="px-4 py-3 text-left font-semibold">Updated what</th>
                  <th class="px-4 py-3 text-left font-semibold" aria-sort="descending" title="Sorted: newest first">Date updated (PH)</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 text-sm">
                @forelse($rows as $row)
                  <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-slate-800">{{ $row['by'] ?? '' }}</td>
                    <td class="px-4 py-3 text-slate-700">{{ $row['what'] ?? '' }}</td>
                    <td class="px-4 py-3 text-slate-700 whitespace-nowrap">{{ $row['when'] ?? '' }}</td>
                  </tr>
                @empty
                  <tr><td colspan="3" class="px-4 py-6 text-center text-gray-600">No history yet.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </details>

      </div>

      <aside class="space-y-6">
        <div class="rounded-xl border bg-white p-5">
        <div class="flex items-center justify-between mb-2">
          <label class="block text-sm font-semibold">Thumbnail</label>
          <button type="button"
                  class="inline-flex items-center gap-1.5 rounded-lg border px-2.5 py-1.5 text-sm hover:bg-gray-50"
                  @click="$store.gallery && $store.gallery.open({ onlyImages: true })">
            <i class="fa-regular fa-images"></i> View Gallery
          </button>
        </div>

          <div class="aspect-[16/9] bg-gray-100 rounded-lg overflow-hidden mb-3 relative">
            <img x-show="thumbUrl" :src="thumbUrl" class="w-full h-full object-cover" alt="Thumbnail">
          </div>

            <div class="flex items-center gap-2">
              <input type="file" name="featured_image" accept="image/*"
                    @change="previewThumb($event)"
                    class="rounded-lg border px-3 py-2">
              <input type="hidden" name="featured_image_url" x-ref="featured_from_gallery">
              <button type="button"
                      class="rounded-lg border px-2 py-1 text-sm hover:bg-gray-50"
                      @click="thumbUrl=null; if ($refs.featured_from_gallery) $refs.featured_from_gallery.value=''; const f=$root.querySelector('input[type=file][name=featured_image]'); if (f) f.value='';">
                Clear
              </button>
            </div>

            <p class="text-xs text-slate-500 mt-1">
              Tip: Choose a file <em>or</em> open the gallery and click “Use”. If you pick a file, the gallery URL is cleared (and vice-versa).
            </p>
            <p class="text-xs text-gray-500 mt-2">JPG/PNG/WebP/GIF up to 4MB.</p>

        </div>

        {{-- Scheduling (PH time) with confirmations (Promos) --}}
        <div class="rounded-xl border bg-white p-5 space-y-4"
             x-data="pubBox({ defaultMode: '{{ $pModeDefault }}', scheduledAtInit: '{{ $pSched }}', expiresAtInit: '{{ $pExp }}' })"
             x-init="init()">

          <input type="hidden" name="status_intent" x-ref="status_intent" :value="mode">
          <input type="hidden" name="scheduled_publish_date" x-ref="hidden_sched">
          <input type="hidden" name="expires_at" x-ref="hidden_exp">

          <label class="block text-sm font-semibold">
            Scheduling <span class="text-xs text-slate-500">(Asia/Manila, UTC+8)</span>
          </label>

          <div class="space-y-2">
            <label class="inline-flex items-center gap-2">
              <input type="radio" name="publish_mode" value="now" class="rounded" x-model="mode" @change="onModeChange()">
              <span class="text-sm">Publish now</span>
            </label>
            <label class="inline-flex items-center gap-2">
              <input type="radio" name="publish_mode" value="schedule" class="rounded" x-model="mode" @change="onModeChange()">
              <span class="text-sm">Schedule</span>
            </label>
          </div>

          <div x-ref="schedWrap" x-show="mode==='schedule'" x-transition x-cloak class="grid gap-3">
            <div>
              <label class="block text-xs font-semibold mb-1">Publish at</label>
              <div class="flex items-center gap-2">
                <input type="datetime-local" x-ref="published_at"
                value="{{ $pSched }}"
                :disabled="mode!=='schedule'"
                step="60"
                @input="$refs.hidden_sched && ($refs.hidden_sched.value = $event.target.value)"
                class="w-full rounded-lg border-gray-300">
                <button type="button"
                        class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm text-white bg-emerald-600 hover:bg-emerald-700"
                        @click="askConfirmPublish()">
                  <i class="fa-solid fa-check"></i> OK
                </button>
                <button type="button"
                        class="text-xs text-slate-600 underline"
                        x-show="isPublishConfirmed"
                        @click="clearConfirmPublish()">
                  Change
                </button>
              </div>
              <p class="text-xs text-slate-500 mt-1" x-show="{{ ($pSchedDT || $pPubDT) ? 'true' : 'false' }}">
                  @php $curP = $pSchedDT ?: $pPubDT; @endphp
                  @if($curP)
                    Current: {{ $curP->format('M d, Y • g:i A') }} PH /
                            {{ $curP->copy()->timezone('UTC')->format('M d, Y • g:i A') }} UTC
                  @endif
                </p>
              <p class="text-xs mt-1"
                 :class="isPublishConfirmed ? 'text-emerald-700' : 'text-slate-500'">
                <template x-if="isPublishConfirmed">
                  <span class="inline-flex items-center gap-1"><i class="fa-solid fa-circle-check"></i> Confirmed</span>
                </template>
                <template x-if="!isPublishConfirmed">
                  <span>Select a time and press <strong>OK</strong> to confirm.</span>
                </template>
              </p>
            </div>
            <div>
              <label class="block text-xs font-semibold mb-1">Unpublish at (expires)</label>
              <div class="flex items-center gap-2">
                <input type="datetime-local" x-ref="expires_at"
                value="{{ $pExp }}"
                step="60"
                @input="$refs.hidden_exp && ($refs.hidden_exp.value = $event.target.value)"
                class="w-full rounded-lg border-gray-300">

                <button type="button"
                        class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm text-white bg-amber-600 hover:bg-amber-700"
                        @click="askConfirmExpire()">
                  <i class="fa-solid fa-check"></i> OK
                </button>
                <button type="button"
                        class="text-xs text-slate-600 underline"
                        x-show="isExpireConfirmed"
                        @click="clearConfirmExpire()">
                  Change
                </button>
              </div>
              <p class="text-xs text-slate-500 mt-1" x-show="{{ $pExp ? 'true' : 'false' }}">
                @if($pExpDT)
                  Current: {{ $pExpDT->format('M d, Y • g:i A') }} PH / {{ $pExpDT->copy()->timezone('UTC')->format('M d, Y • g:i A') }} UTC
                @endif
              </p>
              <p class="text-xs mt-1"
                 :class="isExpireConfirmed ? 'text-emerald-700' : 'text-slate-500'">
                <template x-if="isExpireConfirmed">
                  <span class="inline-flex items-center gap-1"><i class="fa-solid fa-circle-check"></i> Confirmed</span>
                </template>
                <template x-if="!isExpireConfirmed">
                  <span>(Optional) Select a time and press <strong>OK</strong> to confirm expiry.</span>
                </template>
              </p>
            </div>
            <p class="text-xs text-slate-500">
              Editing: future → <em>Scheduled</em>, past → <em>Published</em>, <code>created_at</code> never changes on edits.
            </p>
          </div>
        </div>

        <div class="rounded-xl border bg-white p-5">
          <div class="flex gap-2">
            <button type="submit" name="action" value="draft"   class="px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-50">Save Draft</button>
            <button type="submit" name="action" value="publish" class="px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700">Update & Publish</button>
          </div>
        </div>
      </aside>
    </form>
  </div>
  @endisset
</div> {{-- /x-data=dashPage --}}


{{-- =============== Chart.js =============== --}}
<script
  src="https://cdn.jsdelivr.net/npm/chart.js@4.4.6/dist/chart.umd.min.js"
  integrity="sha384-Sse/HDqcypGpyTDpvZOJNnG0TT3feGQUkF9H+mnRvic+LjR+K1NhTt8f51KIQ3v3"
  crossorigin="anonymous"
  referrerpolicy="no-referrer">
</script>

{{-- =============== Chart.js: Activity (14 days) =============== --}}
<script>
(function(){
  const dataEl = document.getElementById('analytics-data');
  const canvas = document.getElementById('analyticsChart');
  if (!dataEl || !canvas) return;

  let payload = {};
  try { payload = JSON.parse(dataEl.textContent || dataEl.innerText || '{}'); } catch(_) {}

  function drawChart(){
    if (!window.Chart) return;
    const ctx = canvas.getContext('2d');
    // Recreate safely if re-run
    if (window.__ANALYTICS_CHART__) { window.__ANALYTICS_CHART__.destroy(); }
    window.__ANALYTICS_CHART__ = new Chart(ctx, {
      type: 'line',
      data: {
        labels: payload.labels || [],
        datasets: [
          { label: 'Blogs',  data: payload.blogs  || [], fill: false, tension: 0.3 },
          { label: 'Promos', data: payload.promos || [], fill: false, tension: 0.3 }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: true, position: 'top' }, tooltip: { mode: 'index', intersect: false } },
        interaction: { mode: 'index', intersect: false },
        scales: { y: { beginAtZero: true, ticks: { precision: 0 } }, x: { ticks: { autoSkip: true, maxTicksLimit: 8 } } }
      }
    });
  }

  if (window.Chart) {
    drawChart();
  } else {
    const s = document.createElement('script');
    s.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.6/dist/chart.umd.min.js';
    s.onload = drawChart;
    s.onerror = () => console.warn('Chart.js failed to load');
    document.head.appendChild(s);
  }
})();
</script>

{{-- =============== Alpine helper: pubBox (schedule UI, allows past dates) =============== --}}
<script>
/* Publishing/Scheduling box for Blogs & Promos (Alpine) */
function pubBox({ defaultMode = 'now', scheduledAtInit = '', expiresAtInit = '' } = {}) {
  return {
    mode: defaultMode,
    isPublishConfirmed: !!(scheduledAtInit && String(scheduledAtInit).trim()),
    isExpireConfirmed:  !!(expiresAtInit && String(expiresAtInit).trim()),

    init() {
      // 1) Allow past dates: remove any min attributes browsers might enforce
      [this.$refs?.published_at, this.$refs?.expires_at].forEach(el => {
        if (el?.removeAttribute) el.removeAttribute('min');
      });

      // 2) Seed refs & hidden fields
      if (this.$refs.hidden_sched) this.$refs.hidden_sched.value = scheduledAtInit || '';
      if (this.$refs.hidden_exp)   this.$refs.hidden_exp.value   = expiresAtInit   || '';
      if (this.$refs.published_at) this.$refs.published_at.value = scheduledAtInit || '';
      if (this.$refs.expires_at)   this.$refs.expires_at.value   = expiresAtInit   || '';
      if (this.$refs.status_intent) this.$refs.status_intent.value = this.mode;

      // Optional flag some pages use (align created_at on create)
      if (this.$refs.align_on_create) {
        this.$refs.align_on_create.value = (this.mode === 'now' ? '1' : '0');
      }

      // 3) Keep hidden fields live-synced as user types
      this._wireInputs();
    },

    onModeChange() {
      if (this.$refs.status_intent) this.$refs.status_intent.value = this.mode;

      // If switching to "now", clear any scheduled publish
      if (this.mode === 'now') this.clearConfirmPublish();

      if (this.$refs.align_on_create) {
        this.$refs.align_on_create.value = (this.mode === 'now' ? '1' : '0');
      }
    },

    /* ---------- Publish (schedule) ---------- */
    askConfirmPublish() {
      const v = this._val(this.$refs.published_at);
      if (!v) {
        this.isPublishConfirmed = false;
        if (this.$refs.hidden_sched) this.$refs.hidden_sched.value = '';
        return this.toast('warning','Pick a publish time, then press OK.');
      }
      // Past times are fine — backend will treat past schedule as published
      this.isPublishConfirmed = true;
      if (this.$refs.hidden_sched) this.$refs.hidden_sched.value = v;
      this.toast('success','Publish time confirmed.');
    },
    clearConfirmPublish() {
      this.isPublishConfirmed = false;
      if (this.$refs.hidden_sched) this.$refs.hidden_sched.value = '';
      if (this.$refs.published_at) this.$refs.published_at.value = '';
    },

    /* ---------- Expire (unpublish) ---------- */
    askConfirmExpire() {
      const v = this._val(this.$refs.expires_at);
      if (!v) {
        this.isExpireConfirmed = false;
        if (this.$refs.hidden_exp) this.$refs.hidden_exp.value = '';
        return this.toast('warning','Pick an expiry time, then press OK (optional).');
      }
      this.isExpireConfirmed = true;
      if (this.$refs.hidden_exp) this.$refs.hidden_exp.value = v;
      this.toast('success','Expiry time confirmed.');
    },
    clearConfirmExpire() {
      this.isExpireConfirmed = false;
      if (this.$refs.hidden_exp) this.$refs.hidden_exp.value = '';
      if (this.$refs.expires_at) this.$refs.expires_at.value = '';
    },

    /* ---------- Internal wiring ---------- */
    _wireInputs() {
      const sync = (src, dst) => {
        if (!src || !dst) return;
        // Ensure no min constraint sneaks back in via templating/partials
        src.removeAttribute?.('min');
        src.addEventListener('input', (e) => { dst.value = (e.target.value || '').trim(); });
        src.addEventListener('change', (e) => { dst.value = (e.target.value || '').trim(); });
      };
      sync(this.$refs.published_at, this.$refs.hidden_sched);
      sync(this.$refs.expires_at,   this.$refs.hidden_exp);
    },

    _val(refEl){ return (refEl?.value || '').trim(); },

    /* ---------- helpers ---------- */
    toast(type, message){
      try { window.Alpine?.store('toasts')?.push(type, message); } catch(_) {}
    }
  }
}
</script>




@push('head')
  @once
    <script src="https://cdn.tiny.cloud/1/303vfzwx90ntdo0r3z5ke8oujodiytlf7988o2i24t7x97hi/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
  @endonce
@endpush
@push('scripts')
<script>
(function () {
  const SELECTOR = 'textarea#body-create, textarea#body-edit, textarea#promo-body-create, textarea#promo-body-edit';
  const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || @json(csrf_token());
  const UPLOAD_URL = @json($tinymceUploadUrl);

  function uploadImage(file) {
    const form = new FormData();
    form.append('file', file);
    form.append('_token', CSRF);
    return fetch(UPLOAD_URL, {
      method: 'POST',
      body: form,
      headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin'
    });
  }

  // only init when the textarea is actually visible
  function isVisible(el) {
    if (!el) return false;
    const cs = getComputedStyle(el);
    if (cs.display === 'none' || cs.visibility === 'hidden') return false;
    // if parent chain is display:none, offsetParent null unless position:fixed
    return !!(el.offsetParent || cs.position === 'fixed');
  }

  async function initOne(el) {
    if (!window.tinymce) return false;
    if (el.dataset.mceAttached === '1') return true;
    if (!isVisible(el)) return false;

    // TinyMCE needs a stable id
    if (!el.id) el.id = 'mce_' + Math.random().toString(36).slice(2, 9);

    tinymce.init({
      target: el,
      height: 480,
      branding: false,
      contextmenu: false,
      convert_urls: false,
      menubar: 'file edit view insert format tools table help',
      plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table help wordcount',
      toolbar: 'undo redo | blocks | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | forecolor backcolor | link image media table | removeformat | code',
      automatic_uploads: true,
      images_upload_credentials: true,
      image_caption: true,
      file_picker_types: 'image',
      images_upload_handler: (blobInfo) => new Promise((resolve, reject) => {
        uploadImage(blobInfo.blob())
          .then(async (r) => {
            if (!r.ok) return reject(`Upload failed (${r.status})`);
            const data = await r.json().catch(() => null);
            const url = data?.location || data?.url;
            if (!url) return reject('Invalid JSON (no location/url).');
            resolve(url);
          })
          .catch(err => reject(`Upload error: ${err?.message || err}`));
      }),
      file_picker_callback: (cb, value, meta) => {
        if (meta.filetype !== 'image') return;
        const input = document.createElement('input');
        input.type = 'file'; input.accept = 'image/*';
        input.onchange = () => {
          const file = input.files?.[0];
          if (!file) return;
          uploadImage(file)
            .then(async (r) => {
              if (!r.ok) throw new Error(`Upload failed (${r.status})`);
              const data = await r.json().catch(() => null);
              const url = data?.location || data?.url;
              if (!url) throw new Error('Invalid JSON (no location/url).');
              cb(url, { title: file.name });
            })
            .catch(console.warn);
        };
        input.click();
      },
      setup(ed) {
        ed.on('init', () => { el.dataset.mceAttached = '1'; });
        ed.on('remove', () => { el.dataset.mceAttached = '0'; });
      }
    });

    return true;
  }

  function initAllVisible() {
    if (!window.tinymce) return;
    document.querySelectorAll(SELECTOR).forEach((el) => { initOne(el); });
  }

  // keep textareas in sync on ANY form submit
  document.addEventListener('submit', () => { if (window.tinymce) tinymce.triggerSave(); }, true);

  // boot with retries until TinyMCE CDN is ready
  function boot(retries = 30) {
    if (window.tinymce) { initAllVisible(); return; }
    if (retries <= 0) return;
    setTimeout(() => boot(retries - 1), 120);
  }

  // initial load & BFCache restore
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => boot(), { once: true });
  } else {
    boot();
  }
  window.addEventListener('load', () => initAllVisible());
  window.addEventListener('pageshow', () => initAllVisible());

  // handle in-page nav (your ?show=... links & pushState)
  window.addEventListener('popstate', () => setTimeout(initAllVisible, 150));
  document.addEventListener('click', (e) => {
    const a = e.target.closest('a[href]');
    if (!a) return;
    const href = a.getAttribute('href') || '';
    if (href.includes('?show=') || href.includes('#add-') || href.includes('#edit-') || href.includes('#promos') || href.includes('#blogs')) {
      setTimeout(initAllVisible, 200);
    }
  });

  // Alpine boot (x-cloak removal happens after this)
  document.addEventListener('alpine:init', () => setTimeout(initAllVisible, 0));

  // watch x-show / class / style changes so hidden sections coming visible get editors
   const mo = new MutationObserver((muts) => {
    let shouldCheck = false;
    for (const m of muts) {
      if (m.type === 'attributes') {
        if (m.attributeName === 'style' ||
            m.attributeName === 'class' ||
            m.attributeName === 'x-show' ||
            m.attributeName === 'hidden') { shouldCheck = true; break; }
      } else if (m.type === 'childList') {
        shouldCheck = true; break;
      }
    }
    if (shouldCheck) setTimeout(initAllVisible, 120);
  });

  mo.observe(document.documentElement, {
    attributes: true,
    attributeFilter: ['style','class','x-show','hidden'],
    childList: true,
    subtree: true
  });
})();
</script>
@endpush


<script>
function formGuard () {
  return {
    submitting: false,

    clearErrors(form){
      form.querySelectorAll('.form-error').forEach(n => n.remove());
      form.querySelectorAll('[aria-invalid="true"]').forEach(el => {
        el.removeAttribute('aria-invalid');
        el.classList.remove('border-red-500','focus:ring-red-500');
      });
    },

    flag(el, msg){
      if (!el) return;
      el.setAttribute('aria-invalid','true');
      el.classList.add('border-red-500','focus:ring-red-500');
      const p = document.createElement('p');
      p.className = 'form-error mt-1 text-xs text-red-600';
      p.textContent = msg || 'This field is required.';
      el.insertAdjacentElement('afterend', p);
    },

    validateAndSubmit(e){
      if (this.submitting) return;
      const form = e.target;

      // Ensure TinyMCE writes back into textareas
      try { if (window.tinymce) tinymce.triggerSave(); } catch {}

      // Which submit button was used?
      const submitter = e.submitter || document.activeElement;
      const isDraft = (submitter?.name === 'action' && submitter?.value === 'draft');

      let firstBad = null;
      const toast = (t,m)=>window.Alpine?.store('toasts')?.push(t,m);
      const flag  = (el,msg)=>{ this.flag(el, msg); firstBad ??= el; };

      // 1) Always-required fields
      form.querySelectorAll('[required], [data-required="true"], [data-required="always"]').forEach(el => {
        const val = (el.value ?? '').trim();
        if (!val) flag(el, 'This field is required.');
      });

      // 2) Publish-only required fields (e.g., Body on create)
      if (!isDraft) {
        form.querySelectorAll('[data-required-if="publish"]').forEach(el => {
          const val = (el.value ?? '').trim();
          if (!val) flag(el, 'This field is required to publish.');
        });
      }

      // 3) Scheduling rules – only enforce when actually publishing
      const mode = form.querySelector('input[name="publish_mode"]:checked')?.value || 'now';
      if (!isDraft && mode === 'schedule') {
        const schedHidden  = form.querySelector('input[name="scheduled_publish_date"]');
        // match your Alpine x-ref, fallback to hidden
        const schedVisible = form.querySelector('input[x-ref="published_at"]') || schedHidden;

        const expHidden  = form.querySelector('input[name="expires_at"]');
        const expVisible = form.querySelector('input[x-ref="expires_at"]') || expHidden;

        const schedVal = (schedHidden?.value || '').trim();
        const expVal   = (expHidden?.value   || '').trim();

        if (!schedVal) {
          flag(schedVisible, 'Please pick a time and press OK to confirm.');
        }

        if (schedVal && expVal) {
          const sched = new Date(schedVal);
          const exp   = new Date(expVal);
          if (!isNaN(+sched) && !isNaN(+exp) && exp < sched) {
            flag(expVisible, 'Expiry must be same as or after publish time.');
          }
        }
      }

      if (firstBad) {
        try { firstBad.focus(); } catch {}
        toast?.('error','Please fix the highlighted fields.');
        return;
      }

      // --- FINAL SUBMIT (preserve clicked button name=value even with @submit.prevent) ---
      this.submitting = true;

      // Optional: disable submit buttons to avoid double-submits
      try { form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach(b => b.disabled = true); } catch {}

      // Inject a temporary hidden input that mirrors the clicked submit button
      let tmpBtn;
      if (submitter && submitter.name) {
        tmpBtn = document.createElement('input');
        tmpBtn.type  = 'hidden';
        tmpBtn.name  = submitter.name;
        tmpBtn.value = submitter.value;
        form.appendChild(tmpBtn);
      }

      // Programmatic submit bypasses the prevented submit handler and sends all fields
      form.submit();
    }
  }
}
</script>

<script>
function blogsList(init = {}) {
  return {
    blogQuery:  init.blogQuery  ?? '',
    blogCat:    init.blogCat    ?? '',
    blogStatus: init.blogStatus ?? '',
    blogCreator:init.blogCreator?? '',
    blogSort:   init.blogSort   ?? 'latest',
    endpoint: @json(route('admin.dashboard.fragment.blogs')),
    loading:false,
    init(){
      // intercept pagination clicks inside result
      this.$refs.blogsTarget?.addEventListener('click', (e) => {
        const a = e.target.closest('a');
        if (!a) return;
        const href = a.getAttribute('href');
        if (!href) return;
        // only hijack internal links
        if (href.includes('/fragment/blogs')) {
          e.preventDefault();
          this.fetch(href);
          this.$nextTick(()=>document.getElementById('blogs')?.scrollIntoView({behavior:'smooth'}));
        }
      });
    },
    async fetch(url=null){
      this.loading = true;
      try{
        let u = url ? new URL(url, location.origin) : new URL(this.endpoint, location.origin);
        if (!url) {
          const params = {
            blog_q: this.blogQuery, blog_category: this.blogCat, blog_status: this.blogStatus,
            blog_creator: this.blogCreator, blog_sort: this.blogSort
          };
          Object.entries(params).forEach(([k,v])=>{ if(v!=='' && v!=null) u.searchParams.set(k,v); });
        }
        u.searchParams.set('_ts', Date.now()); // bust caches
        const res  = await fetch(u, { headers: { 'X-Requested-With':'XMLHttpRequest', 'Accept':'text/html' }});
        const html = await res.text();
        this.$refs.blogsTarget.innerHTML = html;
      } catch(e){
        Alpine.store('toasts')?.push('error','Failed to load blogs.');
      } finally { this.loading=false; }
    }
  }
}
</script>

<script>
function promosList(init = {}) {
  return {
    promoQuery:  init.promoQuery  ?? '',
    promoCat:    init.promoCat    ?? '',
    promoStatus: init.promoStatus ?? '',
    promoCreator:init.promoCreator?? '',
    promoSort:   init.promoSort   ?? 'latest',
    endpoint: @json(route('admin.dashboard.fragment.promos')),
    loading:false,
    init(){
      this.$refs.promosTarget?.addEventListener('click', (e) => {
        const a = e.target.closest('a');
        if (!a) return;
        const href = a.getAttribute('href');
        if (!href) return;
        if (href.includes('/fragment/promos')) {
          e.preventDefault();
          this.fetch(href);
          this.$nextTick(()=>document.getElementById('promos')?.scrollIntoView({behavior:'smooth'}));
        }
      });
    },
    async fetch(url=null){
      this.loading = true;
      try{
        let u = url ? new URL(url, location.origin) : new URL(this.endpoint, location.origin);
        if (!url) {
          const p = { promo_q:this.promoQuery, promo_category:this.promoCat, promo_status:this.promoStatus,
                      promo_creator:this.promoCreator, promo_sort:this.promoSort };
          Object.entries(p).forEach(([k,v])=>{ if(v!=='' && v!=null) u.searchParams.set(k,v); });
        }
        u.searchParams.set('_ts', Date.now());
        const res  = await fetch(u, { headers: { 'X-Requested-With':'XMLHttpRequest', 'Accept':'text/html' }});
        const html = await res.text();
        this.$refs.promosTarget.innerHTML = html;
      } catch(e){
        Alpine.store('toasts')?.push('error','Failed to load promos.');
      } finally { this.loading=false; }
    }
  }
}
</script>


<script src="/path/to/alpine.min.js" defer></script>

</section>
@endsection