<?php

namespace App\Http\Controllers\Session;

use App\Http\Controllers\Controller;
use App\Http\Requests\Session\StartWorkoutSessionRequest;
use App\Models\WorkoutSession;
use App\Models\WorkoutTemplate;
use App\Services\WorkoutEngineService;

class WorkoutSessionController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | START SESSION
    |--------------------------------------------------------------------------
    */
    public function start(StartWorkoutSessionRequest $request)
    {
        $template = WorkoutTemplate::findOrFail($request->template_id);
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        // hanya 1 session aktif per user
        $activeSession = WorkoutSession::where('started_by', $user->id)
            ->whereIn('status', ['running', 'paused'])
            ->exists();

        if ($activeSession) {
            return response()->json([
                'success' => false,
                'message' => 'You already have an active session.'
            ], 422);
        }

        $session = WorkoutSession::create([
            'template_id' => $template->id,
            'started_by' => $user->id,
            'status' => 'running',
            'current_phase' => 'warmup',
            'current_round' => 1,
            'current_station' => 1,
            'current_set' => 1,
            'started_at' => now(),
            'paused_at' => null,
            'finished_at' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Session started',
            'data' => $session
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | CURRENT SESSION (RAW DATA)
    |--------------------------------------------------------------------------
    */
    public function current()
    {
        $session = WorkoutSession::with([
            'template',
            'template.stations.exercise'
        ])
        ->whereIn('status', ['running', 'paused'])
        ->latest()
        ->first();

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'No active workout session.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $session
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | PAUSE SESSION (FREEZE TIME)
    |--------------------------------------------------------------------------
    */
    public function pause(WorkoutEngineService $engine)
    {
        $session = WorkoutSession::where('status', 'running')
            ->latest()
            ->first();

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'No running session.'
            ], 404);
        }

        $session->update([
            'status' => 'paused',
            'paused_at' => now(),
            'current_phase' => $engine->getCurrentPhase($session),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Paused',
            'data' => $session->fresh()
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | RESUME SESSION (ADJUST TIME OFFSET)
    |--------------------------------------------------------------------------
    */
    public function resume()
{
    $session = WorkoutSession::where('status', 'paused')
        ->latest()
        ->first();

    if (!$session) {
        return response()->json([
            'success' => false,
            'message' => 'No paused session.'
        ], 404);
    }

    $pausedSeconds = $session->paused_at
        ? $session->paused_at->diffInSeconds(now())
        : 0;

    $session->update([
        'status' => 'running',
        'paused_total_seconds' =>
            ($session->paused_total_seconds ?? 0) + $pausedSeconds,
        'paused_at' => null,
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Resumed',
        'data' => $session->fresh()
    ]);
}

    /*
    |--------------------------------------------------------------------------
    | FINISH SESSION
    |--------------------------------------------------------------------------
    */
    public function finish()
    {
        $session = WorkoutSession::whereIn('status', ['running', 'paused'])
            ->latest()
            ->first();

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'No active session.'
            ], 404);
        }

        $session->update([
            'status' => 'finished',
            'current_phase' => 'finished',
            'finished_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Workout finished.',
            'data' => $session
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | CURRENT STATE (TV ENGINE OUTPUT)
    |--------------------------------------------------------------------------
    */
    public function currentState(WorkoutEngineService $engine)
    {
        $session = WorkoutSession::with([
            'template.stations.exercise',
            'template.warmups.exercise',
            'template.cooldowns.exercise'
        ])
        ->whereIn('status', ['running', 'paused'])
        ->latest()
        ->first();

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'No active session.'
            ], 404);
        }

        // Auto finish jika engine sudah selesai
        if ($engine->isFinished($session) && $session->status !== 'finished') {
            $session->update([
                'status' => 'finished',
                'finished_at' => now(),
                'current_phase' => 'finished',
            ]);

            $session->refresh();
        }

        $state = $engine->getCurrentState($session);

        // Kalau paused, status diubah menjadi paused,
        // tapi phase/exercise/station/timer tetap dipertahankan
        if ($session->status === 'paused') {
            $state['status'] = 'paused';
        }

        return response()->json([
            'success' => true,
            'data' => $state
        ]);
    }
}