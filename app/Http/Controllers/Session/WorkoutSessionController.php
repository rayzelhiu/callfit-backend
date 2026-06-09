<?php

namespace App\Http\Controllers\Session;

use App\Http\Controllers\Controller;
use App\Http\Requests\Session\StartWorkoutSessionRequest;
use App\Models\WorkoutSession;
use App\Models\WorkoutTemplate;
use App\Services\WorkoutEngineService;

class WorkoutSessionController extends Controller
{
    public function start(StartWorkoutSessionRequest $request)
    {
        $template = WorkoutTemplate::findOrFail($request->template_id);

      
        $user = $request->user(); // 🔥 FIX AUTH DI SINI

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated'
            ], 401);
        }
        
        $activeSession = WorkoutSession::whereIn('status', [
            'running',
            'paused'
        ])->exists();

        if ($activeSession) {

            return response()->json([
                'success' => false,
                'message' => 'There is already an active workout session.'
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
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Session started',
            'data' => $session
        ]);
    }

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

    public function pause()
    {
        $session = WorkoutSession::where('status', 'running')->latest()->first();

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'No running workout session.'
            ], 404);
        }

        $session->update([
            'status' => 'paused'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Workout paused.',
            'data' => $session
        ]);
    }

   public function resume()
    {
        $session = WorkoutSession::where('status', 'paused')->latest()->first();

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'No paused workout session.'
            ], 404);
        }

        $session->update([
            'status' => 'running'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Workout resumed.',
            'data' => $session
        ]);
    }
    
    public function finish()
    {
        $session = WorkoutSession::whereIn('status', [
            'running',
            'paused'
        ])->latest()->first();

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'No active workout session.'
            ], 404);
        }

        $session->update([
            'status' => 'finished',
            'current_phase' => 'finished',
            'finished_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Workout finished.',
            'data' => $session
        ]);
    }

        public function currentState(WorkoutEngineService $engine )
        {
            $session = WorkoutSession::with([
                'template.stations.exercise',
                'template.warmups.exercise',
                'template.cooldowns.exercise'
            ])
            ->where('status', 'running')
            ->latest()
            ->first();

            if (!$session) {

                return response()->json([
                    'success' => false,
                    'message' => 'No active workout session.'
                ],404);
            }

            if ($engine->isFinished($session) && $session->status !== 'finished') {
                $session->update([
                    'status' => 'finished',
                    'finished_at' => now(),
                    'current_phase' => 'finished',
                ]);

                $session->refresh();
            }

            return response()->json([
                'success' => true,
                'data' => $engine->getCurrentState($session)
            ]);

            
        }
}