<?php

declare(strict_types=1);

namespace Jatnnik\Edric;

final readonly class RouteDefinition
{
    public function __construct(
        public string $method,
        public string $path,
        public mixed $handler,
    ) {}
}
