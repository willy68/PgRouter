<?php

/**
 * @see https://github.com/mezzio/mezzio-router for the canonical source repository
 */

declare(strict_types=1);

namespace PgRouter;

use PgRouter\RouteGroup;
use Mezzio\Router\RouterInterface;
use Mezzio\Router\DuplicateRouteDetector;
use Mezzio\Router\Exception\DuplicateRouteException;

/**
 * Aggregate routes for the router.
 *
 * This class provides * methods for creating path+HTTP method-based routes and
 * injecting them into the router:
 *
 * - get
 * - post
 * - put
 * - patch
 * - delete
 * - head
 * - options
 * - any
 *
 * A general `route()` method allows specifying multiple request methods and/or
 * arbitrary request methods when creating a path-based route.
 *
 * A general `addRoute()` method with created Route add route on the router.
 *
 * Internally, the class performs some checks for duplicate routes when
 * attaching via one of the exposed methods, and will raise an exception when a
 * collision occurs.
 */
class RouteCollector implements RouteCollectionInterface
{
    use RouteCollectionTrait;

    /** @var RouterInterface */
    protected $router;

    /** @var bool */
    protected $detectDuplicates = true;

    /**
     * List of all routes registered directly with the application.
     *
     * @var Route[]
     */
    private $routes = [];

    /** @var null|DuplicateRouteDetector */
    private $duplicateRouteDetector;

    public function __construct(RouterInterface $router, bool $detectDuplicates = true)
    {
        $this->router           = $router;
        $this->detectDuplicates = $detectDuplicates;
    }

    /**
     * Add a route to the collection.
     *
     * @throws DuplicateRouteException If specification represents an existing route.
     */
    public function addRoute(Route $route): Route
    {
        $this->detectDuplicate($route);
        $this->router->addRoute($route);
        $this->routes[$route->getName()] = $route;
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
        $methods = $methods ?? Route::HTTP_METHOD_ANY;
        $route   = new Route($uri, $callback, $name, $methods);
        return $this->addRoute($route);
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
     * @param string|callable $callable
     */
    public function crud(string $prefixPath, $callable, string $prefixName): RouteGroup
    {
        return $this->group(
            $prefixPath,
            function (RouteGroup $route) use ($callable, $prefixName) {
                $route->crud($callable, $prefixName);
            }
        );
    }

    /**
     * Retrieve all directly registered routes with the application.
     *
     * @return Route[]
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * Retrieve Route by name
     */
    public function getRouteName(string $name): ?Route
    {
        return $this->routes[$name] ?? null;
    }

    private function detectDuplicate(Route $route): void
    {
        if ($this->detectDuplicates && ! $this->duplicateRouteDetector) {
            $this->duplicateRouteDetector = new DuplicateRouteDetector();
        }

        if ($this->duplicateRouteDetector) {
            $this->duplicateRouteDetector->detectDuplicate($route);
            return;
        }
    }

    /**
     * @internal This should only be used in unit tests.
     */
    public function willDetectDuplicates(): bool
    {
        return $this->detectDuplicates;
    }
}
