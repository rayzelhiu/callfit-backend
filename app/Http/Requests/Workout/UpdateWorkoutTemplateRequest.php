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
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',

        'work_duration' => 'required|integer|min:1',
        'rest_duration' => 'required|integer|min:0',
        'switch_duration' => 'required|integer|min:0',

        'total_sets' => 'required|integer|min:1',
        'total_rounds' => 'required|integer|min:1',
    ];
}
}