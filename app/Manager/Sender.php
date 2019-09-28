<?php

namespace App\Manager;

class Sender
{
    const MSG_ROOM_ID = 1001;
    const MSG_WAIT_PLAYER = 1002;
    const MSG_START_GAME = 1003;
    const MSG_GAME_INFO = 1004;

    const CODE_MSG = [
        self::MSG_ROOM_ID => '匹配成功',
        self::MSG_WAIT_PLAYER => '等待其他玩家中……',
        self::MSG_START_GAME => '游戏开始啦~',
        self::MSG_GAME_INFO => 'game info',
    ];

    public static function send(string $playerFd, $data = '', int $code = 0, string $msg = 'success')
    {
        $message = [
            'code' => $code,
            'msg' => self::CODE_MSG[$code] ?? $msg,
            'data' => $data,
        ];

        DataCenter::$server->push($playerFd, json_encode($message));
    }

    public static function sendByPlayerId(string $playerId, $data = '', int $code = 0, string $msg = 'success')
    {
        $playerFd = DataCenter::getPlayerFd($playerId);
        if (empty($playerFd)) {
            return;
        }

        self::send($playerFd, $data, $code, $msg);
    }
}
