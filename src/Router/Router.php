<?php

namespace TrackPHP\Router;

class Router {

  private array $routes = [
    'GET' => [],
    'POST' => []
  ];

    public function get(string $pattern, string $handler): void
    {
        $this->addRoute('GET', $pattern, $handler);
    }

    public function post(string $pattern, string $handler): void
    {
        $this->addRoute('POST', $pattern, $handler);
    }

    public function match(string $method, string $uri): ?Route
    {
        foreach ($this->routes[$method] ?? [] as $route) {
            if (preg_match($route->regexPattern, $uri, $matches)) {
                array_shift($matches); // remove full match
                $route->params = array_combine(
                    $this->extractParamNames($route->pattern),
                    $matches
                );
                return $route;
            }
        }
        return null;
    }

    private function addRoute(string $method, string $pattern, string $handler): void
    {
        if (!str_contains($handler, '#')) {
            throw new \InvalidArgumentException("Handler must be in 'controller#action' format");
        }

        [$controllerName, $action] = explode('#', $handler, 2);
        $controller = ucfirst($controllerName) . 'Controller';
        $regexPattern = $this->compilePattern($pattern);

        $this->routes[$method][] = new Route(
            $method,
            $pattern,
            $regexPattern,
            $controller,
            $action,
            []
        );
    }

    private function extractParamNames(string $pattern): array
    {
        preg_match_all('/\{([^\/]+)\}/', $pattern, $matches);
        return $matches[1]; // Returns something like ['postId', 'commentId']
    }

    private function compilePattern(string $pattern): string
    {
        // Escape slashes and convert dynamic segments
        $regexPattern = preg_replace('#\{[^/]+\}#', '([^/]+)', $pattern);
        return '#^' . $regexPattern . '$#';
    }

}
