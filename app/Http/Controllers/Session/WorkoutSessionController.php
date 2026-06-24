<?php

namespace App\Http\Controllers\Session;

use App\Http\Controllers\Controller;
use App\Models\WorkoutSession;
use App\Models\WorkoutTemplate;
use Illuminate\Http\Request;

class WorkoutSessionController extends Controller
{
    public function queue(Request $request)
    {
        $template = WorkoutTemplate::findOrFail($request->template_id);

        $session = WorkoutSession::create([
            'template_id' => $template->id,
            'started_by' => $request->user()->id,
            'status' => 'waiting',
            'current_phase' => 'warmup',
            'current_set' => 1,
            'current_round' => 1,
            'started_at' => null,
            'paused_at' => null,
            'paused_total_seconds' => 0,
        ]);

        return response()->json([
            'success' => true,
            'data' => $session
        ]);
    }

    public function start(Request $request)
    {
        $session = WorkoutSession::where('id', $request->session_id)
            ->where('status', 'waiting')
            ->where('started_by', $request->user()->id)
            ->first();

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'Session tidak valid'
            ], 404);
        }

        // close other running session
        WorkoutSession::where('started_by', $request->user()->id)
            ->where('status', 'running')
            ->update(['status' => 'finished']);

        // start session
        $session->update([
            'status' => 'running',
            'started_at' => now(),
        ]);

        // ================= IMPORTANT PART =================
        $engine = app(\App\Services\WorkoutEngineService::class);

        $totalDuration = array_sum(
            array_column($engine->buildSequence($session), 'duration')
        );

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $session->id,
                'started_at' => $session->started_at->timestamp,
                'total_duration' => $totalDuration
            ]
        ]);
    }
    public function waiting()
    {
        return response()->json([
            'success' => true,
            'data' => WorkoutSession::with('template')
                ->where('status', 'waiting')
                ->orderBy('created_at', 'asc')
                ->get()
        ]);
    }

    public function finish()
    {
        $session = WorkoutSession::whereIn('status', ['running', 'paused'])
            ->latest()
            ->first();

        if ($session) {
            $session->update([
                'status' => 'finished',
                'finished_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Finished'
        ]);
    }
}