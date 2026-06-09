<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Exercise\ExerciseController;
use App\Http\Controllers\Workout\WorkoutTemplateController;
use App\Http\Controllers\Workout\WorkoutStationController;
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



/*
|--------------------------------------------------------------------------
| Workout
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    Route::apiResource('exercises', ExerciseController::class);

    Route::apiResource('workouts', WorkoutTemplateController::class);

    Route::get('/workouts/{id}/stations', [WorkoutStationController::class, 'index']);
    Route::post('/workouts/stations', [WorkoutStationController::class, 'store']);
    Route::put('/workouts/stations/{id}', [WorkoutStationController::class, 'update']);
    Route::delete('/workouts/stations/{id}', [WorkoutStationController::class, 'destroy']);

    Route::post('/sessions/start', [WorkoutSessionController::class, 'start']);
    Route::get('/sessions/current', [WorkoutSessionController::class, 'current']);
    Route::post('/sessions/pause', [WorkoutSessionController::class, 'pause']);
    Route::post('/sessions/resume', [WorkoutSessionController::class, 'resume']);
    Route::post('/sessions/finish', [WorkoutSessionController::class, 'finish']);

});

/*
|--------------------------------------------------------------------------
| TV
|--------------------------------------------------------------------------
*/
Route::get('/time', function () {
    return [
        'config' => config('app.timezone'),
        'now' => now()->toDateTimeString(),
        'php' => date('Y-m-d H:i:s'),
    ];
});

Route::get('/tv/current', [TVController::class, 'current']);
Route::get('/sessions/current-state', [
    WorkoutSessionController::class,
    'currentState'
]);