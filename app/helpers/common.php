<?php

declare(strict_types=1);

if (!function_exists('root_path')) {
    function root_path(string $path = ''): string
    {
        $root = dirname(__DIR__, 2);
        return $path !== '' ? $root . DIRECTORY_SEPARATOR . $path : $root;
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        $relative = 'assets/' . ltrim($path, '/');
        return versioned_public_url($relative);
    }
}

if (!function_exists('public_file_version')) {
    function public_file_version(string $relativePath): string
    {
        $fullPath = root_path('public/' . ltrim($relativePath, '/'));
        if (file_exists($fullPath)) {
            return (string) filemtime($fullPath);
        }

        return '1';
    }
}

if (!function_exists('versioned_public_url')) {
    function versioned_public_url(string $relativePath): string
    {
        $relativePath = ltrim($relativePath, '/');
        $base = url('/' . $relativePath);
        $version = public_file_version($relativePath);
        $separator = str_contains($base, '?') ? '&' : '?';
        return $base . $separator . 'v=' . $version;
    }
}

if (!function_exists('base_path_url')) {
    function base_path_url(): string
    {
        $app = config('app');
        $path = '';

        if (!empty($app['base_url'])) {
            $path = parse_url($app['base_url'], PHP_URL_PATH) ?: '';
        } else {
            $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
            $scriptDir = rtrim($scriptDir, '/');
            if ($scriptDir !== '' && $scriptDir !== '.') {
                if (str_ends_with($scriptDir, '/public')) {
                    $scriptDir = substr($scriptDir, 0, -7);
                }
                $path = $scriptDir;
            }
        }

        $path = trim((string) $path);
        if ($path === '' || $path === '/') {
            return '';
        }

        return '/' . trim($path, '/');
    }
}

if (!function_exists('url')) {
    function url(string $path = '/'): string
    {
        $base = base_path_url();
        if ($path === '' || $path === '/') {
            return $base !== '' ? $base . '/' : '/';
        }

        return $base . '/' . ltrim($path, '/');
    }
}

if (!function_exists('config')) {
    function config(string $file): array
    {
        static $cache = [];
        if (!isset($cache[$file])) {
            $cache[$file] = require root_path('config/' . $file . '.php');
        }

        return $cache[$file];
    }
}

if (!function_exists('e')) {
    function e(string|null $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('state_label')) {
    function state_label(string $state): string
    {
        $map = [
            'borrador' => 'Borrador',
            'sorteo_pendiente' => 'Sorteo pendiente',
            'eleccion_equipos' => 'Eleccion de equipos',
            'en_juego' => 'En juego',
            'desempate' => 'Desempate',
            'finalizado' => 'Finalizado',
            'pendiente' => 'Pendiente',
            'jugado' => 'Jugado',
            'validado' => 'Validado',
        ];

        if (isset($map[$state])) {
            return $map[$state];
        }

        return ucfirst(str_replace('_', ' ', $state));
    }
}
