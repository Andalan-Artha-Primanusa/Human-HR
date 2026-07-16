
<?php
use App\Http\Controllers\Api\PohController;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PublicApplicationController;
use App\Http\Controllers\Api\PublicJobController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1'); // 5 login attempts per minute per IP
Route::apiResource('pohs', PohController::class);

Route::middleware(['api.token', 'verified'])->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::middleware('role:hr|superadmin|trainer')->group(function () {
        Route::get('/users', [UserController::class, 'index'])
            ->middleware('throttle:60,1');
        Route::get('/users/{user}', [UserController::class, 'show'])
            ->middleware('throttle:60,1');
    });
});

Route::prefix('public')->middleware('throttle:30,1')->group(function () {
    Route::post('/jobs/apply/profile', [PublicApplicationController::class, 'injectApplicantProfile']);
    Route::post('/jobs/apply/cv', [PublicApplicationController::class, 'uploadCvByCode']);
    Route::post('/jobs/{job}/apply/cv', [PublicApplicationController::class, 'uploadCv']);
});

Route::prefix('public')->middleware(['public.api.login', 'throttle:30,1'])->group(function () { // 30 requests per minute
    Route::get('/jobs', [PublicJobController::class, 'index']);
    Route::get('/jobs/code/{code}', [PublicJobController::class, 'showByCode']);
    Route::get('/jobs/{job}', [PublicJobController::class, 'show']);
    Route::get('/users', [UserController::class, 'publicIndex']);
    Route::get('/users/{user}', [UserController::class, 'publicShow']);
});
