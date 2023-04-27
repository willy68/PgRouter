<?php

declare(strict_types=1);

namespace PgRouter;

use FastRoute\RouteCollector;
use Mezzio\Router\FastRouteRouter;
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
        ?callable $dispatcherFactory = null,
        ?array $config = null
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
        $this->router->addRoute($route->getRoute());
        $this->routes[$route->getName()] = $route;
    }

    public function match(Request $request): RouteResult
    {
        $result = $this->router->match($request);
        if ($result->isSuccess()) {
            $name = $result->getMatchedRouteName();
            if (array_key_exists($name, $this->routes)) {
                return new RouteResult($result, $this->routes[$name]);
            }
            /** @var CallableMiddleware $middleware */
            $middleware = $result->getMatchedRoute()->getMiddleware();
            $route = (new Route(
                $result->getMatchedRoute()->getPath(),
                $middleware->getCallable(),
                $name,
                $result->getMatchedRoute()->getAllowedMethods()
            ))
                ->setRoute($result->getMatchedRoute());
            $this->routes[$name] = $route;
            return new RouteResult($result, $route);
        }
        return new RouteResult($result);
    }

    public function generateUri(string $name, array $substitutions = [], array $options = []): string
    {
        return $this->router->generateUri($name, $substitutions, $options);
    }

    /**
     * Create multiple routes with same prefix
     *
     * Ex:
     * ```
     * $router->group('/admin', function (RouteGroup $route) {
     *  $route->route('/acme/route1', 'AcmeController::actionOne', 'route1', [GET]);
     *  $route->route('/acme/route2', 'AcmeController::actionTwo', 'route2', [GET])->middleware(Middleware::class);
     *  $route->route('/acme/route3', 'AcmeController::actionThree', 'route3', [GET]);
     * })
     * ->middleware(Middleware::class);
     * ```
     */
    public function group(string $prefix, callable $callable): RouteGroup
    {
        $group = new RouteGroup($prefix, $callable, $this);
        /* run group to inject routes on router*/
        $group();

        return $group;
    }

    /**
     * Generate crud Routes
     *
     * @param string $prefixPath
     * @param callable|string $callable
     * @param string $prefixName
     * @return RouteGroup
     */
    public function crud(string $prefixPath, callable|string $callable, string $prefixName): RouteGroup
    {
        return $this->group(
            $prefixPath,
            function (RouteGroup $route) use ($callable, $prefixName) {
                $route->crud($callable, $prefixName);
            }
        );
    }

    public function getInnerRouter(): \Mezzio\Router\RouterInterface
    {
        return $this->router;
    }
}
