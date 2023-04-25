<?php

declare(strict_types=1);

namespace PgRouter;

use Mezzio\Router\Route as MezzioRoute;
use PgRouter\Middlewares\Stack\MiddlewareAwareStackTrait;

class Route implements RouteInterface
{
    use MiddlewareAwareStackTrait;

    private string $path;
    private string $name;
    private ?array $methods;
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
        if ($name === null) {
            $name = ($methods === null) ? $path : $path . '^' . join(':', $methods);
        }
        $this->path = $path;
        $this->name = $name;
        $this->methods = $methods;
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

    public function getPath(): string
    {
        return $this->path;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): RouteInterface
    {
        $this->name = $name;
        return $this;
    }

    public function getAllowedMethods(): ?array
    {
        return $this->methods;
    }
    /**
     * Indicate whether the specified method is allowed by the route.
     *
     * @param string $method HTTP method to test.
     */
    public function allowsMethod(string $method): bool
    {
        $method = strtoupper($method);
        return $this->allowsAnyMethod() || in_array($method, $this->methods ?? [], true);
    }

    /**
     * Indicate whether any method is allowed by the route.
     */
    public function allowsAnyMethod(): bool
    {
        return $this->methods === MezzioRoute::HTTP_METHOD_ANY;
    }

    public function setOptions(array $options): self
    {
        $this->options = $options;
        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}
