<?php

namespace App\Http\Controllers\Workout;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WorkoutStation;

class WorkoutStationController extends Controller
{
    /**
     * Get all stations for a workout
     */
    public function index($workoutId)
    {
        return response()->json([
            'success' => true,
            'data' => WorkoutStation::with('exercise')
                ->where('template_id', $workoutId)
                ->orderBy('station_number')
                ->get()
        ]);
    }

    /**
     * Store new station
     */
    public function store(Request $request)
    {
        $request->validate([
            'template_id' => 'required|exists:workout_templates,id',
            'exercise_id' => 'required|exists:exercises,id',
            'station_number' => 'required|integer',
            'sort_order' => 'nullable|integer',
            'work_duration_override' => 'nullable|integer',
            'rest_duration_override' => 'nullable|integer',
            'total_sets_override' => 'nullable|integer',
        ]);

        $station = WorkoutStation::create([
            'template_id' => $request->template_id,
            'exercise_id' => $request->exercise_id,
            'station_number' => $request->station_number,
            'sort_order' => $request->sort_order ?? 1,
            'work_duration_override' => $request->work_duration_override,
            'rest_duration_override' => $request->rest_duration_override,
            'total_sets_override' => $request->total_sets_override,
        ]);

        return response()->json([
            'success' => true,
            'data' => $station
        ], 201);
    }

    /**
     * Update station
     */
   public function update(Request $request, string $id)
    {
        $station = WorkoutStation::findOrFail($id);

        $data = $request->validate([
            'exercise_id' => 'sometimes|exists:exercises,id',
            'station_number' => 'sometimes|integer',
            'sort_order' => 'nullable|integer',
            'work_duration_override' => 'nullable|integer',
            'rest_duration_override' => 'nullable|integer',
            'total_sets_override' => 'nullable|integer',
        ]);

        $station->update($data);

        return response()->json([
            'success' => true,
            'data' => $station
        ]);
    }
    /**
     * Delete station
     */
    public function destroy(string $id)
    {
        WorkoutStation::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Station deleted'
        ]);
    }
}