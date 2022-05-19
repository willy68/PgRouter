<?php

/**
 * https://github.com/thephpleague/route
 */

declare(strict_types=1);

namespace PgRouter;

interface RouteCollectionInterface
{
    /**
     * Add a route object to the collection.
     */
    public function addRoute(Route $route): Route;

    /**
     * Add a route to the collection.
     *
     * Accepts a combination of a path and callback,
     * and optionally the HTTP methods allowed and name.
     */
    public function route(string $uri, $callback, ?string $name = null, ?array $methods = null);

    /**
     * Add a route that responds to GET HTTP method
     *
     * @param string|callable $callable
     */
    public function get(string $uri, $callable, ?string $name = null): Route;

    /**
     * Add a route that responds to POST HTTP method
     *
     * @param string|callable $callable
     */
    public function post(string $uri, $callable, ?string $name = null): Route;

    /**
     * Add a route that responds to PUT HTTP method
     *
     * @param string|callable $callable
     */
    public function put(string $uri, $callable, ?string $name = null): Route;

    /**
     * Add a route that responds to PATCH HTTP method
     *
     * @param string|callable $callable
     */
    public function patch(string $uri, $callable, ?string $name = null): Route;

    /**
     * Add a route that responds to DELETE HTTP method
     *
     * @param string|callable $callable
     */
    public function delete(string $uri, $callable, ?string $name = null): Route;

    /**
     * @param string|callable $callback
     * @param null|string $name The name of the route.
     */
    public function any(string $path, $callback, ?string $name = null): Route;

    /**
     * Add a route that responds to HEAD HTTP method
     *
     * @param string|callable $callable
     */
    public function head(string $uri, $callable, ?string $name = null): Route;

    /**
     * Add a route that responds to OPTIONS HTTP method
     *
     * @param string|callable $callable
     */
    public function options(string $uri, $callable, ?string $name = null): Route;

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
    public function group(string $prefix, callable $callable): RouteGroup;
}
