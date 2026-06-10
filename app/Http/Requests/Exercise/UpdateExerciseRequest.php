<?php

namespace App\Http\Requests\Exercise;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExerciseRequest extends FormRequest
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
            'video_url' => 'nullable|string',
            'thumbnail_url' => 'nullable|string',
            'category' => 'nullable|in:warmup,workout,cooldown,general',
        ];
    }
}