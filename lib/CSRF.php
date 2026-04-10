<?php
// lib/CSRF.php

class CSRF {
    /**
     * Generate a new CSRF token and store it in session
     */
    public static function generate() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Return hidden input field with CSRF token
     */
    public static function input() {
        $token = self::generate();
        return '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }

    /**
     * Validate the provided token against session
     */
    public static function validate($token) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden');
            die("CSRF validation failed. Request denied.");
        }
        return true;
    }
}
