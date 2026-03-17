<?php

declare(strict_types=1);

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        $key = config('app')['csrf_key'];
        if (empty($_SESSION[$key])) {
            $_SESSION[$key] = bin2hex(random_bytes(32));
        }

        return $_SESSION[$key];
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        $token = csrf_token();
        return '<input type="hidden" name="_csrf" value="' . e($token) . '">';
    }
}

if (!function_exists('verify_csrf')) {
    function verify_csrf(?string $token): bool
    {
        $key = config('app')['csrf_key'];
        $sessionToken = $_SESSION[$key] ?? '';
        return is_string($token) && hash_equals($sessionToken, $token);
    }
}
