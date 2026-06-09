<?php

namespace App\Http\Requests\Workout;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkoutTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string',

            'warmup_duration' => 'sometimes|integer|min:0',
            'cooldown_duration' => 'sometimes|integer|min:0',

            'work_duration' => 'sometimes|integer|min:1',
            'rest_duration' => 'sometimes|integer|min:0',
            'switch_duration' => 'sometimes|integer|min:0',

            'total_sets' => 'sometimes|integer|min:1',
            'total_rounds' => 'sometimes|integer|min:1',
        ];
    }
}