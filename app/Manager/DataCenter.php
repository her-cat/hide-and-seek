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
        $keys = [
            sprintf('%s:player_wait_list', self::CACHE_PREFIX),
            sprintf('%s:online_players', self::CACHE_PREFIX),
        ];

        $keys = array_merge($keys, self::redis()->keys(sprintf('%s:player_id:*', self::CACHE_PREFIX)));
        $keys = array_merge($keys, self::redis()->keys(sprintf('%s:player_fd:*', self::CACHE_PREFIX)));
        $keys = array_merge($keys, self::redis()->keys(sprintf('%s:player_room_id:*', self::CACHE_PREFIX)));

        self::redis()->del($keys);
    }

    public static function getPlayerWaitListLen()
    {
        $key = sprintf('%s:player_wait_list', self::CACHE_PREFIX);

        return self::redis()->scard($key);
    }

    public static function pushPlayerToWaitList(string $playerId)
    {
        $key = sprintf('%s:player_wait_list', self::CACHE_PREFIX);

        return self::redis()->sadd($key, [$playerId]);
    }

    public static function popPlayerFromWaitList()
    {
        $key = sprintf('%s:player_wait_list', self::CACHE_PREFIX);

        return self::redis()->spop($key);
    }

    public static function delPlayerFromWaitList(string $playerId)
    {
        $key = sprintf('%s:player_wait_list', self::CACHE_PREFIX);

        self::redis()->srem($key, $playerId);
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
        self::setOnlinePlayer($playerId);
    }

    public static function delPlayerInfo(int $playerFd)
    {
        $playerId = self::getPlayerId($playerFd);

        self::delPlayerId($playerFd);

        if (is_string($playerId)) {
            self::delPlayerFd($playerId);
            self::delOnlinePlayer($playerId);
            self::delPlayerFromWaitList($playerId);
        }
    }

    public static function setPlayerRoomId(string $playerId, string $roomId)
    {
        $key = sprintf('%s:player_room_id:%s', self::CACHE_PREFIX, $playerId);

        self::redis()->set($key, $roomId);
    }

    public static function getPlayerRoomId(string $playerId)
    {
        $key = sprintf('%s:player_room_id:%s', self::CACHE_PREFIX, $playerId);

        return self::redis()->get($key);
    }

    public static function delPlayerRoomId(string $playerId)
    {
        $key = sprintf('%s:player_room_id:%s', self::CACHE_PREFIX, $playerId);

        self::redis()->del([$key]);
    }

    public static function getOnlinePlayerLen()
    {
        $key = sprintf('%s:online_players', self::CACHE_PREFIX);

        return self::redis()->hlen($key);
    }

    public static function setOnlinePlayer(string $playerId)
    {
        $key = sprintf('%s:online_players', self::CACHE_PREFIX);

        self::redis()->hset($key, $playerId, 1);
    }

    public static function getOnlinePlayer(string $playerId)
    {
        $key = sprintf('%s:online_players', self::CACHE_PREFIX);

        return self::redis()->hget($key, $playerId);
    }

    public static function delOnlinePlayer(string $playerId)
    {
        $key = sprintf('%s:online_players', self::CACHE_PREFIX);

        self::redis()->hdel($key, [$playerId]);
    }
}
