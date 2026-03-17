<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Core\Request;

class AdminMiddleware
{
    public function handle(Request $request): void
    {
        if (!is_admin()) {
            flash('error', 'No tienes permisos para acceder a esta seccion.');
            header('Location: ' . url('/dashboard'));
            exit;
        }
    }
}
