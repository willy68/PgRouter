<?php

/**
 * https://github.com/thephpleague/route
 */

declare(strict_types=1);

namespace PgRouter\Middlewares\Stack;

use PgRouter\Route;
use PgRouter\RouteGroup;
use PgRouter\Router;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Server\MiddlewareInterface;
use Mezzio\Router\Middleware\RoutePrefixMiddleware;

use function is_string;
use function array_shift;
use function array_unshift;

trait MiddlewareAwareStackTrait
{
    /** @var array */
    protected array $middlewares = [];

    /**
     * Add middleware
     *
     * @param string|MiddlewareInterface $middleware
     * @return MiddlewareAwareStackTrait|Route|RouteGroup|Router
     */
    public function middleware(string|MiddlewareInterface $middleware): self
    {
        $this->middlewares[] = $middleware;
        return $this;
    }

    /**
     * Add middlewares array
     *
     * @param string[]|MiddlewareInterface[] $middlewares
     */
    public function middlewares(array $middlewares): self
    {
        foreach ($middlewares as $middleware) {
            $this->middleware($middleware);
        }
        return $this;
    }

    /**
     * Add middleware in first
     *
     * @param string|MiddlewareInterface $middleware
     * @return MiddlewareAwareStackTrait|Route|RouteGroup|Router
     */
    public function prependMiddleware(string|MiddlewareInterface $middleware): self
    {
        array_unshift($this->middlewares, $middleware);
        return $this;
    }

    public function routePrefix(ContainerInterface $c, string $routePrefix, string $middleware): self
    {
        $middleware = new RoutePrefixMiddleware($c, $routePrefix, $middleware);
        $this->middlewares[] = $middleware;
        return $this;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function shiftMiddleware(ContainerInterface $c): ?MiddlewareInterface
    {
        $middleware = array_shift($this->middlewares);
        if ($middleware === null) {
            return null;
        }

        if (is_string($middleware)) {
            $middleware = $c->get($middleware);
        }

        if (!$middleware instanceof MiddlewareInterface) {
            return null;
        }

        return $middleware;
    }

    /**
     * get middleware stack
     *
     * @return string[]|MiddlewareInterface[]
     */
    public function getMiddlewareStack(): array
    {
        return $this->middlewares;
    }
}
