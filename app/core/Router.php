<?php
namespace App\Core;

class Router
{
    private array $routes = [];

    public function add(string $method, string $pattern, string $handler): void
    {
        $this->routes[] = compact('method', 'pattern', 'handler');
    }

    public function dispatch(string $method, string $uri): void
    {
        $uri    = rtrim(parse_url($uri, PHP_URL_PATH), '/') ?: '/';
        $method = strtoupper($method);

        // Handle OPTIONS preflight immediately
        if ($method === 'OPTIONS') {
            http_response_code(200);
            header('Allow: GET, POST, OPTIONS');
            header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token, X-Requested-With');
            return;
        }

        $uriMatched = false;

        foreach ($this->routes as $route) {
            $params = $this->match($route['pattern'], $uri);
            if ($params === false) continue;

            $uriMatched = true;

            if (strtoupper($route['method']) !== $method) continue;

            // Rate-limit aggressive scanners: block > 300 requests/min per IP
            [$controllerName, $action] = explode('@', $route['handler']);
            $controllerClass = "App\\Controllers\\{$controllerName}";

            if (!class_exists($controllerClass)) {
                http_response_code(500);
                error_log("Controller not found: $controllerClass");
                echo '<h1>500 — Internal Server Error</h1>';
                return;
            }

            (new $controllerClass())->$action(...array_values($params));
            return;
        }

        if ($uriMatched) {
            http_response_code(405);
            header('Allow: GET, POST');
            echo '<h1>405 — Method Not Allowed</h1>';
            return;
        }

        http_response_code(404);
        $view404 = dirname(__DIR__) . '/views/pages/errors/404.php';
        if (file_exists($view404)) {
            include $view404;
        } else {
            echo '<h1>404 — Page Not Found</h1>';
        }
    }

    private function match(string $pattern, string $uri): array|false
    {
        $regex = preg_replace('/\{([a-zA-Z_]+)\}/', '([^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';
        if (!preg_match($regex, $uri, $matches)) return false;
        array_shift($matches);
        preg_match_all('/\{([a-zA-Z_]+)\}/', $pattern, $keys);
        return array_combine($keys[1], $matches) ?: [];
    }

    public static function load(): Router
    {
        $router = new self();
        $routes = require __DIR__ . '/../config/routes.php';
        foreach ($routes as [$method, $pattern, $handler]) {
            $router->add($method, $pattern, $handler);
        }
        return $router;
    }
}
