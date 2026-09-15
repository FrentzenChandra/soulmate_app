<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ServisStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $servisId;
    public string $status;
    public string $noServis;
    public ?int $cabangId;

    public function __construct(int $servisId, string $status, string $noServis, ?int $cabangId = null)
    {
        $this->servisId = $servisId;
        $this->status = $status;
        $this->noServis = $noServis;
        $this->cabangId = $cabangId;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('servis-updates'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'ServisStatusUpdated';
    }
}
