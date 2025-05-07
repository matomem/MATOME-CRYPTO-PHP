<?php
class Utils {
    public static function redirect($url) {
        header('Location: ' . $url);
        exit;
    }
    public static function sanitize($data) {
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }
} 