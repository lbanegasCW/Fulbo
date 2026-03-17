<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/helpers/common.php';
require_once root_path('app/helpers/env.php');

load_env(root_path('.env'));

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $parts = explode('\\', $relative);
    $parts[0] = strtolower($parts[0]);
    $path = root_path('app/' . implode(DIRECTORY_SEPARATOR, $parts) . '.php');
    if (file_exists($path)) {
        require_once $path;
    }
});

require_once root_path('app/helpers/session.php');
require_once root_path('app/helpers/csrf.php');
require_once root_path('app/helpers/auth.php');

date_default_timezone_set(config('app')['timezone']);
start_secure_session();

use App\Core\Request;
use App\Core\Router;

$router = new Router();
require root_path('routes/web.php');

$request = new Request();
$router->dispatch($request);
