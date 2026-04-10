<?php
// config/config.php

// Absolute paths
define('ROOT_PATH', dirname(__DIR__));
define('LIB_PATH', ROOT_PATH . '/lib');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('COMPONENTS_PATH', ROOT_PATH . '/components');

// Include system essentials
require_once CONFIG_PATH . '/session.php';
require_once CONFIG_PATH . '/database.php';
require_once CONFIG_PATH . '/permissions.php';
require_once CONFIG_PATH . '/notifications.php';
require_once LIB_PATH . '/CSRF.php';
require_once LIB_PATH . '/DebugLogger.php';

// Global App Settings
$app_name = "DFCMS";
$app_version = "1.2.0-Prod";

function env_value($key, $default = null) {
    $value = getenv($key);
    if ($value === false && isset($_ENV[$key])) {
        $value = $_ENV[$key];
    }
    if ($value === false || $value === null || $value === '') {
        return $default;
    }
    return $value;
}

function app_debug() {
    return strtolower((string) env_value('APP_DEBUG', 'false')) === 'true';
}

/**
 * Helper to get base URL (useful for redirects and links)
 */
function base_url($path = '') {
    $root = (string) env_value('APP_BASE_URL', '/dfcms/');
    if ($root === '') {
        $root = '/';
    }
    if (substr($root, -1) !== '/') {
        $root .= '/';
    }
    return $root . ltrim($path, '/');
}

/**
 * Helper to check if user is logged in
 */
function check_login($role = null) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . base_url('auth/login.php'));
        exit;
    }
    if ($role && $_SESSION['role'] !== $role) {
        die("Access Denied: You do not have the required role.");
    }
}
