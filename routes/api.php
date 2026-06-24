<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Exercise\ExerciseController;
use App\Http\Controllers\WarmUp\WarmupController;
use App\Http\Controllers\CoolDown\CooldownController;
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


Route::prefix('auth')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

});

    Route::apiResource('exercises', ExerciseController::class);

    Route::apiResource('workouts', WorkoutTemplateController::class);

    Route::apiResource('warmups', WarmupController::class);
    Route::post(
    'warmups/reorder',
    [WarmupController::class, 'reorder']
);

    Route::apiResource('cooldowns', CoolDownController::class);
    
Route::post(
  '/cooldowns/reorder',
  [CooldownController::class, 'reorder']
);

    Route::get('/workouts/{id}/stations', [WorkoutStationController::class, 'index']);
    Route::post('/workouts/stations', [WorkoutStationController::class, 'store']);
    Route::put('/workouts/stations/{id}', [WorkoutStationController::class, 'update']);
    Route::delete('/workouts/stations/{id}', [WorkoutStationController::class, 'destroy']);
    Route::post(
    'workout-stations/reorder',
    [WorkoutStationController::class, 'reorder']
);

    

    Route::post('/sessions/start', [WorkoutSessionController::class, 'start']);
    Route::get('/sessions/current', [WorkoutSessionController::class, 'current']);
    Route::post('/sessions/pause', [WorkoutSessionController::class, 'pause']);
    Route::post('/sessions/resume', [WorkoutSessionController::class, 'resume']);
    Route::post('/sessions/finish', [WorkoutSessionController::class, 'finish']);
    Route::post('/sessions/queue', [WorkoutSessionController::class, 'queue']);
Route::get('/sessions/waiting', [WorkoutSessionController::class, 'waiting']);

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
    Route::get('/sessions/current-state', [WorkoutSessionController::class,'currentState']);
Route::get('/tv/current', [TVController::class, 'current']);


