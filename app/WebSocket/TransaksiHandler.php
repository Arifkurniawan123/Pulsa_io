<?php

namespace App\WebSocket;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class TransaksiHandler implements MessageComponentInterface
{
    protected $clients;
    protected $lastTransactions = [];

    public function __construct()
    {
        $this->clients = new \SplObjectStorage();
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
        echo "Client connected: {$conn->resourceId}\n";
        
        // Kirim data terakhir saat connect
        if (!empty($this->lastTransactions)) {
            $conn->send(json_encode([
                'event' => 'initial_data',
                'data' => $this->lastTransactions
            ]));
        }
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $data = json_decode($msg, true);
        
        if (isset($data['event']) && $data['event'] === 'new_transaction') {
            // Simpan transaksi terbaru (max 50)
            array_unshift($this->lastTransactions, $data['data']);
            if (count($this->lastTransactions) > 50) {
                array_pop($this->lastTransactions);
            }
            
            // Broadcast ke semua client
            foreach ($this->clients as $client) {
                $client->send(json_encode([
                    'event' => 'new_transaction',
                    'data' => $data['data']
                ]));
            }
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        echo "Client disconnected: {$conn->resourceId}\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "Error: {$e->getMessage()}\n";
        $conn->close();
    }
}