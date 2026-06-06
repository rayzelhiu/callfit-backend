<?php

namespace App\Http\Controllers\Exercise;

use App\Http\Controllers\Controller;
use App\Http\Requests\Exercise\StoreExerciseRequest;
use App\Http\Requests\Exercise\UpdateExerciseRequest;
use App\Models\Exercise;

class ExerciseController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => Exercise::latest()->get()
        ]);
    }

    public function store(StoreExerciseRequest $request)
    {
        $exercise = Exercise::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Exercise berhasil ditambahkan',
            'data' => $exercise
        ], 201);
    }

    public function show(string $id)
    {
        $exercise = Exercise::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $exercise
        ]);
    }

    public function update(UpdateExerciseRequest $request, string $id)
    {
        $exercise = Exercise::findOrFail($id);

        $exercise->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Exercise berhasil diupdate',
            'data' => $exercise
        ]);
    }

    public function destroy(string $id)
    {
        $exercise = Exercise::findOrFail($id);

        $exercise->delete();

        return response()->json([
            'success' => true,
            'message' => 'Exercise berhasil dihapus'
        ]);
    }
}