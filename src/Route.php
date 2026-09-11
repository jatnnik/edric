<?php

declare(strict_types=1);

namespace Jatnnik\Edric;

final class Route
{
    private static ?Router $router = null;

    public static function use(Router $router): void
    {
        self::$router = $router;
    }

    public static function get(string $path, mixed $handler): Router
    {
        return self::router()->get($path, $handler);
    }

    public static function post(string $path, mixed $handler): Router
    {
        return self::router()->post($path, $handler);
    }

    public static function put(string $path, mixed $handler): Router
    {
        return self::router()->put($path, $handler);
    }

    public static function patch(string $path, mixed $handler): Router
    {
        return self::router()->patch($path, $handler);
    }

    public static function delete(string $path, mixed $handler): Router
    {
        return self::router()->delete($path, $handler);
    }

    private static function router(): Router
    {
        return self::$router ?? throw new \LogicException('No router configured. Call Route::use($router) first.');
    }
}
