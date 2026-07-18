<?php

namespace App\Services;

use App\Models\WorkoutSession;

class WorkoutEngineService
{
    public function buildSequence($session): array
    {
        $t = $session->template;
        $seq = [];

        foreach ($t->warmups->sortBy('sort_order') as $w) {
            $seq[] = [
                'type' => 'warmup',
                'duration' => $t->warmup_duration,
                'exercise' => $w->exercise,
            ];
        }

        for ($r = 1; $r <= $t->total_rounds; $r++) {
            foreach ($t->stations->sortBy('sort_order') as $st) {
                for ($s = 1; $s <= $t->total_sets; $s++) {
                    $seq[] = [
                        'type' => 'work',
                        'station' => $st->station_number,
                        'duration' => $t->work_duration,
                        'set' => $s,
                    ];

                    if ($s < $t->total_sets) {
                        $seq[] = [
                            'type' => 'rest',
                            'duration' => $t->rest_duration,
                        ];
                    } else {
                        $seq[] = [
                            'type' => 'switch',
                            'duration' => $t->switch_duration,
                        ];
                    }
                }
            }
        }

        foreach ($t->cooldowns->sortBy('sort_order') as $c) {
            $seq[] = [
                'type' => 'cooldown',
                'duration' => $t->cooldown_duration,
            ];
        }

        return $seq;
    }

    public function getCurrentState($session, $tvId = '1')
    {
        if ($session->status === 'paused') {
            return [
                'phase' => 'paused',
                'status' => 'paused',
                'index' => $session->current_step_index,
                'remaining_time' => $session->current_remaining_seconds,
            ];
        }

        $seq = $this->buildSequence($session);
        $start = $session->started_at->timestamp;
        $now = now()->timestamp;
        $elapsed = $now - $start - ($session->paused_total_seconds ?? 0);
        $cursor = 0;

        foreach ($seq as $index => $step) {
            $cursor += $step['duration'];

            if ($elapsed < $cursor) {
                $stepStart = $cursor - $step['duration'];
                $remaining = max(0, $step['duration'] - ($elapsed - $stepStart));

                // Filter stations: TV 1 (1-6), TV 2 (7-12)
                $allStations = $session->template->stations;
                $filteredStations = ($tvId == "1") ? $allStations->take(6) : $allStations->slice(6, 6);

                return [
                    'phase' => $step['type'],
                    'index' => $index,
                    'remaining_time' => $remaining,
                    'set' => $step['set'] ?? null,
                    'active_station_number' => $step['station'] ?? null,
                    'active_exercise_id' => data_get($step, 'exercise.id'),
                    'context' => [
                        'stations' => $filteredStations->values(),
                        'warmups' => $session->template->warmups,
                        'cooldowns' => $session->template->cooldowns,
                    ]
                ];
            }
        }

        if ($elapsed >= array_sum(array_column($seq, 'duration'))) {
            $session->update(['status' => 'finished']);
            return [
                'phase' => 'finished',
                'status' => 'finished',
                'index' => count($seq),
                'remaining_time' => 0,
                'context' => [
                    'stations' => $session->template->stations->take(6), // Fallback
                    'warmups' => $session->template->warmups,
                    'cooldowns' => $session->template->cooldowns,
                ]
            ];
        }
    }
}