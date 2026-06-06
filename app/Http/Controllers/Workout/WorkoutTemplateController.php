<?php

namespace App\Http\Controllers\Workout;

use App\Http\Controllers\Controller;
use App\Http\Requests\Workout\StoreWorkoutTemplateRequest;
use App\Http\Requests\Workout\UpdateWorkoutTemplateRequest;
use App\Models\WorkoutTemplate;

class WorkoutTemplateController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => WorkoutTemplate::latest()->get()
        ]);
    }

    public function store(StoreWorkoutTemplateRequest $request)
    {
        $data = $request->validated();

        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated'
            ], 401);
        }

        $data['created_by'] = $user->id;

        $workout = WorkoutTemplate::create($data);

        return response()->json([
            'success' => true,
            'data' => $workout
        ], 201);
    }

    public function show(string $id)
    {
        $workout = WorkoutTemplate::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $workout
        ]);
    }

    public function update(UpdateWorkoutTemplateRequest $request, string $id)
    {
        $workout = WorkoutTemplate::findOrFail($id);

        $workout->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Workout berhasil diupdate',
            'data' => $workout
        ]);
    }

    public function destroy(string $id)
    {
        $workout = WorkoutTemplate::findOrFail($id);

        $workout->delete();

        return response()->json([
            'success' => true,
            'message' => 'Workout berhasil dihapus'
        ]);
    }
}