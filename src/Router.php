<?php

declare(strict_types=1);

namespace PgRouter;

use Mezzio\Router\FastRouteRouter;
use PgRouter\Middlewares\Stack\MiddlewareAwareStackTrait;

class Router extends FastRouteRouter
{
    use MiddlewareAwareStackTrait;
}
