<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use App\Http\Controllers\BlogPublicController;
use App\Http\Controllers\PromoPublicController;

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\BlogController as AdminBlogController;
use App\Http\Controllers\Admin\PromoController as AdminPromoController;
use App\Http\Controllers\Admin\MediaController;

use App\Http\Controllers\UnsubscribeController;
use App\Http\Controllers\AccessFormController;

use App\Http\Controllers\SearchController;
use App\Http\Controllers\Admin\PopupController;

/*
|--------------------------------------------------------------------------
| Public pages - MAIN NAV
|--------------------------------------------------------------------------
*/
Route::view('/', 'home')->name('home');
Route::view('/about', 'about')->name('about');
Route::view('/accommodations', 'accommodations')->name('accommodations');
Route::view('/amenities', 'amenities')->name('amenities');
Route::view('/faqs', 'faqs')->name('faqs');
Route::view('/privacy-policy', 'privacy-policy')->name('privacy-policy');
Route::view('/cctv-privacy-policy', 'cctv-policy')->name('cctv-policy');
Route::view('/gallery', 'gallery')->name('gallery');
Route::view('/meetings-events', 'meetings')->name('meetings');
Route::view('/food-beverages', 'dining')->name('dining');
Route::view('/astoria-bohol-lantawan', 'lantawan')->name('lantawan');
Route::view('/explore', 'explore')->name('explore');

/* Search */
Route::get('/search', [SearchController::class, 'index'])->name('search');

/* Public promos */
Route::get('/promos', [PromoPublicController::class, 'index'])->name('promos');
Route::get('/promos/{promo:slug}', [PromoPublicController::class, 'show'])->name('promos.show');

/* Public blogs */
Route::get('/blogs', [BlogPublicController::class, 'index'])->name('blogs');

// Single article: /YYYY/MM/DD/slug
Route::get('/{year}/{month}/{day}/{slug}', [BlogPublicController::class, 'show'])
    ->where('year', '\d{4}') // 4-digit year
    ->where('month', '0[1-9]|1[0-2]')
    ->where('day', '0[1-9]|[12][0-9]|3[01]')
    ->name('articles.show');

Route::view('/contact-us', 'contact')->name('contact');

/*
|--------------------------------------------------------------------------
| Public pages - ACCOMMODATIONS
|--------------------------------------------------------------------------
*/
Route::view('/accommodations/deluxe-room', 'rooms.deluxe')->name('deluxe');
Route::view('/accommodations/luxury-room', 'rooms.luxury')->name('luxury');
/*
|--------------------------------------------------------------------------
| Public pages - OUTLETS DROPDOWN
|--------------------------------------------------------------------------
*/
Route::view('/mangrove-conference-and-convention-center', 'Outlets.mng')->name('mng');
Route::view('/chardonnay-by-astoria', 'Outlets.chy')->name('chy');
Route::view('/minami-saki-by-astoria', 'Outlets.msk')->name('msk');

/*
|--------------------------------------------------------------------------
| Hidden login at /apz-admin (keep name=login)
|--------------------------------------------------------------------------
*/
// GET: show login if guest; otherwise go to dashboard
Route::get('/apz-admin', function (Request $request) {
    if (Auth::check()) {
        return redirect()->route('admin.dashboard');
    }
    return view('auth.login');
})->name('login');

// POST: login (guest-only + throttled)
Route::post('/apz-admin', function (Request $request) {
    $credentials = $request->validate([
        'email'    => ['required','email'],
        'password' => ['required'],
    ]);

    $remember = $request->boolean('remember');

    if (Auth::attempt($credentials, $remember)) {
        $request->session()->regenerate();
        return redirect()->intended(route('admin.dashboard'));
    }

    return back()->withErrors([
        'email' => 'The provided credentials do not match our records.',
    ])->onlyInput('email');
})->middleware(['guest','throttle:6,1'])->name('login.store');

