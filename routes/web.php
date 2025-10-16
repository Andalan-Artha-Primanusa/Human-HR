<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;

// === Public / Careers Controllers ===
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\InterviewController;
use App\Http\Controllers\PsychotestController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\ManpowerDashboardController;

// === Admin Controllers ===
use App\Http\Controllers\Admin\SiteController as AdminSiteController;

/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
|
| Halaman landing & daftar/detail lowongan (tanpa login).
| Pakai whereUuid untuk parameter yang berupa UUID (Laravel mendukung whereUuid()).
|
*/
Route::get('/', WelcomeController::class)->name('welcome');

Route::get('/jobs', [JobController::class, 'index'])->name('jobs.index');
Route::get('/jobs/{job}', [JobController::class, 'show'])
    ->whereUuid('job')
    ->name('jobs.show');

/*
|--------------------------------------------------------------------------
| Authenticated (semua user yang login)
|--------------------------------------------------------------------------
|
| Area kandidat/pelamar: profil, apply job, daftar lamaran saya, psikotes.
|
*/
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Profile (default Breeze)
    Route::get('/profile',  [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',[ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile',[ProfileController::class, 'destroy'])->name('profile.destroy');

    // Apply ke lowongan tertentu (POST-only)
    Route::post('/jobs/{job}/apply', [ApplicationController::class, 'store'])
        ->whereUuid('job')
        ->name('applications.store');

    // Daftar lamaran milik user saat ini
    Route::get('/me/applications', [ApplicationController::class, 'index'])
        ->name('applications.mine');

    // Psikotes (lihat & submit attempt)
    Route::get('/me/psychotest/{attempt}',  [PsychotestController::class, 'show'])
        ->name('psychotest.show');
    Route::post('/me/psychotest/{attempt}', [PsychotestController::class, 'submit'])
        ->name('psychotest.submit');
});

/*
|--------------------------------------------------------------------------
| Admin (HR & Superadmin)
|--------------------------------------------------------------------------
|
| Semua rute di bawah ini memerlukan login + role hr|superadmin
| (pakai spatie/laravel-permission middleware('role:hr|superadmin')).
|
*/
Route::prefix('admin')
    ->as('admin.')
    ->middleware(['auth', 'role:hr|superadmin'])
    ->group(function () {

        /*
         * CRUD Lowongan (versi admin)
         * Hindari bentrok dengan rute publik show() di atas – maka di sini kita exclude 'show'
         */
        Route::resource('jobs', JobController::class)->except(['show']);

        /*
         * Admin: Sites (Resource penuh)
         * Controller: App\Http\Controllers\Admin\SiteController
         * Views yang diharapkan:
         *   - resources/views/admin/sites/index.blade.php
         *   - resources/views/admin/sites/create.blade.php
         *   - resources/views/admin/sites/edit.blade.php
         *   - resources/views/admin/sites/show.blade.php
         */
        Route::resource('sites', AdminSiteController::class);

        /*
         * Admin: daftar kandidat & Kanban board
         */
        Route::get('applications',       [ApplicationController::class, 'adminIndex'])
            ->name('applications.index');
        Route::get('applications/board', [ApplicationController::class, 'board'])
            ->name('applications.board');

        /*
         * Perpindahan stage (POST only) – tombol di UI harus submit ke rute ini.
         * Controller method: ApplicationController@moveStage
         */
        Route::post('applications/{application}/move', [ApplicationController::class, 'moveStage'])
            ->whereUuid('application')
            ->name('applications.move');

        /*
         * Legacy GET handler – kalau masih ada link lama (GET) kita redirect ke index agar tidak 405.
         */
        Route::get('applications/{application}/move', function () {
            return redirect()
                ->route('admin.applications.index')
                ->with('warn', 'Aksi pindah stage harus via POST. Tombol lama di halamanmu masih pakai GET — sudah diarahkan ulang.');
        })->whereUuid('application');

        /*
         * AJAX Kanban (drag & drop) – JSON { application_id, to_stage }
         * Controller method: ApplicationController@moveStageAjax
         */
        Route::post('applications/board/move', [ApplicationController::class, 'moveStageAjax'])
            ->name('applications.board.move');

        /*
         * Index halaman penunjang untuk sidenav admin
         */
        Route::get('interviews',  [InterviewController::class,  'index'])
            ->name('interviews.index');
        Route::get('psychotests', [PsychotestController::class, 'index'])
            ->name('psychotests.index');
        Route::get('offers',      [OfferController::class,      'index'])
            ->name('offers.index');

        /*
         * Admin: jadwal interview (create/store by application)
         */
        Route::post('interviews/{application}', [InterviewController::class, 'store'])
            ->whereUuid('application')
            ->name('interviews.store');

        /*
         * Admin: offering letter
         */
        Route::post('offers/{application}', [OfferController::class, 'store'])
            ->whereUuid('application')
            ->name('offers.store');

        Route::get('offers/{offer}/pdf', [OfferController::class, 'pdf'])
            ->whereUuid('offer')
            ->name('offers.pdf');

        /*
         * Admin: Dashboard Manpower
         */
        Route::get('dashboard/manpower', ManpowerDashboardController::class)
            ->name('dashboard.manpower');
    });

/*
|--------------------------------------------------------------------------
| Auth scaffolding routes (Breeze/Fortify)
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';
