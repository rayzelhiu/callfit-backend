<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class WorkoutTick implements ShouldBroadcast
{
    public $payload;

    public function __construct($payload)
    {
        $this->payload = $payload;
    }

    public function broadcastOn()
    {
        return new Channel('tv-workout');
    }

    public function broadcastAs()
    {
        return 'tick';
    }
}