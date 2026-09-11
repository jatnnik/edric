<?php

declare(strict_types=1);

namespace Jatnnik\Edric;

use Jatnnik\Edric\Exceptions\MethodNotAllowedException;
use Jatnnik\Edric\Exceptions\RouteNotFoundException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class Router implements RequestHandlerInterface
{
    /** @var list<RouteDefinition> */
    private array $routes = [];

    public function get(string $path, mixed $handler): self
    {
        return $this->add('GET', $path, $handler);
    }

    public function post(string $path, mixed $handler): self
    {
        return $this->add('POST', $path, $handler);
    }

    public function put(string $path, mixed $handler): self
    {
        return $this->add('PUT', $path, $handler);
    }

    public function patch(string $path, mixed $handler): self
    {
        return $this->add('PATCH', $path, $handler);
    }

    public function delete(string $path, mixed $handler): self
    {
        return $this->add('DELETE', $path, $handler);
    }

    public function add(
        string $method,
        string $path,
        mixed $handler,
    ): self {
        $this->routes[] = new RouteDefinition(
            mb_strtoupper($method),
            $this->normalizePath($path),
            $handler,
        );

        return $this;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $path = $this->normalizePath(
            $request->getUri()->getPath(),
        );

        $method = mb_strtoupper($request->getMethod());

        $pathMatched = false;

        foreach ($this->routes as $route) {
            $params = $this->match($route->path, $path);

            if ($params === null) {
                continue;
            }

            $pathMatched = true;

            if ($route->method !== $method) {
                continue;
            }

            foreach ($params as $name => $value) {
                $request = $request->withAttribute($name, $value);
            }

            return $this->dispatch(
                $route->handler,
                $request,
            );
        }

        if ($pathMatched) {
            throw new MethodNotAllowedException(
                sprintf(
                    'Method %s is not allowed for %s.',
                    $method,
                    $path,
                ),
            );
        }

        throw new RouteNotFoundException(
            sprintf(
                'No route found for %s %s.',
                $method,
                $path,
            ),
        );
    }

    /**
     * @return array<string, string>|null
     */
    private function match(
        string $routePath,
        string $requestPath,
    ): ?array {
        $pattern = preg_replace_callback(
            '/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/',
            fn(array $match): string => '(?P<' . $match[1] . '>[^/]+)',
            $routePath,
        );

        if ($pattern === null) {
            throw new \RuntimeException(
                'Could not compile route pattern.',
            );
        }

        if (!preg_match(
            '#^' . $pattern . '$#',
            $requestPath,
            $matches,
        )) {
            return null;
        }

        $params = [];

        foreach ($matches as $name => $value) {
            if (is_string($name)) {
                $params[$name] = $value;
            }
        }

        return $params;
    }

    private function dispatch(
        mixed $handler,
        ServerRequestInterface $request,
    ): ResponseInterface {
        if ($handler instanceof \Closure) {
            return $handler($request);
        }

        if (is_callable($handler)) {
            return $handler($request);
        }

        if (
            is_array($handler)
            && count($handler) === 2
            && is_string($handler[0])
            && is_string($handler[1])
        ) {
            [$class, $method] = $handler;

            $controller = new $class();

            return $controller->{$method}($request);
        }

        if (is_string($handler) && class_exists($handler)) {
            $controller = new $handler();

            if (is_callable($controller)) {
                return $controller($request);
            }
        }

        throw new \RuntimeException(
            'Route handler is not callable.',
        );
    }

    private function normalizePath(string $path): string
    {
        $path = '/' . mb_trim($path, '/');

        return $path === '/' ? '/' : mb_rtrim($path, '/') ;
    }
}
