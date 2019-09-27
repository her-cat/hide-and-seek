<?php

namespace App;

use App\Manager\ExceptionHandler;
use App\Manager\Log;
use Swoole\Websocket\Server as Websocket;

class Server
{
    const HOST = '0.0.0.0';
    const PORT = 8811;
    const FRONTEND_PORT = 8812;
    const CONFIG = [
        'worker_num' => 4,
        'enable_static_handler' => true,
    ];

    /**
     * @var Websocket
     */
    private $websocket;

    public function __construct(string $host = null, string $port = null, array $config = [])
    {
        $this->websocket = new Websocket($host ?? self::HOST, $port ?? self::PORT);
        $this->websocket->set(array_merge(self::CONFIG, $config));
        $this->websocket->listen($host ?? self::HOST, $config['front_port'] ?? self::FRONTEND_PORT, SWOOLE_SOCK_TCP);

        $this->setWebsocketCallback();
    }

    private function setWebsocketCallback()
    {
        $this->websocket->on('start', [$this, 'onStart']);
        $this->websocket->on('workerStart', [$this, 'onWorkerStart']);
        $this->websocket->on('open', [$this, 'onOpen']);
        $this->websocket->on('message', [$this, 'onMessage']);
        $this->websocket->on('close', [$this, 'onClose']);
    }

    public function run()
    {
        $this->websocket->start();
    }

    public function onStart($server)
    {
        @swoole_set_process_name('hide-and-seek');

        Log::info(sprintf("master start(listen on %s:%s)", $this->websocket->host, $this->websocket->port));
    }

    public function onWorkerStart($server, $workerId)
    {
        Log::info(sprintf("server: onWorkerStart, worker_id:%s", $workerId));
    }

    public function onOpen($server, $request)
    {
        Log::info(sprintf('client open fd：%d', $request->fd));
    }

    public function onMessage($server, $request)
    {
        Log::info(sprintf('client fd: %s message: %s', $request->fd, $request->data));

        $server->push($request->fd, 'success');
    }

    public function onClose($server, $fd)
    {
        Log::log(sprintf('client close fd：%d', $fd));
    }
}
