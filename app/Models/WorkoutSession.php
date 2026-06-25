<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkoutSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_id',
        'started_by',
        'status',
        'current_phase',
        'current_station',
        'current_set',
        'current_round',
        'started_at',
        'finished_at',
        'paused_at',
        'paused_total_seconds', // 🔥 INI WAJIB
        'paused_snapshot_remaining',
            'current_step_index',
    'current_remaining_seconds',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'paused_at' => 'datetime',
    ];

    public function template()
    {
        return $this->belongsTo(WorkoutTemplate::class, 'template_id');
    }

    public function trainer()
    {
        return $this->belongsTo(User::class, 'started_by');
    }
}