<?php

namespace App\Enums;

enum WorkoutStateEnum: string
{
    case WARMUP = 'WARMUP';
    case WORK = 'WORK';
    case REST = 'REST';
    case SWITCH = 'SWITCH';
    case COOLDOWN = 'COOLDOWN';
    case FINISHED = 'FINISHED';
}