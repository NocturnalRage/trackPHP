<?php

namespace TrackPHP\Router;

class Router {

  private array $routes = [
    'GET' => [],
    'POST' => []
  ];
  private array $namedRoutes = [];

    public function get(string $pattern, string $handler, ?string $name = null): void
    {
        $this->addRoute('GET', $pattern, $handler, $name);
    }

    public function post(string $pattern, string $handler, ?string $name = null): void
    {
        $this->addRoute('POST', $pattern, $handler, $name);
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

    public function path(string $namedRoute, ?array $values = null): string
    {
        if (!isset($this->namedRoutes[$namedRoute])) {
            throw new \InvalidArgumentException("No route found with name: $namedRoute");
        }
        $route = $this->namedRoutes[$namedRoute];
        $paramNames = $this->extractParamNames($route->pattern);
        $path = $route->pattern;
        foreach ($paramNames as $paramName) {
            if (!array_key_exists($paramName, $values)) {
                throw new \InvalidArgumentException("Missing parameter '$paramName' for route '$namedRoute'");
            }
            $path = str_replace('{' . $paramName . '}', $values[$paramName], $path);
        }
        return $path;
    }

    private function addRoute(string $method, string $pattern, string $handler, ?string $name = null): void
    {
        $paramNames = $this->extractParamNames($pattern);
        if (count($paramNames) !== count(array_unique($paramNames))) {
            // find which names are duplicated
            $dupes = array_diff_assoc($paramNames, array_unique($paramNames));
            $list  = implode(', ', array_unique($dupes));
            throw new \InvalidArgumentException(
                "Duplicate route parameters not allowed: {$list}"
            );
        }

        if (!str_contains($handler, '#')) {
            throw new \InvalidArgumentException("Handler must be in 'controller#action' format");
        }

        [$controllerName, $action] = explode('#', $handler, 2);
        $controller = ucfirst($controllerName) . 'Controller';
        $regexPattern = $this->compilePattern($pattern);

        $route = new Route(
            $method,
            $pattern,
            $regexPattern,
            $controller,
            $action,
            []
        );
        $this->routes[$method][] = $route;
        if ($name === null) {
            $base = preg_replace('/Controller$/', '', $route->controller);
            $name = lcfirst($base . '.' . $route->action);
        }
        $this->namedRoutes[$name] = $route;
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
