<?php

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use App\WebSocket\TransaksiHandler;

require_once __DIR__ . '/vendor/autoload.php';

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new TransaksiHandler()
        )
    ),
    8081 // Port WebSocket
);

echo "========================================\n";
echo "  WebSocket Server Running\n";
echo "  ws://localhost:8081\n";
echo "  Press Ctrl+C to stop\n";
echo "========================================\n";

$server->run();