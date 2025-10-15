<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;

// === Careers Controllers ===
use App\Http\Controllers\JobController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\InterviewController;
use App\Http\Controllers\PsychotestController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\ManpowerDashboardController;
use App\Http\Controllers\WelcomeController;
// === Admin Controllers ===
use App\Http\Controllers\Admin\SiteController as AdminSiteController;

/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/
Route::get('/', WelcomeController::class)->name('welcome');

// Daftar & Detail Lowongan (public)
Route::get('/jobs', [JobController::class, 'index'])->name('jobs.index');
Route::get('/jobs/{job}', [JobController::class, 'show'])
    ->whereUuid('job')
    ->name('jobs.show');

/*
|--------------------------------------------------------------------------
| Authenticated (semua user yang login)
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Profile (default Breeze)
    Route::get('/profile',  [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',[ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile',[ProfileController::class, 'destroy'])->name('profile.destroy');

    // Pelamar: apply & kelola lamaran saya
    Route::post('/jobs/{job}/apply', [ApplicationController::class, 'store'])
        ->whereUuid('job')
        ->name('applications.store');

    Route::get('/me/applications', [ApplicationController::class, 'index'])->name('applications.mine');

    // Pelamar: psikotes
    Route::get('/me/psychotest/{attempt}',  [PsychotestController::class, 'show'])->name('psychotest.show');
    Route::post('/me/psychotest/{attempt}', [PsychotestController::class, 'submit'])->name('psychotest.submit');
});

/*
|--------------------------------------------------------------------------
| Admin (HR & Superadmin)
| - butuh spatie/laravel-permission: middleware('role:hr|superadmin')
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->as('admin.')
    ->middleware(['auth', 'role:hr|superadmin'])
    ->group(function () {

        // CRUD Lowongan (versi admin)
        Route::resource('jobs', JobController::class);

        // Admin: Sites
        Route::resource('sites', AdminSiteController::class);

        // Admin: daftar kandidat & Kanban board
        Route::get('applications',       [ApplicationController::class, 'adminIndex'])->name('applications.index');
        Route::get('applications/board', [ApplicationController::class, 'board'])->name('applications.board');

        // === Perpindahan stage (POST only) ===
        Route::post('applications/{application}/move', [ApplicationController::class, 'moveStage'])
            ->whereUuid('application')
            ->name('applications.move');

        // --- Legacy GET handler (redirect agar tidak 405) ---
        Route::get('applications/{application}/move', function () {
            return redirect()
                ->route('admin.applications.index')
                ->with('warn', 'Aksi pindah stage harus via POST. Tombol lama di halamanmu masih pakai GET — sudah saya arahkan kembali.');
        })->whereUuid('application');

        // AJAX Kanban (POST JSON)
        Route::post('applications/board/move', [ApplicationController::class, 'moveStageAjax'])
            ->name('applications.board.move');

        // ---- Admin: Index pages untuk sidenav (baru) ----
        Route::get('interviews',  [InterviewController::class,  'index'])->name('interviews.index');
        Route::get('psychotests', [PsychotestController::class, 'index'])->name('psychotests.index');
        Route::get('offers',      [OfferController::class,      'index'])->name('offers.index');

        // Admin: jadwal interview (create/store by application)
        Route::post('interviews/{application}', [InterviewController::class, 'store'])
            ->whereUuid('application')
            ->name('interviews.store');

        // Admin: offering letter
        Route::post('offers/{application}', [OfferController::class, 'store'])
            ->whereUuid('application')
            ->name('offers.store');

        Route::get('offers/{offer}/pdf', [OfferController::class, 'pdf'])
            ->whereUuid('offer')
            ->name('offers.pdf');

        // Admin: Dashboard Manpower
        Route::get('dashboard/manpower', ManpowerDashboardController::class)->name('dashboard.manpower');
    });

require __DIR__.'/auth.php';
