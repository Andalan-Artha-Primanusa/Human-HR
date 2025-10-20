<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;

// === Public / Careers Controllers ===
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\PsychotestController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\ManpowerDashboardController;
use App\Http\Controllers\CandidateProfileController;

// === Admin Controllers (sesuai screenshot)
use App\Http\Controllers\Admin\SiteController as AdminSiteController;     // Admin/SiteController.php
use App\Http\Controllers\InterviewController as AdminInterviewController; // root/InterviewController.php (admin)
use App\Http\Controllers\Admin\UserController;                            // NEW: Kelola Users (akses HR & Superadmin)
use App\Http\Controllers\Admin\AuditLogController;                        // NEW: Audit Logs (read-only)

// === Public Sites Controller (user non-admin)
use App\Http\Controllers\SitePublicController;

// === NEW: pusat notifikasi & interview milik user
use App\Http\Controllers\UserNotificationController;
use App\Http\Controllers\MyInterviewController;

// === NEW: Manpower Requirement Controller (sinkron openings)
use App\Http\Controllers\Admin\ManpowerRequirementController;

/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/

Route::get('/', WelcomeController::class)->name('welcome');

Route::get('/jobs', [JobController::class, 'index'])->name('jobs.index');
Route::get('/jobs/{job}', [JobController::class, 'show'])
    ->whereUuid('job')
    ->name('jobs.show');

Route::middleware('auth')->group(function () {
    Route::get('/jobs/{job}/apply/profile',  [CandidateProfileController::class, 'edit'])
        ->whereUuid('job')
        ->name('candidate.profiles.edit');
    Route::post('/jobs/{job}/apply/profile', [CandidateProfileController::class, 'update'])
        ->whereUuid('job')
        ->name('candidate.profiles.update');
});

/*
|--------------------------------------------------------------------------
| Public Sites (read-only)
|--------------------------------------------------------------------------
*/
Route::get('/sites', [SitePublicController::class, 'index'])->name('sites.index');
Route::get('/sites/{site}', [SitePublicController::class, 'show'])
    ->whereUuid('site')
    ->name('sites.show');

