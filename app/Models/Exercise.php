<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exercise extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'video_url',
        'thumbnail_url',
        'category'
    ];

    public function workoutStations()
    {
        return $this->hasMany(WorkoutStation::class, 'exercise_id');
    }

    public function warmups()
    {
        return $this->hasMany(WarmupExercise::class, 'exercise_id');
    }

    public function cooldowns()
    {
        return $this->hasMany(CooldownExercise::class, 'exercise_id');
    }
}