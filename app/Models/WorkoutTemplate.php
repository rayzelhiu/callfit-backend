<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkoutTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'work_duration',
        'rest_duration',
        'switch_duration',
        'total_sets',
        'total_rounds',
        'created_by'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function stations()
    {
        return $this->hasMany(WorkoutStation::class, 'template_id');
    }

    public function warmups()
    {
        return $this->hasMany(WarmupExercise::class, 'template_id');
    }

    public function cooldowns()
    {
        return $this->hasMany(CooldownExercise::class, 'template_id');
    }

    public function sessions()
    {
        return $this->hasMany(WorkoutSession::class, 'template_id');
    }
}