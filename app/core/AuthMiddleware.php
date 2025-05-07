<?php

namespace App\Core;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthMiddleware
{
    private static $secretKey;

    public static function initialize($secretKey)
    {
        self::$secretKey = $secretKey;
    }

    public static function authenticate()
    {
        $headers = getallheaders();
        $token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;

        if (!$token) {
            self::unauthorized('No token provided');
        }

        try {
            $decoded = JWT::decode($token, new Key(self::$secretKey, 'HS256'));
            return $decoded;
        } catch (\Exception $e) {
            self::unauthorized('Invalid token');
        }
    }

    public static function generateToken($userId, $userRole)
    {
        $payload = [
            'user_id' => $userId,
            'role' => $userRole,
            'iat' => time(),
            'exp' => time() + (60 * 60 * 24) // 24 hours
        ];

        return JWT::encode($payload, self::$secretKey, 'HS256');
    }

    private static function unauthorized($message)
    {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode([
            'error' => true,
            'message' => $message
        ]);
        exit;
    }

    public static function validateRole($requiredRole)
    {
        $user = self::authenticate();
        
        if ($user->role !== $requiredRole) {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode([
                'error' => true,
                'message' => 'Insufficient permissions'
            ]);
            exit;
        }

        return $user;
    }
} 