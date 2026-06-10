<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CooldownExercise extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_id',
        'exercise_id',
        'sort_order'
    ];

    public function template()
    {
        return $this->belongsTo(WorkoutTemplate::class, 'template_id');
    }

    public function exercise()
    {
        return $this->belongsTo(Exercise::class, 'exercise_id');
    }
}