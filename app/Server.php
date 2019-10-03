<?php

namespace App;

use App\Manager\DataCenter;
use App\Manager\ExceptionHandler;
use App\Manager\Log;
use App\Manager\Logic;
use App\Manager\Sender;
use App\Manager\TaskManager;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\WebSocket\Frame;
use Swoole\Websocket\Server as Websocket;

class Server
{
    const HOST = '0.0.0.0';

    const PORT = 8811;

    const FRONTEND_PORT = 8812;

    const CONFIG = [
        'worker_num' => 4,
        'task_worker_num' => 4,
        'dispatch_mode' => 5,
        'enable_static_handler' => true,
        'document_root' => null,
    ];

    const CLIENT_CODE_MATCH_PLAYER = 600;

    const CLIENT_CODE_START_ROOM = 601;

    const CLIENT_CODE_MOVE_PLAYER = 602;

    const CLIENT_CODE_MAKE_CHALLENGE = 603;

    const CLIENT_CODE_ACCEPT_CHALLENGE = 604;

    const CLIENT_CODE_REFUSE_CHALLENGE = 605;

    /**
     * @var Logic
     */
    private $logic;

    /**
     * @var Websocket
     */
    private $websocket;

    public function __construct(string $host = null, string $port = null, array $config = [])
    {
        $this->logic = new Logic();

        $this->websocket = new Websocket($host ?? self::HOST, $port ?? self::PORT);
        $this->websocket->set(array_merge(self::CONFIG, $config));
        $this->websocket->listen($host ?? self::HOST, $config['front_port'] ?? self::FRONTEND_PORT, SWOOLE_SOCK_TCP);

        $this->setWebsocketCallback();

        $this->bootstrapException(new ExceptionHandler());
    }

    private function setWebsocketCallback()
    {
        $this->websocket->on('start', [$this, 'onStart']);
        $this->websocket->on('workerStart', [$this, 'onWorkerStart']);
        $this->websocket->on('open', [$this, 'onOpen']);
        $this->websocket->on('message', [$this, 'onMessage']);
        $this->websocket->on('close', [$this, 'onClose']);
        $this->websocket->on('task', [$this, 'onTask']);
        $this->websocket->on('finish', [$this, 'onFinish']);
        $this->websocket->on('request', [$this, 'onRequest']);
    }

    private function bootstrapException(ExceptionHandler $handler)
    {
        error_reporting(-1);
        set_error_handler([$handler, 'handleError']);
        set_exception_handler([$handler, 'handleException']);
        register_shutdown_function([$handler, 'handleShutdown']);
    }

    public function run()
    {
        if (empty($this->websocket->setting['document_root'])) {
            Log::notice('The "document_root" not configured.');
        }

        DataCenter::init();

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

        DataCenter::$server = $server;
    }

    public function onOpen(Websocket $server, Request $request)
    {
        Log::info(sprintf('client open fd：%d', $request->fd));

        $playerId = $request->get['player_id'];

        if (empty(DataCenter::getOnlinePlayer($playerId))) {
            DataCenter::setPlayerInfo($playerId, $request->fd);

            Sender::send($request->fd, '', 0, '连接成功!');
        } else {
            $server->disconnect($request->fd, 4000, '该player_id已在线');
        }
    }

    public function onMessage(Websocket $server, Frame $frame)
    {
        Log::info(sprintf('client fd: %s message: %s', $frame->fd, $frame->data));

        $data = json_decode($frame->data, true);
        $playerId = DataCenter::getPlayerId($frame->fd);

        switch ($data['code']) {
            case self::CLIENT_CODE_MATCH_PLAYER:
                $this->logic->matchPlayer($playerId);
                break;
            case self::CLIENT_CODE_START_ROOM:
                $this->logic->startRoom($data['room_id'], $playerId);
                break;
            case self::CLIENT_CODE_MOVE_PLAYER:
                $this->logic->movePlayer($playerId, $data['direction']);
                break;
            case self::CLIENT_CODE_MAKE_CHALLENGE:
                $this->logic->makeChallenge($data['opponent_id'], $playerId);
                break;
            case self::CLIENT_CODE_ACCEPT_CHALLENGE:
                $this->logic->acceptChallenge($data['challenger_id'], $playerId);
                break;
            case self::CLIENT_CODE_REFUSE_CHALLENGE:
                $this->logic->refuseChallenge($data['challenger_id']);
                break;
        }
    }

    public function onClose(Websocket $server, $fd)
    {
        Log::info(sprintf('client close fd：%d', $fd));

        $this->logic->closeRoom(DataCenter::getPlayerId($fd));

        DataCenter::delPlayerInfo($fd);
    }

    public function onTask(Websocket $server, $taskId, $srcWorkerId, array $data)
    {
        Log::info('onTask', $data);

        $result = false;

        switch ($data['code']) {
            case TaskManager::TASK_CODE_FIND_PLAYER:
                $ret = TaskManager::findPlayer();
                if (!empty($ret)) {
                    $result['data'] = $ret;
                }
                break;
        }

        if (!empty($result)) {
            $result['code'] = $data['code'];
            return $result;
        }
    }

    public function onFinish(Websocket $server, $taskId, $data)
    {
        Log::info('onFinish', $data);

        $code = $data['code'];
        $data = $data['data'];

        switch ($code) {
            case TaskManager::TASK_CODE_FIND_PLAYER:
                $this->logic->createRoom($data['red_player'], $data['blue_player']);
                break;
        }
    }

    public function onRequest(Request $request, Response $response)
    {
        Log::info('onRequest');

        $data = [];

        switch ($request->get['action']) {
            case 'get_online_player':
                $data = [
                    'online_player' => DataCenter::getOnlinePlayerLen(),
                ];
                break;
            case 'get_player_rank':
                $data = [
                    'players_rank' => DataCenter::getPlayerRank(),
                ];
                break;

        }

        if (!empty($data)) {
            $response->end(json_encode($data));
        }
    }
}
