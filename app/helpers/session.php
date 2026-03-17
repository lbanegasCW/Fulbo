<?php

declare(strict_types=1);

if (!function_exists('start_secure_session')) {
    function start_secure_session(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $appConfig = config('app');
        session_name($appConfig['session_name']);
        session_set_cookie_params([
            'httponly' => true,
            'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
            'samesite' => 'Lax',
            'path' => '/',
        ]);

        session_start();
    }
}

if (!function_exists('session_set')) {
    function session_set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }
}

if (!function_exists('session_get')) {
    function session_get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }
}

if (!function_exists('flash')) {
    function flash(string $type, string $message): void
    {
        $_SESSION['_flash'][] = ['type' => $type, 'message' => $message];
    }
}

if (!function_exists('get_flashes')) {
    function get_flashes(): array
    {
        $messages = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        return $messages;
    }
}
