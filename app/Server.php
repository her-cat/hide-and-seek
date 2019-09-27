<?php

namespace App;

use App\Manager\ExceptionHandler;
use App\Manager\Log;
use Swoole\Http\Request;
use Swoole\WebSocket\Frame;
use Swoole\Websocket\Server as Websocket;

class Server
{
    const HOST = '0.0.0.0';
    const PORT = 8811;
    const FRONTEND_PORT = 8812;
    const CONFIG = [
        'worker_num' => 4,
        'enable_static_handler' => true,
        'document_root' => null,
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
        if (empty($this->websocket->setting['document_root'])) {
            Log::notice('The "document_root" not configured.');
        }

        $this->websocket->start();
    }

    public function onStart(Websocket $server)
    {
        @swoole_set_process_name('hide-and-seek');

        Log::info(sprintf("master start(listen on %s:%s)", $this->websocket->host, $this->websocket->port));
    }

    public function onWorkerStart(Websocket $server, int $workerId)
    {
        Log::info(sprintf("server: onWorkerStart, worker_id:%s", $workerId));
    }

    public function onOpen(Websocket $server, Request $request)
    {
        Log::info(sprintf('client open fd：%d', $request->fd));
    }

    public function onMessage(Websocket $server, Frame $frame)
    {
        Log::info(sprintf('client fd: %s message: %s', $frame->fd, $frame->data));

        $server->push($frame->fd, 'success');
    }

    public function onClose(Websocket $server, $fd)
    {
        Log::log(sprintf('client close fd：%d', $fd));
    }
}
