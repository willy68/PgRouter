<?php

declare(strict_types=1);

namespace PgRouter;

use PgRouter\Middleware\Stack\MiddlewareStackInterface;
use PgRouter\RouteGroup;
use PgRouter\Middlewares\CallableMiddleware;
use PgRouter\Middlewares\Stack\MiddlewareAwareStackTrait;

class Route extends \Mezzio\Router\Route implements RouteInterface, MiddlewareStackInterface
{
    use MiddlewareAwareStackTrait;

    public const HTTP_SCHEME_ANY = null;

    /** @var ?string */
    protected $host;

    /** @var ?int */
    protected $port;

    /** @var null|string[] Schemes allowed with this route */
    protected $schemes;

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


    public function getHost(): ?string
    {
        return $this->host;
    }

    public function getPort(): ?int
    {
        return $this->port;
    }

    /**
     * get schemes array available for this route
     *
     * @return null|string[] Returns HTTP_SCHEME_ANY or string of allowed schemes.
     */
    public function getSchemes(): ?array
    {
        return $this->schemes;
    }

    /**
     * Indicate whether the specified scheme is allowed by the route.
     *
     * @param string $method HTTP method to test.
     */
    public function allowsScheme(string $scheme): bool
    {
        $schemes = strtolower($scheme);
        return $this->allowsAnyScheme() || in_array($schemes, $this->schemes, true);
    }

    /**
     * Indicate whether any schemes is allowed by the route.
     */
    public function allowsAnyScheme(): bool
    {
        return $this->schemes === self::HTTP_SCHEME_ANY;
    }

    /**
     * set schemes available for this route
     *
     * @return Route
     */
    public function setSchemes(?array $schemes = null): self
    {
        $schemes       = is_array($schemes) ? array_map('strtolower', $schemes) : $schemes;
        $this->schemes = $schemes;
        return $this;
    }

    public function setHost(string $host): self
    {
        $this->host = $host;
        return $this;
    }

    public function setPort(int $port): self
    {
        $this->port = $port;
        return $this;
    }
}
