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
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TypeError;

use function sprintf;

/**
 * @covers \Mezzio\Router\Route
 */
class RouteTest extends TestCase
{
    use ProphecyTrait;

    /** @var callable */
    private $noopMiddleware;

    public function testRoutePathIsRetrievable(): void
    {
        $route = new Route('/foo', $this->noopMiddleware);
        $this->assertEquals('/foo', $route->getPath());
    }

    public function testRouteMiddlewareIsRetrievable(): void
    {
        $route = new Route('/foo', $this->noopMiddleware);
        $this->assertSame($this->noopMiddleware, $route->getRoute()->getMiddleware());
    }

    public function testRouteInstanceAcceptsAllHttpMethodsByDefault(): void
    {
        $route = new Route('/foo', $this->noopMiddleware);
        $this->assertSame(MezzioRoute::HTTP_METHOD_ANY, $route->getAllowedMethods());
    }

    public function testRouteAllowsSpecifyingHttpMethods(): void
    {
        $methods = [RequestMethod::METHOD_GET, RequestMethod::METHOD_POST];
        $route = new Route('/foo', $this->noopMiddleware, 'foo', $methods);
        $this->assertSame($methods, $route->getAllowedMethods());
    }

    public function testRouteCanMatchMethod(): void
    {
        $methods = [RequestMethod::METHOD_GET, RequestMethod::METHOD_POST];
        $route = new Route('/foo', $this->noopMiddleware, 'foo', $methods);
        $this->assertTrue($route->allowsMethod(RequestMethod::METHOD_GET));
        $this->assertTrue($route->allowsMethod(RequestMethod::METHOD_POST));
        $this->assertFalse($route->allowsMethod(RequestMethod::METHOD_PATCH));
        $this->assertFalse($route->allowsMethod(RequestMethod::METHOD_DELETE));
    }

    public function testRouteHeadMethodIsNotAllowedByDefault(): void
    {
        $route = new Route('/foo', $this->noopMiddleware, 'foo', [RequestMethod::METHOD_GET]);
        $this->assertFalse($route->allowsMethod(RequestMethod::METHOD_HEAD));
    }

    public function testRouteOptionsMethodIsNotAllowedByDefault(): void
    {
        $route = new Route('/foo', $this->noopMiddleware, 'foo', [RequestMethod::METHOD_GET]);
        $this->assertFalse($route->allowsMethod(RequestMethod::METHOD_OPTIONS));
    }

    public function testRouteAllowsSpecifyingOptions(): void
    {
        $options = ['foo' => 'bar'];
        $route = new Route('/foo', $this->noopMiddleware);
        $route->setOptions($options);
        $this->assertSame($options, $route->getOptions());
    }

    public function testRouteOptionsAreEmptyByDefault(): void
    {
        $route = new Route('/foo', $this->noopMiddleware);
        $this->assertSame([], $route->getOptions());
    }

    public function testRouteNameForRouteAcceptingAnyMethodMatchesPathByDefault(): void
    {
        $route = new Route('/test', $this->noopMiddleware);
        $this->assertSame('/test', $route->getName());
    }

    public function testRouteNameWithConstructor(): void
    {
        $route = new Route('/test', $this->noopMiddleware, 'test', [RequestMethod::METHOD_GET]);
        $this->assertSame('test', $route->getName());
    }

    public function testRouteNameWithGET(): void
    {
        $route = new Route('/test', $this->noopMiddleware, 'foo', [RequestMethod::METHOD_GET]);
        $this->assertSame('/test^GET', $route->getName());
    }

    public function testRouteNameWithGetAndPost(): void
    {
        $route = new Route(
            '/test',
            $this->noopMiddleware,
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

    /**
     * @requires PHP < 8.0
     */
    public function testThrowsExceptionDuringConstructionOnInvalidMiddleware(): void
    {
        $this->expectException(TypeError::class);
        $this->expectExceptionMessage(sprintf(
            'must implement interface %s',
            MiddlewareInterface::class
        ));

        new Route('/foo', 12345);
    }

    public function testRouteNameIsMutable(): void
    {
        $route = new Route('/foo', $this->noopMiddleware, 'foo', [RequestMethod::METHOD_GET]);
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

        new Route('/test', $this->noopMiddleware, 'test', $invalidHttpMethods);
    }

    public function testAllowsHttpInteropMiddleware(): void
    {
        $middleware = $this->createMock(CallableMiddleware::class);
        $route = new Route('/test', $middleware, 'test', MezzioRoute::HTTP_METHOD_ANY);
        $this->assertSame($middleware, $route->getRoute()->getMiddleware());
    }

    /**
     * @return array
     */
    public function invalidMiddleware(): array
    {
        // Strings are allowed, because they could be service names.
        return [
            'null' => [null],
            'true' => [true],
            'false' => [false],
            'zero' => [0],
            'int' => [1],
            'non-callable-object' => [(object)['handler' => 'foo']],
            'callback' => [
                function () {
                },
            ],
            'array' => [['Class', 'method']],
            'string' => ['Application\Middleware\HelloWorld'],
        ];
    }

    /**
     * @requires PHP < 8.0
     * @dataProvider invalidMiddleware
     * @param mixed $middleware
     */
    public function testConstructorRaisesExceptionForInvalidMiddleware(mixed $middleware): void
    {
        $this->expectException(TypeError::class);
        $this->expectExceptionMessage(sprintf(
            'must implement interface %s',
            MiddlewareInterface::class
        ));

        new Route('/test', $middleware);
    }

    public function testRouteIsMiddlewareAndProxiesToComposedMiddleware(): void
    {
        $request = $this->prophesize(ServerRequestInterface::class)->reveal();
        $handler = $this->prophesize(RequestHandlerInterface::class)->reveal();
        $response = $this->prophesize(ResponseInterface::class)->reveal();
        $middleware = $this->prophesize(MiddlewareInterface::class);
        $middleware->process($request, $handler)->willReturn($response);

        $route = new Route('/foo', $middleware->reveal());
        $this->assertSame($response, $route->getRoute()->getMiddleware()->process($request, $handler));
    }

    public function testConstructorShouldRaiseExceptionIfMethodsArgumentIsAnEmptyArray(): void
    {
        $middleware = $this->prophesize(MiddlewareInterface::class)->reveal();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('empty');
        new Route('/foo', $middleware, 'foo', []);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->noopMiddleware = $this->createMock(MiddlewareInterface::class);
    }
}
