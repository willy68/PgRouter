<?php

declare(strict_types=1);

namespace PgRouter;

use Mezzio\Router\Middleware\Stack\MiddlewareStackInterface;
use PgRouter\RouteGroup;
use PgRouter\Middlewares\CallableMiddleware;
use PgRouter\Middlewares\Stack\MiddlewareAwareStackTrait;

class Route extends \Mezzio\Router\Route implements RouteInterface, MiddlewareStackInterface
{
    use MiddlewareAwareStackTrait;

    protected $callback;

    protected $group;

    public function __construct(
        string $path,
        $callback,
        ?string $name = null,
        ?array $method = self::HTTP_METHOD_ANY
    ) {
        $this->callback = $callback;
        $middleware = new CallableMiddleware($callback);
        parent::__construct($path, $middleware, $method, $name);
    }

    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * Get the parent group
     */
    public function getParentGroup(): ?RouteGroup
    {
        return $this->group;
    }

    /**
     * Set the parent group
     *
     * @return Route
     */
    public function setParentGroup(RouteGroup $group): self
    {
        $prefix      = $group->getPrefix();
        $path        = $this->getPath();

        if (strcmp($prefix, substr($path, 0, strlen($prefix))) === 0) {
            $this->group = $group;
        }

        return $this;
    }
}
