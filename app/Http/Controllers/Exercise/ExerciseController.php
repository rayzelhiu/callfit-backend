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
    $data = $request->validated();

    $data['category'] = $data['category'] ?? 'general';

    // VIDEO SAFE UPLOAD
    if ($request->hasFile('video')) {
        $file = $request->file('video');

        $path = $file->store('videos', 'public');

        $data['video_url'] = asset('storage/' . $path);
    }

    // THUMBNAIL SAFE UPLOAD
    if ($request->hasFile('thumbnail')) {
        $file = $request->file('thumbnail');

        $path = $file->store('thumbnails', 'public');

        $data['thumbnail_url'] = asset('storage/' . $path);
    }

    $exercise = Exercise::create($data);

    return response()->json([
        'success' => true,
        'data' => $exercise
    ]);
}

public function update(UpdateExerciseRequest $request, string $id)
{
    $exercise = Exercise::findOrFail($id);

    $data = $request->validated();

    // VIDEO UPDATE
    if ($request->hasFile('video')) {
        $path = $request->file('video')->store('videos', 'public');
        $data['video_url'] = asset('storage/' . $path);
    }

    // THUMBNAIL UPDATE (FIX UTAMA)
    if ($request->hasFile('thumbnail')) {
        $path = $request->file('thumbnail')->store('thumbnails', 'public');
        $data['thumbnail_url'] = asset('storage/' . $path);
    }

    // ❗ BLOCK STRING RUSAK (INI YANG FIX "FROG")
    if (isset($data['thumbnail_url']) && !str_contains($data['thumbnail_url'], 'http')) {
        unset($data['thumbnail_url']);
    }

    if (isset($data['video_url']) && !str_contains($data['video_url'], 'http')) {
        unset($data['video_url']);
    }

    $exercise->update($data);

    return response()->json([
        'success' => true,
        'data' => $exercise->fresh()
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