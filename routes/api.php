<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Exercise\ExerciseController;
use App\Http\Controllers\Workout\WorkoutTemplateController;
use App\Http\Controllers\Session\WorkoutSessionController;
use App\Http\Controllers\TV\TVController;

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

Route::prefix('auth')->group(function () {

    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

});

/*
|--------------------------------------------------------------------------
| Exercise
|--------------------------------------------------------------------------
*/

Route::apiResource('exercises', ExerciseController::class);

/*
|--------------------------------------------------------------------------
| Workout
|--------------------------------------------------------------------------
*/

Route::apiResource('workouts', WorkoutTemplateController::class);

/*
|--------------------------------------------------------------------------
| Session
|--------------------------------------------------------------------------
*/

Route::prefix('sessions')->group(function () {

    Route::post('/start', [WorkoutSessionController::class, 'start']);
    Route::post('/pause', [WorkoutSessionController::class, 'pause']);
    Route::post('/resume', [WorkoutSessionController::class, 'resume']);
    Route::post('/finish', [WorkoutSessionController::class, 'finish']);
    Route::get('/current', [WorkoutSessionController::class, 'current']);

});

/*
|--------------------------------------------------------------------------
| TV
|--------------------------------------------------------------------------
*/

Route::get('/tv/current', [TVController::class, 'current']);