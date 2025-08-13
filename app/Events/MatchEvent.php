<?php

namespace App\Events;

use App\Models\Matching;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MatchEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $match;
    public $eventType;
    public $eventData;
    public $message;

    public function __construct(Matching $match, string $eventType, array $eventData = [] )
    {
        $this->match = $match;
        $this->eventType = $eventType;
        $this->eventData = $eventData;
        
    }

    public function broadcastOn()
    {
        return new Channel("match.{$this->match->id}");
    }

    public function broadcastAs()
    {
        return $this->eventType;
    }

    public function broadcastWith()
    {
        return $this->eventData;
    }
}