/*
|--------------------------------------------------------------------------
| Authenticated (semua user)
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', fn() => view('dashboard'))
    ->middleware(['auth'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    // Profile (Breeze)
    Route::get('/profile',  [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Apply job (POST only)
    Route::post('/jobs/{job}/apply', [ApplicationController::class, 'store'])
        ->whereUuid('job')
        ->name('applications.store');

    // Lamaran saya
    Route::get('/me/applications', [ApplicationController::class, 'index'])
        ->name('applications.mine');

    // Psikotes
    Route::get('/me/psychotest/{attempt}',  [PsychotestController::class, 'show'])
        ->whereUuid('attempt')
        ->name('psychotest.show');
    Route::post('/me/psychotest/{attempt}', [PsychotestController::class, 'submit'])
        ->whereUuid('attempt')
        ->name('psychotest.submit');

    // ===== User Notifications =====
    Route::get('/me/notifications', [UserNotificationController::class, 'index'])
        ->name('me.notifications.index');
    Route::post('/me/notifications/read-all', [UserNotificationController::class, 'markAllRead'])
        ->name('me.notifications.read_all');
    Route::post('/me/notifications/{notification}/read', [UserNotificationController::class, 'markRead'])
        ->whereUuid('notification')
        ->name('me.notifications.read');
    Route::delete('/me/notifications/{notification}', [UserNotificationController::class, 'destroy'])
        ->whereUuid('notification')
        ->name('me.notifications.destroy');

    // ===== My Interviews (user) =====
    Route::get('/me/interviews', [MyInterviewController::class, 'index'])
        ->name('me.interviews.index');
    Route::get('/me/interviews/{interview}', [MyInterviewController::class, 'show'])
        ->whereUuid('interview')
        ->name('me.interviews.show');
    Route::get('/me/interviews/{interview}/ics', [MyInterviewController::class, 'ics'])
        ->whereUuid('interview')
        ->name('me.interviews.ics');
});

/*
|--------------------------------------------------------------------------
| Admin (HR & Superadmin)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->as('admin.')
    ->middleware(['auth', 'role:hr|superadmin'])
    ->group(function () {

        // Jobs (resource, tanpa show agar tidak bentrok public show)
        Route::resource('jobs', JobController::class)->except(['show']);

        // Sites (pakai Admin\SiteController sesuai screenshot)
        Route::resource('sites', AdminSiteController::class);

        // Kandidat & Kanban
        Route::get('applications',       [ApplicationController::class, 'adminIndex'])
            ->name('applications.index');
        Route::get('applications/board', [ApplicationController::class, 'board'])
            ->name('applications.board');

        // Pindah stage (POST only)
        Route::post('applications/{application}/move', [ApplicationController::class, 'moveStage'])
            ->whereUuid('application')
            ->name('applications.move');

        // Legacy GET -> redirect
        Route::get('applications/{application}/move', function () {
            return redirect()
                ->route('admin.applications.index')
                ->with('warn', 'Aksi pindah stage harus via POST. Tombol lama masih pakai GET — diarahkan ulang.');
        })->whereUuid('application');

        // AJAX Kanban
        Route::post('applications/board/move', [ApplicationController::class, 'moveStageAjax'])
            ->name('applications.board.move');

        // ===== Admin helper pages =====
        Route::get('interviews',  [AdminInterviewController::class, 'index'])
            ->name('interviews.index');
        Route::get('psychotests', [PsychotestController::class, 'index'])
            ->name('psychotests.index');
        Route::get('offers',      [OfferController::class, 'index'])
            ->name('offers.index');

        // Schedule interview (by application)
        Route::post('interviews/{application}', [AdminInterviewController::class, 'store'])
            ->whereUuid('application')
            ->name('interviews.store');

        // Offer letter
        Route::post('offers/{application}', [OfferController::class, 'store'])
            ->whereUuid('application')
            ->name('offers.store');
        Route::get('offers/{offer}/pdf', [OfferController::class, 'pdf'])
            ->whereUuid('offer')
            ->name('offers.pdf');

        /*
|----------------------------------------------------------------------
| Manpower Dashboard (pakai __invoke di Admin\ManpowerRequirementController)
|----------------------------------------------------------------------
*/
        Route::get('dashboard/manpower', \App\Http\Controllers\Admin\ManpowerRequirementController::class)
            ->name('dashboard.manpower');

        /*
|----------------------------------------------------------------------
| Manpower per Job (assets & ratio) -> sinkron jobs.openings
|----------------------------------------------------------------------
*/
        Route::get('manpower/{job}/edit', [\App\Http\Controllers\Admin\ManpowerRequirementController::class, 'edit'])
            ->whereUuid('job')
            ->name('manpower.edit');

        Route::put('manpower/{job}', [\App\Http\Controllers\Admin\ManpowerRequirementController::class, 'update'])
            ->whereUuid('job')
            ->name('manpower.update');

        Route::delete('manpower/{job}/{manpower}', [\App\Http\Controllers\Admin\ManpowerRequirementController::class, 'destroy'])
            ->whereUuid(['job', 'manpower'])
            ->name('manpower.destroy');

        /* Preview kalkulasi tanpa simpan (dipakai Headcount Estimator) */
        Route::post('manpower/preview', [\App\Http\Controllers\Admin\ManpowerRequirementController::class, 'preview'])
            ->name('manpower.preview');

        // Candidate Profiles (read-only)
        Route::get('candidates', [CandidateProfileController::class, 'adminIndex'])
            ->name('candidates.index');
        Route::get('candidates/{profile}', [CandidateProfileController::class, 'adminShow'])
            ->whereUuid('profile')
            ->name('candidates.show');
        Route::get('candidates/{profile}/cv', [CandidateProfileController::class, 'adminCv'])
            ->whereUuid('profile')
            ->name('candidates.cv');

        /*
        |------------------------------------------------------------------
        | NEW: System Admin — Users & Audit Logs (untuk migrasi & governance)
        |------------------------------------------------------------------
        */

        // Users (HR & Superadmin). Tidak pakai 'show' agar aman.
        Route::resource('users', UserController::class)->except(['show']);

        // Optional: endpoint migrasi/ekspor-impor users (CSV/Excel/JSON)
        Route::get('users-export', [UserController::class, 'export'])
            ->name('users.export');          // GET  /admin/users-export    -> unduh data users
        Route::post('users-import', [UserController::class, 'import'])
            ->name('users.import');          // POST /admin/users-import    -> unggah & proses file

        // Audit Logs (read-only)
        Route::get('audit-logs', [AuditLogController::class, 'index'])
            ->name('audit_logs.index');      // Listing + filter
        Route::get('audit-logs/{log}', [AuditLogController::class, 'show'])
            ->whereUuid('log')
            ->name('audit_logs.show');       // Detail (diff/context)
        Route::get('audit-logs-export', [AuditLogController::class, 'export'])
            ->name('audit_logs.export');     // Ekspor untuk arsip/compliance

        /*
        |------------------------------------------------------------------
        | NEW: Manpower per Job (assets & ratio) -> sinkron jobs.openings
        |------------------------------------------------------------------
        */
        Route::get('manpower/{job}/edit', [ManpowerRequirementController::class, 'edit'])
            ->whereUuid('job')
            ->name('manpower.edit');
        Route::put('manpower/{job}', [ManpowerRequirementController::class, 'update'])
            ->whereUuid('job')
            ->name('manpower.update');

        // (Opsional) endpoint JSON sederhana untuk hitung cepat tanpa simpan
        Route::post('manpower/preview', [ManpowerRequirementController::class, 'preview'])
            ->name('manpower.preview');
    });

/*
|--------------------------------------------------------------------------
| Auth scaffolding routes (Breeze/Fortify)
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';
