<?php

namespace App\Http\Controllers\TV;

use App\Http\Controllers\Controller;
use App\Models\WorkoutSession;
use App\Services\WorkoutEngineService;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class TVController extends Controller
{
    public function current(Request $request, WorkoutEngineService $engine)
    {
        // Ambil tv_id dari query string (default ke 1)
        $tvId = $request->query('tv_id', '1');

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
                'success' => true,
                'data' => null
            ]);
        }

        Log::info("TV_SESSION_STATE", [
            'id' => $session->id,
            'tv_id' => $tvId,
            'status' => $session->status
        ]);

        return response()->json([
            'success' => true,
            'data' => $engine->getCurrentState($session, $tvId)
        ]);
    }
}