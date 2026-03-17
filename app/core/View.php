<?php

declare(strict_types=1);

namespace App\Core;

class View
{
    public function render(string $view, array $data = [], string $layout = 'layouts/app'): void
    {
        $viewFile = \root_path('views/' . $view . '.php');
        $layoutFile = \root_path('views/' . $layout . '.php');

        if (!file_exists($viewFile) || !file_exists($layoutFile)) {
            http_response_code(500);
            echo 'Vista no encontrada.';
            return;
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        require $layoutFile;
    }
}
