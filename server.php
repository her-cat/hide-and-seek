<?php

use App\Server;

require_once __DIR__ . '/vendor/autoload.php';

$config = [
    'frontend_port' => 8812,
    'document_root' => '/Users/hexianghui/Code/her-cat/hide-and-seek/public',
];

$server = new Server(null, null, $config);

$server->run();
