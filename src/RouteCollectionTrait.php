<?php

/**
 * https://github.com/thephpleague/route
 */

declare(strict_types=1);

namespace PgRouter;

trait RouteCollectionTrait
{
    /**
     * Add a route to the collection
     */
    abstract public function addRoute(Route $route): Route;

    /**
     * Add a route to the collection
     *
     * @param string|callable $callable
     * @param null|string $name The name of the route.
     * @param array|null $method The HTTP methods.
     */
    abstract public function route(string $uri, $callable, ?string $name = null, ?array $method = null): Route;

    /**
     * Add a route that responds to GET HTTP method
     *
     * @param string|callable $callable
     * @param null|string $name The name of the route.
     */
    public function get(string $uri, $callable, ?string $name = null): Route
    {
        return $this->route($uri, $callable, $name, ['GET']);
    }

    /**
     * Add a route that responds to POST HTTP method
     *
     * @param string|callable $callable
     * @param null|string $name The name of the route.
     */
    public function post(string $uri, $callable, ?string $name = null): Route
    {
        return $this->route($uri, $callable, $name, ['POST']);
    }

    /**
     * Add a route that responds to PUT HTTP method
     *
     * @param string|callable $callable
     * @param null|string $name The name of the route.
     */
    public function put(string $uri, $callable, ?string $name = null): Route
    {
        return $this->route($uri, $callable, $name, ['PUT']);
    }

    /**
     * Add a route that responds to PATCH HTTP method
     *
     * @param string|callable $callable
     * @param null|string $name The name of the route.
     */
    public function patch(string $uri, $callable, ?string $name = null): Route
    {
        return $this->route($uri, $callable, $name, ['PATCH']);
    }

    /**
     * Add a route that responds to DELETE HTTP method
     *
     * @param string|callable $callable
     * @param null|string $name The name of the route.
     */
    public function delete(string $uri, $callable, ?string $name = null): Route
    {
        return $this->route($uri, $callable, $name, ['DELETE']);
    }

    /**
     * @param string|callable $callback
     * @param null|string $name The name of the route.
     */
    public function any(string $path, $callback, ?string $name = null): Route
    {
        return $this->route($path, $callback, $name, null);
    }

    /**
     * Add a route that responds to HEAD HTTP method
     *
     * @param callable|string $callable
     * @param null|string $name The name of the route.
     */
    public function head(string $uri, $callable, ?string $name = null): Route
    {
        return $this->route($uri, $callable, $name, ['HEAD']);
    }

    /**
     * Add a route that responds to OPTIONS HTTP method
     *
     * @param string|callable $callable
     * @param null|string $name The name of the route.
     */
    public function options(string $uri, $callable, ?string $name = null): Route
    {
        return $this->route($uri, $callable, $name, ['OPTIONS']);
    }
}
