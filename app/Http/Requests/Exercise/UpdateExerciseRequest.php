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
            'video_url' => 'nullable|file|mimes:mp4,webm,mov,avi|max:51200',
            'thumbnail_url' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'category' => 'nullable|in:warmup,workout,cooldown,general',
        ];
    }
}