<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Core\Request;

class AuthMiddleware
{
    public function handle(Request $request): void
    {
        if (!is_authenticated()) {
            header('Location: ' . url('/login'));
            exit;
        }

        $user = auth_user();
        if (!$user || (int) $user['activo'] !== 1) {
            session_destroy();
            flash('error', 'Tu cuenta no esta habilitada.');
            header('Location: ' . url('/login'));
            exit;
        }
    }
}
