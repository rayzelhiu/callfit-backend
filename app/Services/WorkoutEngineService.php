<?php

namespace App\Services;

use App\Models\WorkoutSession;

class WorkoutEngineService
{
   public function getCurrentState(WorkoutSession $session): array
{
    $seq = $this->buildSequence($session);

    $start = $session->started_at->timestamp;
    $now = now()->timestamp;

    $elapsed = $now - $start - ($session->paused_total_seconds ?? 0);

    $cursor = 0;
    $index = 0;

    $stations = $session->template->stations->values();

    foreach ($seq as $step) {

        $cursor += $step['duration'];

        if ($elapsed < $cursor) {

            $stepStart = $cursor - $step['duration'];
            $remaining = max(0, $step['duration'] - ($elapsed - $stepStart));

            $isWork = $step['type'] === 'work';

            $activeStationIndex = null;
            $activeExercise = null;

            if ($isWork) {

                $activeStationIndex = $stations
                    ->search(fn ($s) => $s->station_number == $step['station']);

                $activeExercise = $step['exercise'] ?? null;
            }

            return [
                'phase' => $step['type'],
                'remaining_time' => $remaining,

                // UI FLAGS
                'is_work_phase' => $isWork,

                'active_station_number' => $step['station'] ?? null,

                'active_station_sort' => $step['sort_order'] ?? null,

                'active_exercise_id' => data_get($step, 'exercise.id'),

                'has_video' => !empty(data_get($step, 'exercise.video_url')),

                'has_thumbnail' => !empty(data_get($step, 'exercise.thumbnail_url')),

                // timeline index (optional)
                'index' => $index,

                // debug / fallback
                'active_station_index' => $activeStationIndex,
                'active_exercise' => $activeExercise,

                'phase_end' => $now + $remaining,

                'round' => $step['round'] ?? null,
                'set' => $step['set'] ?? null,
                'station' => $step['station'] ?? null,

                'context' => [
                    'stations' => $session->template->stations->values(),
                    'warmups' => $session->template->warmups,
                    'cooldowns' => $session->template->cooldowns,
                    'total_stations' => $session->template->stations->count(),
                ]
            ];
        }

        $index++;
    }

    return [
        'phase' => 'finished',
        'remaining_time' => 0,
        'index' => 0
    ];
}
    public function buildSequence($session): array
    {
        $t = $session->template;
        $seq = [];

        $rounds = max(1, (int) $t->total_rounds);
        $sets = max(1, (int) $t->total_sets);

        /* ================= WARMUP (SORT ORDER FIX) ================= */
        foreach ($t->warmups->sortBy('sort_order') as $w) {
            $seq[] = [
                'type' => 'warmup',
                'duration' => $t->warmup_duration,
                'exercise' => $w->exercise,
                'sort_order' => $w->sort_order
            ];
        }

        /* ================= WORK ================= */
        for ($r = 1; $r <= $rounds; $r++) {

            foreach ($t->stations->sortBy('sort_order') as $st) {

                for ($s = 1; $s <= $sets; $s++) {

                    $seq[] = [
                        'type' => 'work',
                        'round' => $r,
                        'station' => $st->station_number,
                        'set' => $s,
                        'duration' => $t->work_duration,
                        'exercise' => $st->exercise,
                        'sort_order' => $st->sort_order
                    ];

                    if ($s < $sets) {
                        $seq[] = [
                            'type' => 'rest',
                            'station' => $st->station_number,
                            'set' => $s,
                            'duration' => $t->rest_duration
                        ];
                    }

                    if ($s === $sets) {
                        $seq[] = [
                            'type' => 'switch',
                            'station' => $st->station_number,
                            'duration' => $t->switch_duration
                        ];
                    }
                }
            }
        }

        /* ================= COOLDOWN (SORT ORDER FIX) ================= */
        foreach ($t->cooldowns->sortBy('sort_order') as $c) {
            $seq[] = [
                'type' => 'cooldown',
                'duration' => $t->cooldown_duration,
                'exercise' => $c->exercise,
                'sort_order' => $c->sort_order
            ];
        }

        $seq[] = ['type' => 'finished', 'duration' => 0];

        return $seq;
    }
}