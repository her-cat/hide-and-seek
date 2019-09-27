<?php

namespace App\Manager;

/**
 * Class Log.
 *
 * @method static info(string $content, array $context = [])
 * @method static debug(string $content, array $context = [])
 * @method static notice(string $content, array $context = [])
 * @method static error(string $content, array $context = [])
 * @method static waring(string $content, array $context = [])
 */
class Log
{
    const INFO = 'INFO';
    const DEBUG = 'DEBUG';
    const NOTICE = 'NOTICE';
    const ERROR = 'ERROR';
    const WARING = 'WARING';

    protected static $levels = [
        self::INFO, self::DEBUG, self::ERROR, self::WARING, self::NOTICE,
    ];

    public static function log(string $content, array $context = [], string $level = 'INFO')
    {
        if (!in_array($level, self::getLevels(), true)) {
            self::error(sprintf('Invalid error level:"%s"', $level), $context);
            return;
        }

        $context = json_encode($context, JSON_UNESCAPED_UNICODE, JSON_UNESCAPED_SLASHES);

        echo sprintf("[%s][%s]: %s %s\n", date('Y-m-d H:i:s'), $level, $content, $context);
    }

    public static function __callStatic(string $name, array $arguments)
    {
        count($arguments) === 1 && array_push($arguments, []);

        array_push($arguments, strtoupper($name));

        self::log(...$arguments);
    }

    public static function getLevels()
    {
        return self::$levels;
    }
}
