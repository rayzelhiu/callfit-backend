<?php

namespace App\Services;

use App\Models\WorkoutSession;

class WorkoutEngineService
{
    public function getCurrentState(WorkoutSession $session): array
    {
        $phase = $this->getCurrentPhase($session);

        return [

            'status' => $session->status,

            'phase' => $phase,

            'station' => $phase === 'finished'
                ? null
                : $this->getCurrentStation($session),

            'total_stations' => $this->getStationCount($session),

            'exercise' => $phase === 'finished'
            ? null
            : $this->getCurrentExerciseByPhase($session, $phase),

            'current_set' => $phase === 'finished'
                ? null
                : $this->getCurrentSet($session),

            'total_sets' => $this->template($session)->total_sets,

            'current_round' => $this->getCurrentRound($session),

            'total_rounds' => $this->template($session)->total_rounds,

            'remaining_time' => $this->getRemainingTime($session),

            'is_finished' => $phase === 'finished',

        ];
    }
    public function getElapsedSeconds(WorkoutSession $session): int
    {
        $base = $session->started_at->diffInSeconds(now());

        return max(0, $base - $session->paused_total_seconds);
    }
    private function template(WorkoutSession $session)
    {
        return $session->template;
    }

    private function getStationCount(WorkoutSession $session): int
    {
        return $session->template
            ->stations()
            ->count();
    }

    private function getWarmupTotal(WorkoutSession $session): int
    {
        $template = $this->template($session);

        return $template->warmups()->count()
            * $template->warmup_duration;
    }

    private function getCooldownTotal(WorkoutSession $session): int
    {
        $template = $this->template($session);

        return $template->cooldowns()->count()
            * $template->cooldown_duration;
    }

    private function getStationDuration(WorkoutSession $session): int
    {
        $template = $this->template($session);

        return

            ($template->work_duration * $template->total_sets)

            +

            ($template->rest_duration * ($template->total_sets - 1))

            +

            $template->switch_duration;
    }

    private function getRoundDuration(WorkoutSession $session): int
    {
        return

            $this->getStationDuration($session)

            *

        $this->getStationCount($session);
    }

    private function getWorkoutDuration(WorkoutSession $session): int
    {
        return

            $this->getRoundDuration($session)

            *

            $session->template->total_rounds;
    }

    private function getWarmupCount(WorkoutSession $session): int
    {
        return $session->template
            ->warmups()
            ->count();
    }

    private function getCooldownCount(WorkoutSession $session): int
    {
        return $session->template
            ->cooldowns()
            ->count();
    }

    

    public function getCurrentPhase(WorkoutSession $session): string
    {


        $elapsed = $this->getElapsedSeconds($session);

        $warmupTotal = $this->getWarmupTotal($session);
        $workoutTotal = $this->getWorkoutDuration($session);
        $cooldownTotal = $this->getCooldownTotal($session);

        /*
        |--------------------------------------------------------------------------
        | Warmup
        |--------------------------------------------------------------------------
        */

        if ($elapsed < $warmupTotal) {
            return 'warmup';
        }

        /*
        |--------------------------------------------------------------------------
        | Workout
        |--------------------------------------------------------------------------
        */

        if ($elapsed < ($warmupTotal + $workoutTotal)) {

            $elapsedWorkout = $elapsed - $warmupTotal;

            $roundElapsed = $elapsedWorkout % $this->getRoundDuration($session);

            $stationElapsed = $roundElapsed % $this->getStationDuration($session);

            $template = $this->template($session);

            $cursor = 0;

            for ($set = 1; $set <= $template->total_sets; $set++) {

                // WORK

                if ($stationElapsed < ($cursor + $template->work_duration)) {
                    return 'work';
                }

                $cursor += $template->work_duration;

                // REST

                if ($set < $template->total_sets) {

                    if ($stationElapsed < ($cursor + $template->rest_duration)) {
                        return 'rest';
                    }

                    $cursor += $template->rest_duration;
                }
            }

            return 'switch';
        }

        /*
        |--------------------------------------------------------------------------
        | Cooldown
        |--------------------------------------------------------------------------
        */

        if ($elapsed < ($warmupTotal + $workoutTotal + $cooldownTotal)) {
            return 'cooldown';
        }

        /*
        |--------------------------------------------------------------------------
        | Finished
        |--------------------------------------------------------------------------
        */

        return 'finished';
    }

