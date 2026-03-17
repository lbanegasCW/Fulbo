<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Core\Request;

class CsrfMiddleware
{
    public function handle(Request $request): void
    {
        if ($request->method() !== 'POST') {
            return;
        }

        if (!verify_csrf($request->input('_csrf'))) {
            http_response_code(419);
            echo 'Token CSRF invalido.';
            exit;
        }
    }
}
