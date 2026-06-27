<?php

namespace App\Http\Controllers\Session;

use App\Http\Controllers\Controller;
use App\Models\WorkoutSession;
use App\Services\WorkoutEngineService;
use Illuminate\Http\Request;

class WorkoutSessionController extends Controller
{
    public function queue(Request $request)
    {
        $session = WorkoutSession::create([
            'template_id' => $request->template_id,
            'started_by' => $request->user()->id,
            'status' => 'waiting',
            'current_step_index' => 0,
            'current_remaining_seconds' => 0,
        ]);

        return response()->json(['success' => true, 'data' => $session]);
    }

    public function waiting()
{
   $sessions = \App\Models\WorkoutSession::with([
    'template.stations.exercise',
])
->where('status', 'waiting')
->orderBy('created_at', 'asc')
->get();

return response()->json([
    'success' => true,
    'data' => $sessions->map(function ($s) {
        return [
            'id' => $s->id,
            'status' => $s->status,
            'created_at' => $s->created_at,

            'template' => [
                'id' => $s->template->id,
                'name' => $s->template->name,

                // ONLY STATIONS
                'stations' => $s->template->stations->map(function ($st) {
                    return [
                        'id' => $st->id,
                        'station_number' => $st->station_number,
                        'sort_order' => $st->sort_order,
                        'exercise' => [
                            'id' => $st->exercise->id ?? null,
                            'name' => $st->exercise->name ?? null,
                            'video_url' => $st->exercise->video_url ?? null,
                            'thumbnail_url' => $st->exercise->thumbnail_url ?? null,
                        ],
                    ];
                })->values(),
            ],
        ];
    }),
]);

}

    
public function start(Request $request)
{
    $session = WorkoutSession::find($request->session_id);

    $session->update([
        'status' => 'running',
        'started_at' => now(),
        'current_step_index' => 0,
        'paused_total_seconds' => 0,
    ]);

    return response()->json(['success' => true]);
}


 
public function pause(Request $request)
{
    $session = WorkoutSession::find($request->session_id);

    $engine = app(WorkoutEngineService::class);
    $state = $engine->getCurrentState($session);

    $session->update([
        'status' => 'paused',
        'current_step_index' => $state['index'],
        'paused_snapshot_remaining' => $state['remaining_time'],
        'paused_at' => now(),
    ]);

    return response()->json($state);
}

public function resume(Request $request)
{
    $session = WorkoutSession::find($request->session_id);

    $pausedSeconds = now()->timestamp - $session->paused_at->timestamp;

    $session->update([
        'status' => 'running',
        'paused_total_seconds' => $session->paused_total_seconds + $pausedSeconds,
        'paused_at' => null,
    ]);

    return response()->json(['success' => true]);
}


    public function advance(Request $request, WorkoutEngineService $engine)
    {
        $session = WorkoutSession::findOrFail($request->session_id);

        $nextIndex = $session->current_step_index + 1;

        $step = $engine->buildSequence($session)[$nextIndex] ?? null;

        if (!$step) {
            $session->update(['status' => 'finished']);
            return response()->json(['finished' => true]);
        }

        $session->update([
            'current_step_index' => $nextIndex,
            'current_remaining_seconds' => $step['duration'],
        ]);

        return response()->json(['success' => true]);
    }
}