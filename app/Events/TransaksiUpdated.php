<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TransaksiUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $transaksiId;
    public string $status;
    public string $noTransaksi;
    public ?int $cabangId;
    public string $action;
    public ?array $rowPayload;

    public function __construct(
        int $transaksiId,
        string $status,
        string $noTransaksi,
        ?int $cabangId = null,
        string $action = 'saved',
        ?array $rowPayload = null
    ) {
        $this->transaksiId = $transaksiId;
        $this->status = $status;
        $this->noTransaksi = $noTransaksi;
        $this->cabangId = $cabangId;
        $this->action = $action;
        $this->rowPayload = $rowPayload;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('transaksi-updates'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'TransaksiUpdated';
    }
}
