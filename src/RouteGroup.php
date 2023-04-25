<?php

declare(strict_types=1);

namespace PgRouter;

use PgRouter\Middlewares\Stack\MiddlewareAwareStackTrait;

/**
 * Ex:
 * ```
 * $router->group('/admin', function (RouteGroup $route) {
 * $route->route('/acme/route1', 'AcmeController::actionOne', 'route1', [GET]);
 * $route->route('/acme/route2', 'AcmeController::actionTwo', 'route2', [GET])->setScheme('https');
 * $route->route('/acme/route3', 'AcmeController::actionThree', 'route3', [GET]);
 * })
 * ->middleware(Middleware::class);
 * ```
 */
class RouteGroup
{
    use MiddlewareAwareStackTrait;
    use RouteCollectionTrait;

    private string $prefix;

    /**
     * @var callable
     */
    private $callable;

    private RouteCollectionInterface $router;

    /**
     * constructor
     *
     * @param string $prefix
     * @param callable $callable
     * @param RouteCollectionInterface $router
     */
    public function __construct(string $prefix, callable $callable, RouteCollectionInterface $router)
    {
        $this->prefix   = $prefix;
        $this->callable = $callable;
        $this->router   = $router;
    }

    /**
     * Run $callable
     */
    public function __invoke(): void
    {
        ($this->callable)($this);
    }

    /**
     * Add a route to match.
     *
     * Accepts a combination of a path and callback, and optionally the HTTP methods allowed.
     *
     * @param string $path
     * @param callable|string $callback
     * @param null|string $name The name of the route.
     * @param null|array $methods HTTP method to accept; null indicates any.
     * @return Route
     */
    public function route(string $path, callable|string $callback, ?string $name = null, ?array $methods = null): Route
    {
        $path = $path === '/' ? $this->prefix : $this->prefix . sprintf('/%s', ltrim($path, '/'));
        $route   = new Route($path, $callback, $name, $methods);
        return $this->router->route($path, $callback, $name, $methods);
    }

    /**
     * Perform all crud routes for a given class controller
     *
     * @param callable|string $callable The class name generally
     */
    public function crud(callable|string $callable, string $prefixName): self
    {
        $this->get("/", $callable . '::index', "$prefixName.index");
        $this->get("/new", $callable . '::create', "$prefixName.create");
        $this->post("/new", $callable . '::create', "$prefixName.create.post");
        $this->get("/{id:\d+}", $callable . '::edit', "$prefixName.edit");
        $this->post("/{id:\d+}", $callable . '::edit', "$prefixName.edit.post");
        $this->delete("/{id:\d+}", $callable . '::delete', "$prefixName.delete");
        return $this;
    }

    /**
     * Get the value of prefix
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }
}
