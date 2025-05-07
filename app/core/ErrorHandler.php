<?php

namespace App\Core;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;

class ErrorHandler
{
    private static $logger;

    public static function initialize()
    {
        self::$logger = new Logger('app');
        self::$logger->pushHandler(new RotatingFileHandler(
            __DIR__ . '/../../storage/logs/app.log',
            30,
            Logger::DEBUG
        ));

        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    public static function handleError($errno, $errstr, $errfile, $errline)
    {
        if (!(error_reporting() & $errno)) {
            return false;
        }

        $error = [
            'type' => $errno,
            'message' => $errstr,
            'file' => $errfile,
            'line' => $errline
        ];

        self::$logger->error('PHP Error: ' . json_encode($error));

        if (ini_get('display_errors')) {
            self::displayError($error);
        }

        return true;
    }

    public static function handleException($exception)
    {
        $error = [
            'type' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ];

        self::$logger->error('Uncaught Exception: ' . json_encode($error));

        if (ini_get('display_errors')) {
            self::displayError($error);
        }
    }

    public static function handleShutdown()
    {
        $error = error_get_last();
        if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR])) {
            self::handleError(
                $error['type'],
                $error['message'],
                $error['file'],
                $error['line']
            );
        }
    }

    private static function displayError($error)
    {
        if (php_sapi_name() === 'cli') {
            echo "Error: {$error['message']}\n";
            echo "File: {$error['file']}\n";
            echo "Line: {$error['line']}\n";
        } else {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode([
                'error' => true,
                'message' => 'An error occurred',
                'details' => $error
            ]);
        }
    }
} 