/*
|--------------------------------------------------------------------------
| Forgot Password (guest only)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/forgot-password', fn () => view('auth.forgot-password'))->name('password.request');

    Route::post('/forgot-password', function (Request $request) {
        $request->validate(['email' => ['required','email']]);

        $status = \Illuminate\Support\Facades\Password::sendResetLink($request->only('email'));
        return back()->with('status', __($status));
    })->middleware('throttle:3,1')->name('password.email');

    Route::get('/reset-password/{token}', function (string $token, Request $request) {
        return view('auth.reset-password', ['token' => $token, 'email' => $request->query('email')]);
    })->name('password.reset');

    Route::post('/reset-password', function (Request $request) {
        $request->validate([
            'token'    => ['required'],
            'email'    => ['required','email'],
            'password' => ['required','confirmed', \Illuminate\Validation\Rules\Password::defaults()],
        ]);

        $status = \Illuminate\Support\Facades\Password::reset(
            $request->only('email','password','password_confirmation','token'),
            function ($user, $password) {
                $user->forceFill([
                    'password'       => \Illuminate\Support\Facades\Hash::make($password),
                    'remember_token' => \Illuminate\Support\Str::random(60),
                ])->save();

                event(new \Illuminate\Auth\Events\PasswordReset($user));
            }
        );

        return $status === \Illuminate\Support\Facades\Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    })->middleware('throttle:6,1')->name('password.update');
});

/*
|--------------------------------------------------------------------------
| Admin area (authenticated)
| URL: /admin, route names: admin.*
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->middleware(['web','auth'])->group(function () {

    /* ---------- BLOGS: Bulk actions (names used in Blade) ---------- */
    Route::post('blogs/bulk-trash',   [AdminBlogController::class, 'bulkTrash'])->name('blogs.bulkTrash');
    Route::post('blogs/bulk-restore', [AdminBlogController::class, 'bulkRestore'])->name('blogs.bulkRestore');
    Route::post('blogs/bulk-force',   [AdminBlogController::class, 'bulkForceDelete'])->name('blogs.bulkForceDelete');

    Route::get('/', fn () => redirect()->route('admin.dashboard'))->name('root');

    // Canonical dashboard (single-page admin)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Dashboard fragments (AJAX)
    Route::get('/dashboard/fragment/blogs',  [DashboardController::class, 'blogsFragment'])
        ->middleware('throttle:60,1')
        ->name('dashboard.fragment.blogs');

    Route::get('/dashboard/fragment/promos', [DashboardController::class, 'promosFragment'])
        ->middleware('throttle:60,1')
        ->name('dashboard.fragment.promos');

    // ✅ Fragment for trashed promos table
    Route::get('/dashboard/fragment/promos-trash', [DashboardController::class, 'promosTrashFragment'])
        ->middleware('throttle:60,1')
        ->name('dashboard.fragment.promos-trash');

    /*
     * BLOGS (single-page style: redirect legacy endpoints to dashboard panels)
     */
    Route::get('/blogs', fn () =>
        redirect()->to(route('admin.dashboard', ['show' => 'blogs']) . '#blogs')
    )->name('blogs.index');

    Route::get('/blogs/create', fn () =>
        redirect()->to(route('admin.dashboard', ['show' => 'add-blog']) . '#add-blog')
    )->name('blogs.create');

    // Mutations
    Route::post('/blogs', [AdminBlogController::class, 'store'])->name('blogs.store');

    Route::get('/blogs/{article}/edit',  [AdminBlogController::class, 'edit'])
        ->whereNumber('article')->name('blogs.edit');

    Route::put('/blogs/{article}',       [AdminBlogController::class, 'update'])
        ->whereNumber('article')->name('blogs.update');

    Route::delete('/blogs/{article}',    [AdminBlogController::class, 'destroy'])
        ->whereNumber('article')->name('blogs.destroy');

    // Trash actions
    Route::post('/blogs/{id}/restore',   [AdminBlogController::class, 'restore'])
        ->whereNumber('id')->name('blogs.restore');

    Route::delete('/blogs/{id}/force',   [AdminBlogController::class, 'forceDelete'])
        ->whereNumber('id')->name('blogs.forceDelete');

    /*
     * CATEGORIES (dashboard manager)
     */
    Route::post('/categories', [AdminBlogController::class, 'storeCategory'])->name('categories.store');
    Route::delete('/categories/{category}', [AdminBlogController::class, 'destroyCategory'])
        ->whereNumber('category')->name('categories.destroy');

    /*
     * PROMOS (resource; index is handled by dashboard panel)
     * Blade expects:
     *  - route('admin.promos.edit', $promo)
     *  - route('admin.promos.destroy', $promo)
     *  - route('admin.promos.bulkTrash')
     *  - route('admin.promos.bulkRestore')
     *  - route('admin.promos.bulkForceDelete')
     *  - route('admin.promos.restore', $id)
     *  - route('admin.promos.forceDelete', $id) OR route('admin.promos.force-delete', $id)
     */
    Route::resource('promos', AdminPromoController::class)->except(['show','index']);

    // Redirect legacy index to dashboard panel
    Route::get('/promos', fn () =>
        redirect()->to(route('admin.dashboard', ['show' => 'promos']) . '#promos')
    )->name('promos.index');

    // Single-item restore / force delete
    Route::post('/promos/{id}/restore', [AdminPromoController::class, 'restore'])
        ->whereNumber('id')->name('promos.restore');

    Route::delete('/promos/{id}/force', [AdminPromoController::class, 'forceDelete'])
        ->whereNumber('id')->name('promos.forceDelete');

    // Alias used by Blade fallback detection
    Route::delete('/promos/{id}/force-delete', [AdminPromoController::class, 'forceDelete'])
        ->whereNumber('id')->name('promos.force-delete');

    // BULK endpoints (used by promos_table & promos_trashtable)
    Route::post('/promos/bulk-trash',   [AdminPromoController::class, 'bulkTrash'])
        ->middleware('throttle:60,1')->name('promos.bulkTrash');

    Route::post('/promos/bulk-restore', [AdminPromoController::class, 'bulkRestore'])
        ->middleware('throttle:60,1')->name('promos.bulkRestore');

    Route::post('/promos/bulk-force',   [AdminPromoController::class, 'bulkForceDelete'])
        ->middleware('throttle:60,1')->name('promos.bulkForceDelete');

    /*
     * MEDIA
     *  - admin.media.index
     *  - admin.media.list
     *  - admin.media.upload
     *  - admin.media.update
     *  - admin.media.delete
     */
    Route::prefix('media')->name('media.')->group(function () {
        Route::get('/', [MediaController::class, 'index'])->name('index');

        // Accept both PATCH and POST (POST with _method=PATCH)
        Route::match(['patch','post'], '/update', [MediaController::class, 'update'])->name('update');

        Route::get('/list', [MediaController::class, 'list'])
            ->middleware('throttle:60,1')->name('list');

        Route::post('/upload', [MediaController::class, 'upload'])
            ->middleware('throttle:60,1')->name('upload');

        Route::delete('/delete', [MediaController::class, 'delete'])->name('delete');

    /*
     * POP-UP
     */

      
    });

     Route::resource('popups', PopupController::class)->except(['show']);

    /*
     * TinyMCE upload (reuses MediaController@upload)
     * Matches Blade fallback: route('admin.tinymce.upload')
     */
    Route::post('/uploads/tinymce', [MediaController::class, 'upload'])
        ->middleware('throttle:60,1')->name('tinymce.upload');

    /*
     * Admin logout lives at /admin/logout
     */
    Route::post('/logout', function (Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    })->name('logout');
});

/*
|--------------------------------------------------------------------------
| Optional global logout (outside admin)
|--------------------------------------------------------------------------
*/
Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('home');
})->name('logout.global');

/*
|--------------------------------------------------------------------------
| FORMS
|--------------------------------------------------------------------------
*/
// Unsubscribe
Route::get('/unsubscribe', fn () => view('forms.unsubscribe'))->name('unsubscribe.show');
Route::post('/unsubscribe', [UnsubscribeController::class, 'store'])->name('unsubscribe.store');

// Access form
Route::view('/access-form', 'forms.access-form')->name('access-form.show');
Route::post('/access-form', [AccessFormController::class, 'store'])->name('access-form.store');

/*
|--------------------------------------------------------------------------
| 404
|--------------------------------------------------------------------------
*/
Route::fallback(fn () => response()->view('errors.404', [], 404));
