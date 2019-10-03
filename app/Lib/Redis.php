<?php

namespace App\Lib;

class Redis
{
    protected static $config = [
        'host'   => '127.0.0.1',
        'port'   => 6379,
    ];

    public static function getInstance(array $config = [])
    {
        $config = array_merge(self::$config, $config);

        $instance = new \Redis();

        $instance->connect($config['host'], $config['port']);

        if (!empty($config['password'])) {
            $instance->auth($config['password']);
        }

        return $instance;
    }
}
