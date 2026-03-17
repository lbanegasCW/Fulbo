<?php

declare(strict_types=1);

use App\Repositories\UserRepository;

if (!function_exists('auth_user')) {
    function auth_user(): ?array
    {
        $userId = session_get('user_id');
        if (!$userId) {
            return null;
        }

        $repo = new UserRepository();
        return $repo->findById((int) $userId);
    }
}

if (!function_exists('is_authenticated')) {
    function is_authenticated(): bool
    {
        return auth_user() !== null;
    }
}

if (!function_exists('is_admin')) {
    function is_admin(): bool
    {
        $user = auth_user();
        return $user !== null && $user['role_slug'] === 'admin';
    }
}
