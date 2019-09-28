<?php

namespace App\Manager;

class ExceptionHandler
{
    public function handleError(int $level, string $message, string $file = '', int $line = 0)
    {
        if (error_reporting() & $level) {
            switch($level) {
                case E_ERROR:
                    Log::error(sprintf('[ID %s] %s (Line: %s of %s', $level, $message, $line, $file));
                    break;
                case E_WARNING:
                    Log::waring(sprintf('[ID %s] %s (Line: %s of %s', $level, $message, $line, $file));
                    break;
                default:
                    Log::notice(sprintf('[ID %s] %s (Line: %ss of %s', $level, $message, $line, $file));
                    break;
            }
        }
    }

    public function handleException(\Throwable $e)
    {
        Log::error(sprintf('%s (Line: %s of %s', $e->getMessage(), $e->getLine(), $e->getFile()));
    }

    public function handleShutdown()
    {
        if (!is_null($error = error_get_last()) && $this->isFatal($error['type'])) {
            Log::error(sprintf('%s (Line: %s of %s', $error['message'], $error['line'], $error['file']));
        }
    }

    public function isFatal(int $type)
    {
        return in_array($type, [E_COMPILE_ERROR, E_CORE_ERROR, E_ERROR, E_PARSE]);
    }
}
