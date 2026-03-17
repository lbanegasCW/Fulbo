<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Core\View;

abstract class BaseController
{
    protected View $view;
    protected Response $response;

    public function __construct()
    {
        $this->view = new View();
        $this->response = new Response();
    }
}
