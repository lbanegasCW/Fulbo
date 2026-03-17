<?php

declare(strict_types=1);

namespace App\Core;

class Response
{
    public function redirect(string $path): void
    {
        if (!preg_match('#^https?://#i', $path)) {
            $path = url($path);
        }

        header('Location: ' . $path);
        exit;
    }

    public function json(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
