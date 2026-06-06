<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkoutStation extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_id',
        'station_number',
        'exercise_id',
        'work_duration',
        'rest_duration',
        'switch_duration',
        'total_sets',
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