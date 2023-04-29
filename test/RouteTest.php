<?php

/**
 * @see       https://github.com/mezzio/mezzio-router for the canonical source repository
 */

declare(strict_types=1);

namespace MezzioTest\Router;

use Fig\Http\Message\RequestMethodInterface as RequestMethod;
use Mezzio\Router\Exception\InvalidArgumentException;
use Mezzio\Router\Route as MezzioRoute;
use PgRouter\Middlewares\CallableMiddleware;
use PgRouter\Route;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * @covers \Mezzio\Router\Route
 */
class RouteTest extends TestCase
{
    use ProphecyTrait;

    /** @var callable */
    private $noopMiddleware;

    /** @var callable */
    private $fakeCallable;

    public function testRoutePathIsRetrievable(): void
    {
        $route = new Route('/foo', $this->fakeCallable);
        $this->assertEquals('/foo', $route->getPath());
    }

    public function testRouteCallableIsRetrievable(): void
    {
        $route = new Route('/foo', $this->fakeCallable);
        $this->assertSame($this->fakeCallable, $route->getCallback());
    }

    public function testRouteMiddlewareIsInstanceOfCallableMiddleware()
    {
        $route = new Route('/foo', $this->fakeCallable);
        $this->assertInstanceOf(CallableMiddleware::class, $route->getRoute()->getMiddleware());
    }

    public function testRouteInstanceAcceptsAllHttpMethodsByDefault(): void
    {
        $route = new Route('/foo', $this->fakeCallable);
        $this->assertSame(MezzioRoute::HTTP_METHOD_ANY, $route->getAllowedMethods());
    }

    public function testRouteAllowsSpecifyingHttpMethods(): void
    {
        $methods = [RequestMethod::METHOD_GET, RequestMethod::METHOD_POST];
        $route = new Route('/foo', $this->fakeCallable, 'foo', $methods);
        $this->assertSame($methods, $route->getAllowedMethods());
    }

    public function testRouteCanMatchMethod(): void
    {
        $methods = [RequestMethod::METHOD_GET, RequestMethod::METHOD_POST];
        $route = new Route('/foo', $this->fakeCallable, 'foo', $methods);
        $this->assertTrue($route->allowsMethod(RequestMethod::METHOD_GET));
        $this->assertTrue($route->allowsMethod(RequestMethod::METHOD_POST));
        $this->assertFalse($route->allowsMethod(RequestMethod::METHOD_PATCH));
        $this->assertFalse($route->allowsMethod(RequestMethod::METHOD_DELETE));
    }

    public function testRouteHeadMethodIsNotAllowedByDefault(): void
    {
        $route = new Route('/foo', $this->fakeCallable, 'foo', [RequestMethod::METHOD_GET]);
        $this->assertFalse($route->allowsMethod(RequestMethod::METHOD_HEAD));
    }

    public function testRouteOptionsMethodIsNotAllowedByDefault(): void
    {
        $route = new Route('/foo', $this->fakeCallable, 'foo', [RequestMethod::METHOD_GET]);
        $this->assertFalse($route->allowsMethod(RequestMethod::METHOD_OPTIONS));
    }

    public function testRouteAllowsSpecifyingOptions(): void
    {
        $options = ['foo' => 'bar'];
        $route = new Route('/foo', $this->fakeCallable);
        $route->setOptions($options);
        $this->assertSame($options, $route->getOptions());
    }

    public function testRouteOptionsAreEmptyByDefault(): void
    {
        $route = new Route('/foo', $this->fakeCallable);
        $this->assertSame([], $route->getOptions());
    }

    public function testRouteNameForRouteAcceptingAnyMethodMatchesPathByDefault(): void
    {
        $route = new Route('/test', $this->fakeCallable);
        $this->assertSame('/test', $route->getName());
    }

    public function testRouteNameWithConstructor(): void
    {
        $route = new Route('/test', $this->fakeCallable, 'test', [RequestMethod::METHOD_GET]);
        $this->assertSame('test', $route->getName());
    }

    public function testRouteNameWithGET(): void
    {
        $route = new Route('/test', $this->fakeCallable, null, [RequestMethod::METHOD_GET]);
        $this->assertSame('/test^GET', $route->getName());
    }

    public function testRouteNameWithGetAndPost(): void
    {
        $route = new Route(
            '/test',
            $this->fakeCallable,
            null,
            [RequestMethod::METHOD_GET, RequestMethod::METHOD_POST]
        );
        $this->assertSame(
            '/test^GET' .
            MezzioRoute::HTTP_METHOD_SEPARATOR .
            RequestMethod::METHOD_POST,
            $route->getName()
        );
    }

    public function testRouteNameIsMutable(): void
    {
        $route = new Route('/foo', $this->fakeCallable, 'foo', [RequestMethod::METHOD_GET]);
        $route->setName('bar');

        $this->assertSame('bar', $route->getName());
    }

    /**
     * @return array[]
     */
    public function invalidHttpMethodsProvider(): array
    {
        return [
            [[123]],
            [[123, 456]],
            [['@@@']],
            [['@@@', '@@@']],
        ];
    }

    /**
     * @dataProvider invalidHttpMethodsProvider
     */
    public function testThrowsExceptionIfInvalidHttpMethodsAreProvided(array $invalidHttpMethods): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('One or more HTTP methods were invalid');

        new Route('/test', $this->fakeCallable, 'test', $invalidHttpMethods);
    }

    public function testConstructorShouldRaiseExceptionIfMethodsArgumentIsAnEmptyArray(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('empty');
        new Route('/foo', $this->fakeCallable, 'foo', []);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->fakeCallable = function () {
        };
        $this->noopMiddleware = $this->createMock(CallableMiddleware::class);
        $this->noopMiddleware->method('getCallable')->willReturn($this->fakeCallable);
    }
}
