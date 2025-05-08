<?php

namespace App\Core;

class ErrorResponse
{
    public static function send($message, $code = 400, $errors = null)
    {
        $response = [
            'success' => false,
            'message' => $message,
            'code' => $code
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    public static function notFound($message = 'Resource not found')
    {
        self::send($message, 404);
    }

    public static function unauthorized($message = 'Unauthorized access')
    {
        self::send($message, 401);
    }

    public static function forbidden($message = 'Access forbidden')
    {
        self::send($message, 403);
    }

    public static function validationError($errors)
    {
        self::send('Validation failed', 422, $errors);
    }

    public static function serverError($message = 'Internal server error')
    {
        self::send($message, 500);
    }

    public static function badRequest($message = 'Bad request')
    {
        self::send($message, 400);
    }

    public static function tooManyRequests($message = 'Too many requests')
    {
        self::send($message, 429);
    }

    public static function serviceUnavailable($message = 'Service temporarily unavailable')
    {
        self::send($message, 503);
    }
} 