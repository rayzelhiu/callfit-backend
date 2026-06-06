<?php

namespace App\Enums;

enum SessionStatusEnum: string
{
    case WAITING = 'WAITING';
    case RUNNING = 'RUNNING';
    case PAUSED = 'PAUSED';
    case FINISHED = 'FINISHED';
}