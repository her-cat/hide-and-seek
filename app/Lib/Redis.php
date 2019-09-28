<?php

namespace App\Lib;

use Predis\Client;

class Redis
{
    /**
     * @var Client
     */
    protected static $instance = null;

    protected static $config = [
        'scheme' => 'tcp',
        'host'   => '0.0.0.0',
        'port'   => 6379,
    ];

    public static function getInstance(array $config = [])
    {
        if (is_null(self::$instance)) {
            self::$instance = new Client(array_merge(self::$config, $config));

            if (!empty($config['password'])) {
                self::$instance->auth($config['password']);
            }
        }

        return self::$instance;
    }
}
