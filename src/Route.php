<?php

declare(strict_types=1);

namespace PgRouter;

use Mezzio\Router\Route as MezzioRoute;
use PgRouter\Middlewares\CallableMiddleware;
use PgRouter\Middlewares\Stack\MiddlewareAwareStackTrait;

class Route implements RouteInterface
{
    use MiddlewareAwareStackTrait;

    protected MezzioRoute $route;
    public const HTTP_SCHEME_ANY = null;
    protected string $host;
    protected int $port;
    protected ?array $schemes;
    protected array $options;

    /**
     * @var string|array|callable
     */
    protected $callback;

    protected RouteGroup $group;

    public function __construct(
        string $path,
        $callback,
        ?string $name = null,
        ?array $methods = MezzioRoute::HTTP_METHOD_ANY
    ) {
        $this->route = new MezzioRoute($path, new CallableMiddleware($this->callback), $methods, $name);
        $this->callback = $callback;
    }

    public function getCallback(): callable|array|string
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
     * @param RouteGroup $group
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
     * Get schemes array available for this route
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
     * @param string $scheme
     * @return bool
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
     * Set schemes available for this route
     *
     * @param array|null $schemes
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

    public function getPath(): string
    {
        return $this->route->getPath();
    }

    public function getName(): string
    {
        return $this->route->getName();
    }

    /**
     * Set the route name.
     *
     * @param non-empty-string $name
     * @return Route
     */
    public function setName(string $name): self
    {
        $this->route->setName($name);
        return $this;
    }

    public function getAllowedMethods(): ?array
    {
        return $this->route->getAllowedMethods();
    }
    /**
     * Indicate whether the specified method is allowed by the route.
     *
     * @param string $method HTTP method to test.
     */
    public function allowsMethod(string $method): bool
    {
        return $this->route->allowsMethod($method);
    }

    /**
     * Indicate whether any method is allowed by the route.
     */
    public function allowsAnyMethod(): bool
    {
        return $this->route->allowsAnyMethod();
    }

    public function setOptions(array $options): self
    {
        $this->route->setOptions($options);
        return $this;
    }

    public function getOptions(): array
    {
        return $this->route->getOptions();
    }

    /**
     * @return MezzioRoute
     */
    public function getRoute(): MezzioRoute
    {
        return $this->route;
    }

    /**
     * @param MezzioRoute $route
     * @return Route
     */
    public function setRoute(MezzioRoute $route): self
    {
        $this->route = $route;
        return $this;
    }
}
