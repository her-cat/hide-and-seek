<?php

namespace App\Manager;

use App\Lib\Redis;
use Swoole\WebSocket\Server;

class DataCenter
{
    const CACHE_PREFIX = 'hide-and-seek';

    public static $global;

    /**
     * @var Server
     */
    public static $server;

    public static function redis()
    {
        return Redis::getInstance();
    }

    public static function init()
    {
        $key = sprintf('%s:player_wait_list', self::CACHE_PREFIX);
        self::redis()->del([$key]);

        $key = sprintf('%s:player_id:*', self::CACHE_PREFIX);
        self::redis()->del(self::redis()->keys($key));

        $key = sprintf('%s:player_fd:*', self::CACHE_PREFIX);
        self::redis()->del(self::redis()->keys($key));
    }

    public static function getPlayerWaitListLen()
    {
        $key = sprintf('%s:player_wait_list', self::CACHE_PREFIX);

        return self::redis()->llen($key);
    }

    public static function pushPlayerToWaitList(string $playerId)
    {
        $key = sprintf('%s:player_wait_list', self::CACHE_PREFIX);

        return self::redis()->lpush($key, [$playerId]);
    }

    public static function popPlayerFromWaitList()
    {
        $key = sprintf('%s:player_wait_list', self::CACHE_PREFIX);

        return self::redis()->rpop($key);
    }

    public static function delPlayerWaitList()
    {
        $key = sprintf('%s:player_wait_list', self::CACHE_PREFIX);

        return self::redis()->del([$key]);
    }

    public static function setPlayerId(int $playerFd, string $playerId)
    {
        $key = sprintf('%s:player_id:%s', self::CACHE_PREFIX, $playerFd);

        return self::redis()->set($key, $playerId);
    }

    public static function getPlayerId(int $playerFd)
    {
        $key = sprintf('%s:player_id:%s', self::CACHE_PREFIX, $playerFd);

        return self::redis()->get($key);
    }

    public static function delPlayerId(int $playerFd)
    {
        $key = sprintf('%s:player_id:%s', self::CACHE_PREFIX, $playerFd);

        return self::redis()->del([$key]);
    }

    public static function setPlayerFd(string $playerId, int $playerFd)
    {
        $key = sprintf('%s:player_fd:%s', self::CACHE_PREFIX, $playerId);

        return self::redis()->set($key, $playerFd);
    }

    public static function getPlayerFd(string $playerId)
    {
        $key = sprintf('%s:player_fd:%s', self::CACHE_PREFIX, $playerId);

        return self::redis()->get($key);
    }

    public static function delPlayerFd(string $playerId)
    {
        $key = sprintf('%s:player_fd:%s', self::CACHE_PREFIX, $playerId);

        return self::redis()->del([$key]);
    }

    public static function setPlayerInfo(string $playerId, int $playerFd)
    {
        self::setPlayerId($playerFd, $playerId);
        self::setPlayerFd($playerId, $playerFd);
    }

    public static function delPlayerInfo(int $playerFd)
    {
        $playerId = self::getPlayerId($playerFd);

        self::delPlayerId($playerFd);

        if (is_string($playerId)) {
            self::delPlayerFd($playerId);
        }
    }
}
