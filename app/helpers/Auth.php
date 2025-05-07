<?php
class Auth {
    public static function login($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
    }
    public static function logout() {
        session_destroy();
    }
    public static function check() {
        return isset($_SESSION['user_id']);
    }
    public static function user() {
        return isset($_SESSION['user_id']) ? [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username']
        ] : null;
    }
} 