<?php

namespace App\Http\Controllers\WarmUp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WarmupExercise;

class WarmupController extends Controller
{
    public function index(Request $request)
    {
        return WarmupExercise::with(['exercise', 'template'])
            ->where('template_id', $request->template_id)
            ->orderBy('sort_order')
            ->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'template_id' => 'required|integer|exists:workout_templates,id',
            'exercise_id' => 'required|integer|exists:exercises,id',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if (!isset($validated['sort_order'])) {
            $lastOrder = WarmupExercise::where('template_id', $validated['template_id'])
                ->max('sort_order');

            $validated['sort_order'] = $lastOrder ? $lastOrder + 1 : 1;
        }

        return WarmupExercise::create($validated);
    }

    public function update(Request $request, $id)
    {
        $warmup = WarmupExercise::findOrFail($id);

        $validated = $request->validate([
            'exercise_id' => 'sometimes|integer|exists:exercises,id',
            'sort_order' => 'sometimes|integer|min:0',
        ]);

        $warmup->update($validated);

        return $warmup;
    }

    public function destroy($id)
    {
        WarmupExercise::destroy($id);

        return response()->json([
            'message' => 'Warmup deleted'
        ]);
    }

   public function reorder(Request $request)
{
    $request->validate([
        'items' => 'required|array',
        'items.*.id' => 'required|exists:warmup_exercises,id',
        'items.*.sort_order' => 'required|integer',
    ]);

    foreach ($request->items as $item) {
        WarmupExercise::where('id', $item['id'])
            ->update([
                'sort_order' => $item['sort_order']
            ]);
    }

    return response()->json([
        'success' => true,
        'message' => 'Warmup reordered'
    ]);
}
}