    public function getCurrentRound(WorkoutSession $session): int
    {
        if ($this->isFinished($session)) {
            return $this->template($session)->total_rounds;
        }

        
        $elapsed = $this->getElapsedSeconds($session);

        // Kurangi waktu warmup
        $elapsed -= $this->getWarmupTotal($session);

        // Masih di warmup
        if ($elapsed < 0) {
            return 1;
        }

        $roundDuration = $this->getRoundDuration($session);

        // Safety jika round duration 0
        if ($roundDuration <= 0) {
            return 1;
        }

        $round = (int) floor($elapsed / $roundDuration) + 1;

        return min(
            max($round, 1),
            $this->template($session)->total_rounds
        );
    }
     public function getCurrentStation(WorkoutSession $session): ?int
    {
        if ($this->isFinished($session)) {
            return null;
        }

        $elapsed = $this->getElapsedSeconds($session);

        $elapsed -= $this->getWarmupTotal($session);

        if ($elapsed < 0) {
            return 1;
        }

        $roundElapsed = $elapsed % $this->getRoundDuration($session);

        $station = floor(
            $roundElapsed / $this->getStationDuration($session)
        ) + 1;

        return min(
            (int) $station,
            $this->getStationCount($session)
        );
    }

    public function getCurrentSet(WorkoutSession $session): ?int
    {

        if ($this->isFinished($session)) {
            return null;
        }
        $template = $this->template($session);

        $elapsed = $this->getElapsedSeconds($session);

        $elapsed -= $this->getWarmupTotal($session);

        if ($elapsed < 0) {
            return 1;
        }

        $roundElapsed = $elapsed % $this->getRoundDuration($session);

        $stationElapsed = $roundElapsed % $this->getStationDuration($session);

        $cursor = 0;

        for ($set = 1; $set <= $template->total_sets; $set++) {

            $cursor += $template->work_duration;

            if ($stationElapsed < $cursor) {
                return $set;
            }

            if ($set < $template->total_sets) {

                $cursor += $template->rest_duration;

                if ($stationElapsed < $cursor) {
                    return $set;
                }
            }
        }

        return $template->total_sets;
    }

    public function getRemainingTime(WorkoutSession $session): int
    {

        if ($this->isFinished($session)) {
            return 0;
        }

        $template = $this->template($session);

        $elapsed = $this->getElapsedSeconds($session);

        $elapsed -= $this->getWarmupTotal($session);

        if ($elapsed < 0) {
            return $this->getWarmupTotal($session) - $this->getElapsedSeconds($session);
        }

        $roundElapsed = $elapsed % $this->getRoundDuration($session);

        $stationElapsed = $roundElapsed % $this->getStationDuration($session);

        $cursor = 0;

        for ($set = 1; $set <= $template->total_sets; $set++) {

            // WORK
            if ($stationElapsed < ($cursor + $template->work_duration)) {

                return ($cursor + $template->work_duration) - $stationElapsed;
            }

            $cursor += $template->work_duration;

            // REST
            if ($set < $template->total_sets) {

                if ($stationElapsed < ($cursor + $template->rest_duration)) {

                    return ($cursor + $template->rest_duration) - $stationElapsed;
                }

                $cursor += $template->rest_duration;
            }
        }

        // SWITCH
        return $this->getStationDuration($session) - $stationElapsed;
    }

        private function getWarmupIndex(WorkoutSession $session): int
    {
        $elapsed = $this->getElapsedSeconds($session);

        return (int) floor(
            $elapsed / max(1, $this->template($session)->warmup_duration)
        );
    }

    private function getCooldownIndex(WorkoutSession $session): int
    {
        $elapsed = $this->getElapsedSeconds($session);

        $workoutTime =
            $this->getWarmupTotal($session) +
            $this->getWorkoutDuration($session);

        $after = $elapsed - $workoutTime;

        if ($after < 0) {
            return 0;
        }

        return (int) floor(
            $after / max(1, $this->template($session)->cooldown_duration)
        );
    }
    private function getWarmupExercise(WorkoutSession $session)
    {
        $index = $this->getWarmupIndex($session);

        return $session->template
            ->warmups()
            ->with('exercise')
            ->orderBy('sort_order')
            ->get()
            ->values()
            ->get($index)
            ?->exercise;
    }

        private function getWorkoutExercise(WorkoutSession $session)
    {
        if ($this->isFinished($session)) {
            return null;
        }

        $stationNumber = $this->getCurrentStation($session);

        if (!$stationNumber) {
            return null;
        }

        $station = $session->template
            ->stations()
            ->with('exercise')
            ->where('station_number', $stationNumber)
            ->first();

        return $station?->exercise;
    }

    private function getCooldownExercise(WorkoutSession $session)
    {
        $index = $this->getCooldownIndex($session);

        return $session->template
            ->cooldowns()
            ->with('exercise')
            ->orderBy('sort_order')
            ->get()
            ->values()
            ->get($index)
            ?->exercise;
    }
   private function getCurrentExerciseByPhase(WorkoutSession $session, string $phase)
    {
        return match ($phase) {

            'warmup' => $this->getWarmupExercise($session),

            'work', 'rest', 'switch' => $this->getWorkoutExercise($session),

            'cooldown' => $this->getCooldownExercise($session),

            default => null,
        };
    }
    
    public function isFinished(WorkoutSession $session): bool
{
    
    return $this->getCurrentPhase($session) === 'finished';
}
}