<?php

declare(strict_types=1);

namespace App\Core;

class Request
{
    public function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public function uri(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH);
        $normalized = rtrim($path ?: '/', '/') ?: '/';

        $base = base_path_url();
        if ($base !== '' && str_starts_with($normalized, $base)) {
            $normalized = substr($normalized, strlen($base));
            $normalized = $normalized === '' ? '/' : $normalized;
        }

        // Compatibilidad con hostings que exponen /index.php o /public/index.php en la URL.
        foreach (['/public/index.php', '/index.php'] as $prefix) {
            if (str_starts_with($normalized, $prefix)) {
                $normalized = substr($normalized, strlen($prefix));
                $normalized = $normalized === '' ? '/' : $normalized;
            }
        }

        $normalized = rtrim($normalized, '/') ?: '/';

        return $normalized;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($_GET, $_POST);
    }

    public function only(array $keys): array
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->input($key);
        }

        return $result;
    }
}
