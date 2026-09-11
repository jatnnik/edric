# 🌌 Edric

A simple PHP router for small projects.

> [!IMPORTANT]
> This package is still a work in progress! Feel free to open issues.

## Quickstart

```sh
composer require jatnnik/edric
```

## Usage

```php
$router = new Router();

Route::use($router);

Route::get('/', fn () => $response);
Route::get('/users/{id}', [UserController::class, 'show']);

$response = $router->handle($request);
```

Happy hacking :)
