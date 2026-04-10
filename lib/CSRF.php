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

    /**
     * Validate token from JSON header (X-CSRF-Token) or POST payload.
     */
    public static function validateRequest($jsonResponse = false) {
        $token = '';
        if (isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
            $token = (string) $_SERVER['HTTP_X_CSRF_TOKEN'];
        } elseif (isset($_POST['csrf_token'])) {
            $token = (string) $_POST['csrf_token'];
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $valid = isset($_SESSION['csrf_token']) && $token !== '' && hash_equals($_SESSION['csrf_token'], $token);
        if ($valid) {
            return true;
        }

        if ($jsonResponse) {
            if (!headers_sent()) {
                http_response_code(403);
                header('Content-Type: application/json');
            }
            echo json_encode(array('success' => false, 'message' => 'CSRF validation failed.'));
            exit;
        }

        header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden');
        die("CSRF validation failed. Request denied.");
    }
}
