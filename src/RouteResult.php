<?php

declare(strict_types=1);

namespace PgRouter;

class RouteResult
{
    protected \Mezzio\Router\RouteResult $routeResult;

    public function __construct(\Mezzio\Router\RouteResult $routeResult)
    {
        $this->routeResult = $routeResult;
    }


    /**
     * Does the result represent successful routing?
     */
    public function isSuccess(): bool
    {
        return $this->routeResult->isSuccess();
    }

    /**
     * Retrieve the route that resulted in the route match.
     *
     * @return false|null|Route false if representing a routing failure;
     *     null if not created via fromRoute(); Route instance otherwise.
     */
    public function getMatchedRoute(): bool|Route|null
    {
        return $this->routeResult->getMatchedRoute();
    }

    /**
     * Retrieve the matched route name, if possible.
     *
     * If this result represents a failure, return false; otherwise, return the
     * matched route name.
     *
     * @return false|string
     */
    public function getMatchedRouteName(): bool|string
    {
        return $this->routeResult->getMatchedRouteName();
    }

    /**
     * Returns the matched params.
     *
     * Guaranted to return an array, even if it is simply empty.
     */
    public function getMatchedParams(): array
    {
        return $this->routeResult->getMatchedParams();
    }

    /**
     * Is this a routing failure result?
     */
    public function isFailure(): bool
    {
        return $this->routeResult->isFailure();
    }

    /**
     * Does the result represent failure to route due to HTTP method?
     */
    public function isMethodFailure(): bool
    {
        return $this->routeResult->isMethodFailure();
    }

    /**
     * Retrieve the allowed methods for the route failure.
     *
     * @return null|string[] HTTP methods allowed
     */
    public function getAllowedMethods(): ?array
    {
        return $this->routeResult->getAllowedMethods();
    }
}
