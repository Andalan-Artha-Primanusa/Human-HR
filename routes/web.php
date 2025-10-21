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

// === Admin Controllers
use App\Http\Controllers\Admin\SiteController as AdminSiteController;
use App\Http\Controllers\InterviewController as AdminInterviewController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AuditLogController;

// === Public Sites Controller
use App\Http\Controllers\SitePublicController;

// === OTP Verify Code Controller
use App\Http\Controllers\Auth\VerifyCodeController;

// === NEW: pusat notifikasi & interview milik user
use App\Http\Controllers\UserNotificationController;
use App\Http\Controllers\MyInterviewController;

// === NEW: Manpower Requirement Controller (sinkron openings)
use App\Http\Controllers\Admin\ManpowerRequirementController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;
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
| Email Verification via Kode (OTP) — butuh login (belum perlu verified)
| - override verification.notice bawaan ke form kode
| - tambahkan throttle untuk keamanan
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    // Override notice bawaan → redirect ke form OTP
    Route::get('/email/verify', [VerifyCodeController::class, 'notice'])
        ->name('verification.notice');

    // Form input kode
    Route::get('/email/verify/code', [VerifyCodeController::class, 'showForm'])
        ->name('verification.code.form');

    // Submit kode (dibatasi 6x/menit)
    Route::post('/email/verify/code', [VerifyCodeController::class, 'verify'])
        ->middleware('throttle:6,1')
        ->name('verification.code.verify');

    // Kirim ulang kode (dibatasi 6x/menit; controller juga punya cooldown detik)
    Route::post('/email/verify/resend', [VerifyCodeController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('verification.code.resend');
});

/*
|--------------------------------------------------------------------------
| Authenticated (SEMUA wajib verified)
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', fn () => view('dashboard'))
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    // Profile (Breeze)
    Route::get('/profile',  [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Apply job (form profile & submit)
    Route::get('/jobs/{job}/apply/profile',  [CandidateProfileController::class, 'edit'])
        ->whereUuid('job')
        ->name('candidate.profiles.edit');
    Route::post('/jobs/{job}/apply/profile', [CandidateProfileController::class, 'update'])
        ->whereUuid('job')
        ->name('candidate.profiles.update');

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
| Admin (HR & Superadmin) — wajib verified
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->as('admin.')
    ->middleware(['auth', 'verified', 'role:hr|superadmin'])
    ->group(function () {
        // ================= Manpower =================
        Route::get('manpower',                     [ManpowerRequirementController::class, 'index'])->name('manpower.index');
        Route::post('manpower/preview',            [ManpowerRequirementController::class, 'preview'])->name('manpower.preview');
        Route::get('manpower/{job}/edit',          [ManpowerRequirementController::class, 'edit'])->whereUuid('job')->name('manpower.edit'); // <- fix typo
        Route::put('manpower/{job}',               [ManpowerRequirementController::class, 'update'])->whereUuid('job')->name('manpower.update');
        Route::delete('manpower/{job}/{manpower}', [ManpowerRequirementController::class, 'destroy'])
            ->whereUuid(['job', 'manpower'])->name('manpower.destroy');

        // ================= Manpower Dashboard =================
        Route::get('dashboard/manpower', ManpowerDashboardController::class)->name('dashboard.manpower');

        // ================= Jobs (admin) =================
        Route::resource('jobs', JobController::class)->except(['show']);

        // ================= Sites (admin) =================
        Route::resource('sites', AdminSiteController::class);

        // ================= Applications / Kanban =================
        Route::get('applications',       [ApplicationController::class, 'adminIndex'])->name('applications.index');
        Route::get('applications/board', [ApplicationController::class, 'board'])->name('applications.board');

        Route::post('applications/{application}/move', [ApplicationController::class, 'moveStage'])
            ->whereUuid('application')->name('applications.move');

        // Legacy GET -> redirect aman
        Route::get('applications/{application}/move', function () {
            return redirect()->route('admin.applications.index')
                ->with('warn', 'Aksi pindah stage harus via POST. Tombol lama masih pakai GET — diarahkan ulang.');
        })->whereUuid('application');

        // AJAX Kanban
        Route::post('applications/board/move', [ApplicationController::class, 'moveStageAjax'])->name('applications.board.move');

        // ================= Interviews / Psychotest / Offers =================
        Route::get('interviews',  [AdminInterviewController::class, 'index'])->name('interviews.index');
        Route::post('interviews/{application}', [AdminInterviewController::class, 'store'])
            ->whereUuid('application')->name('interviews.store');

        Route::get('psychotests', [PsychotestController::class, 'index'])->name('psychotests.index');

        Route::get('offers',      [OfferController::class, 'index'])->name('offers.index');
        Route::post('offers/{application}', [OfferController::class, 'store'])
            ->whereUuid('application')->name('offers.store');
        Route::get('offers/{offer}/pdf', [OfferController::class, 'pdf'])
            ->whereUuid('offer')->name('offers.pdf');

        // ================= Candidates (read-only admin) =================
        Route::get('candidates',              [CandidateProfileController::class, 'adminIndex'])->name('candidates.index');
        Route::get('candidates/{profile}',    [CandidateProfileController::class, 'adminShow'])->whereUuid('profile')->name('candidates.show');
        Route::get('candidates/{profile}/cv', [CandidateProfileController::class, 'adminCv'])->whereUuid('profile')->name('candidates.cv');

        // ================= Users & Audit Logs =================
        Route::resource('users', UserController::class)->except(['show']);
        Route::get('users-export',  [UserController::class, 'export'])->name('users.export');
        Route::post('users-import', [UserController::class, 'import'])->name('users.import');

        Route::get('audit-logs',        [AuditLogController::class, 'index'])->name('audit_logs.index');
        Route::get('audit-logs/{log}',  [AuditLogController::class, 'show'])->whereUuid('log')->name('audit_logs.show');
        Route::get('audit-logs-export', [AuditLogController::class, 'export'])->name('audit_logs.export');
    });
// === Reset Password (guest only) ===
Route::middleware('guest')->group(function () {
    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    // INI YANG DIPAKAI FORM RESET
    Route::post('/reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

/*
|--------------------------------------------------------------------------
| Auth scaffolding routes (Breeze/Fortify)
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';
