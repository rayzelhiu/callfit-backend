<?php

namespace App\Http\Controllers\CoolDown;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CooldownExercise;

class CooldownController extends Controller
{
    public function index(Request $request)
    {
        return CooldownExercise::with(['exercise', 'template'])
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
            $lastOrder = CooldownExercise::where('template_id', $validated['template_id'])
                ->max('sort_order');

            $validated['sort_order'] = $lastOrder ? $lastOrder + 1 : 1;
        }

        return CooldownExercise::create($validated);
    }

    public function update(Request $request, $id)
    {
        $cooldown = CooldownExercise::findOrFail($id);

        $validated = $request->validate([
            'exercise_id' => 'sometimes|integer|exists:exercises,id',
            'sort_order' => 'sometimes|integer|min:0',
        ]);

        $cooldown->update($validated);

        return $cooldown;
    }

    public function destroy($id)
    {
        CooldownExercise::destroy($id);

        return response()->json([
            'message' => 'Cooldown deleted'
        ]);
    }
}