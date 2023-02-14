<?php

declare(strict_types=1);

namespace PgRouter;

use Mezzio\Router\Middleware\Stack\MiddlewareStackInterface;
use Mezzio\Router\RouterInterface;
use PgRouter\Middlewares\Stack\MiddlewareAwareStackTrait;

use function ltrim;
use function implode;
use function sprintf;

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
class RouteGroup implements MiddlewareStackInterface
{
    use MiddlewareAwareStackTrait;
    use RouteCollectionTrait;

    /**
     * Route prefix for this group
     *
     * @var string
     */
    private $prefix;

    /**
     * Called by router
     *
     * @var callable
     */
    private $callable;

    /**
     * Router
     *
     * @var RouteCollectionInterface|RouterInterface
     */
    private $router;
    /**
     * constructor
     *
     * @param RouteCollectionInterface|RouterInterface $router
     */
    public function __construct(string $prefix, callable $callable, $router)
    {
        $this->prefix   = $prefix;
        $this->callable = $callable;
        $this->router   = $router;
    }

    /**
     * Run $callable
     *
     * @return void
     */
    public function __invoke()
    {
        ($this->callable)($this);
    }

    /**
     * Add route
     */
    public function addRoute(Route $route): Route
    {
        $route = $this->router->addRoute($route);
        $route->setParentGroup($this);
        return $route;
    }

    /**
     * Add a route to match.
     *
     * Accepts a combination of a path and callback, and optionally the HTTP methods allowed.
     *
     * @param string|callable $callback
     * @param null|array  $methods HTTP method to accept; null indicates any.
     * @param null|string $name The name of the route.
     * @throws DuplicateRouteException If specification represents an existing route.
     */
    public function route(string $uri, $callback, ?string $name = null, ?array $methods = null): Route
    {
        $uri = $uri === '/' ? $this->prefix : $this->prefix . sprintf('/%s', ltrim($uri, '/'));
        $methods = $methods ?? Route::HTTP_METHOD_ANY;
        if ($name === null) {
            $name = $methods === null ? $this->prefix . $uri : $this->prefix . $uri . '^' . implode(':', $methods);
        }
        $route   = new Route($uri, $callback, $name, $methods);
        return $this->addRoute($route);
    }

    /**
     * Perfom all crud routes for a given class controller
     *
     * @param string|callable $callable the class name generally
     */
    public function crud($callable, string $prefixName): self
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
