<?php

declare(strict_types=1);

namespace App\Core;

class Router
{
    private array $routes = [];

    public function add(string $method, string $path, callable|array $handler, array $middlewares = []): void
    {
        $normalized = rtrim($path, '/') ?: '/';
        $this->routes[strtoupper($method)][] = [
            'path' => $normalized,
            'handler' => $handler,
            'middlewares' => $middlewares,
        ];
    }

    public function get(string $path, callable|array $handler, array $middlewares = []): void
    {
        $this->add('GET', $path, $handler, $middlewares);
    }

    public function post(string $path, callable|array $handler, array $middlewares = []): void
    {
        $this->add('POST', $path, $handler, $middlewares);
    }

    public function dispatch(Request $request): void
    {
        $method = $request->method();
        $uri = $request->uri();
        $route = $this->matchRoute($method, $uri);

        if (!$route) {
            http_response_code(404);
            echo 'Ruta no encontrada';
            return;
        }

        foreach ($route['middlewares'] as $middlewareClass) {
            $middleware = new $middlewareClass();
            $middleware->handle($request);
        }

        $handler = $route['handler'];

        if (is_array($handler)) {
            [$controller, $action] = $handler;
            $instance = new $controller();
            $instance->{$action}($request);
            return;
        }

        $handler($request);
    }

    private function matchRoute(string $method, string $uri): ?array
    {
        $routes = $this->routes[$method] ?? [];
        foreach ($routes as $route) {
            $regex = preg_replace('#\{[a-zA-Z_][a-zA-Z0-9_]*\}#', '([\\w-]+)', $route['path']);
            $regex = '#^' . $regex . '$#';
            if (!preg_match($regex, $uri, $matches)) {
                continue;
            }

            preg_match_all('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', $route['path'], $paramNames);
            array_shift($matches);
            foreach ($paramNames[1] as $index => $name) {
                $_GET[$name] = $matches[$index] ?? null;
            }

            return $route;
        }

        return null;
    }
}
