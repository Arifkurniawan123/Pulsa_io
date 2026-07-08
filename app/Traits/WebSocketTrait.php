<?php

namespace App\Traits;

use WebSocket\Client;

trait WebSocketTrait
{
    protected function sendWebSocketNotification($data)
    {
        try {
            $client = new Client("ws://localhost:8081");
            $client->send(json_encode([
                'event' => 'new_transaction',
                'data' => $data
            ]));
            $client->close();
        } catch (\Exception $e) {
            // WebSocket server mati, abaikan
            log_message('debug', 'WebSocket not sent: ' . $e->getMessage());
        }
    }
}