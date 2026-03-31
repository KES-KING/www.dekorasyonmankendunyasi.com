<?php
declare(strict_types=1);

final class Router
{
    private array $routes = [];
    private array $dynamicRoutes = [];

    public function get(string $path, callable $handler): void
    {
        $this->map('GET', $path, $handler);
    }

    public function map(string $method, string $path, callable $handler): void
    {
        $normalizedPath = $this->normalizePath($path);
        $httpMethod = strtoupper($method);

        if (str_contains($normalizedPath, '{') && str_contains($normalizedPath, '}')) {
            [$regex, $paramNames] = $this->compileDynamicPath($normalizedPath);
            $this->dynamicRoutes[$httpMethod][] = [
                'path' => $normalizedPath,
                'regex' => $regex,
                'params' => $paramNames,
                'handler' => $handler,
            ];

            return;
        }

        $this->routes[$httpMethod][$normalizedPath] = $handler;
    }

    public function dispatch(string $method, string $path): ?array
    {
        $normalizedPath = $this->normalizePath($path);
        $httpMethod = strtoupper($method);
        $handler = $this->routes[$httpMethod][$normalizedPath] ?? null;

        if ($handler === null && $httpMethod === 'HEAD') {
            $handler = $this->routes['GET'][$normalizedPath] ?? null;
        }

        if ($handler !== null) {
            $result = $this->invokeHandler($handler, []);

            return is_array($result) ? $result : [];
        }

        $dynamicMatch = $this->matchDynamicRoute($httpMethod, $normalizedPath);

        if ($dynamicMatch === null && $httpMethod === 'HEAD') {
            $dynamicMatch = $this->matchDynamicRoute('GET', $normalizedPath);
        }

        if ($dynamicMatch === null) {
            return null;
        }

        $result = $this->invokeHandler($dynamicMatch['handler'], $dynamicMatch['params']);

        return is_array($result) ? $result : [];
    }

    private function matchDynamicRoute(string $method, string $path): ?array
    {
        $routes = $this->dynamicRoutes[$method] ?? [];

        foreach ($routes as $route) {
            if (!preg_match($route['regex'], $path, $matches)) {
                continue;
            }

            $params = [];
            foreach ($route['params'] as $name) {
                if (!array_key_exists($name, $matches)) {
                    continue;
                }

                $params[$name] = (string) $matches[$name];
            }

            return [
                'handler' => $route['handler'],
                'params' => $params,
            ];
        }

        return null;
    }

    private function compileDynamicPath(string $path): array
    {
        $paramNames = [];
        $segments = array_values(array_filter(explode('/', trim($path, '/')), static fn (string $segment): bool => $segment !== ''));
        $patternSegments = [];

        foreach ($segments as $segment) {
            if (preg_match('/^\{([a-zA-Z_][a-zA-Z0-9_]*)\}$/', $segment, $matches) === 1) {
                $paramNames[] = $matches[1];
                $patternSegments[] = '(?P<' . $matches[1] . '>[^/]+)';
                continue;
            }

            $patternSegments[] = preg_quote($segment, '#');
        }

        $pattern = '#^/' . implode('/', $patternSegments) . '$#';

        return [$pattern, $paramNames];
    }

    private function invokeHandler(callable $handler, array $params): mixed
    {
        if ($params === []) {
            return $handler();
        }

        if (is_array($handler) && isset($handler[0], $handler[1])) {
            $reflection = new ReflectionMethod($handler[0], (string) $handler[1]);

            if ($reflection->getNumberOfParameters() < 1) {
                return $handler();
            }

            return $handler($params);
        }

        $reflection = new ReflectionFunction(Closure::fromCallable($handler));

        if ($reflection->getNumberOfParameters() < 1) {
            return $handler();
        }

        return $handler($params);
    }

    private function normalizePath(string $path): string
    {
        if ($path === '') {
            return '/';
        }

        $trimmed = '/' . trim($path, '/');

        if ($trimmed === '//') {
            return '/';
        }

        return rtrim($trimmed, '/') ?: '/';
    }
}
