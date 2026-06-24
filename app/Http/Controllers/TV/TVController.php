<?php

namespace App\Http\Controllers\TV;

use App\Http\Controllers\Controller;
use App\Models\WorkoutSession;
use App\Services\WorkoutEngineService;

class TVController extends Controller
{
    public function current(WorkoutEngineService $engine)
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
                'success' => true,
                'data' => null
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $engine->getCurrentState($session)
        ]);
    }
}