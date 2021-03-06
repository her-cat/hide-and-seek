<?php

namespace App\Manager;

class Sender
{
    const MSG_ROOM_ID = 1001;
    const MSG_WAIT_PLAYER = 1002;
    const MSG_START_GAME = 1003;
    const MSG_GAME_INFO = 1004;
    const MSG_GAME_OVER = 1005;
    const MSG_GAME_CLOSE = 1006;
    const MSG_OPPONENT_OFFLINE = 1007;
    const MSG_MAKE_CHALLENGE = 1008;
    const MSG_REFUSE_CHALLENGE = 1009;

    const CODE_MSG = [
        self::MSG_ROOM_ID => '匹配成功',
        self::MSG_WAIT_PLAYER => '等待其他玩家中……',
        self::MSG_START_GAME => '游戏开始啦~',
        self::MSG_GAME_INFO => 'game info',
        self::MSG_GAME_OVER => '游戏结束啦~',
        self::MSG_GAME_CLOSE => '你的敌人跑路了',
        self::MSG_OPPONENT_OFFLINE => '对手不在线',
        self::MSG_MAKE_CHALLENGE => '发起挑战',
        self::MSG_REFUSE_CHALLENGE => '对方拒绝了你的挑战',
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
