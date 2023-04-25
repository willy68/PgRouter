<?php

declare(strict_types=1);

namespace PgRouter;

use FastRoute\RouteCollector;
use Mezzio\Router\FastRouteRouter;
use Mezzio\Router\RouteResult;
use PgRouter\Middlewares\CallableMiddleware;
use PgRouter\Middlewares\Stack\MiddlewareAwareStackTrait;
use Psr\Http\Message\ServerRequestInterface as Request;

class Router implements RouterInterface
{
    use MiddlewareAwareStackTrait;

    protected array $routes = [];
    protected FastRouteRouter $router;

    public function __construct(
        ?RouteCollector $router = null,
        ?callable       $dispatcherFactory = null,
        ?array          $config = null
    ) {
        $this->router = new FastRouteRouter($router, $dispatcherFactory, $config);
    }

    public function route(string $path, callable|string $callback, ?string $name = null, ?array $methods = null): Route
    {
        $route = new Route($path, $callback, $name, $methods);
        $this->addRoute($route);
        return $route;
    }

    public function addRoute(Route $route): void
    {
        $mezzioRoute = new \Mezzio\Router\Route(
            $route->getPath(),
            new CallableMiddleware(
                $route->getCallback()
            ),
            $route->getAllowedMethods(),
            $route->getName()
        );
        $this->router->addRoute($mezzioRoute);
        $this->routes[$route->getName()] = $route;
    }

    public function match(Request $request): RouteResult
    {
        return $this->router->match($request);
    }

    public function generateUri(string $name, array $substitutions = [], array $options = []): string
    {
        return $this->router->generateUri($name, $substitutions, $options);
    }
}
