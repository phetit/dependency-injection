<?php

declare(strict_types=1);

namespace Phetit\Container\Tests;

use Closure;
use InvalidArgumentException;
use Phetit\Container\Container;
use Phetit\Container\Exception\EntryNotFoundException;
use Phetit\Container\Tests\Fixtures\Service;
use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase
{
    public function testHasReturnsFalseOnMissingEntry(): void
    {
        $container = new Container();

        $hasEntry = $container->has('foo');

        self::assertFalse($hasEntry);
    }

    public function testHasReturnsTrueOnRegisteredEntry(): void
    {
        $container = new Container();

        $container->parameter('foo', fn() => 'bar');
        $container->register('foo_service', fn() => 'bar_service');
        $container->factory('foo_factory', fn() => 'bar_factory');

        self::assertTrue($container->has('foo'));
        self::assertTrue($container->has('foo_service'));
        self::assertTrue($container->has('foo_factory'));
    }

    public function testResolvesClosureEntry(): void
    {
        $container = new Container();

        $container->factory('foo', fn() => 'bar');

        $resolve = $container->get('foo');

        self::assertEquals('bar', $resolve);
    }

    public function testThrowsExceptionWhenTryingToResolveMissingEntry(): void
    {

        $container = new Container();

        self::expectExceptionObject(new EntryNotFoundException());

        $container->get('foo');
    }

    public function testServiceShouldBeTheSame(): void
    {
        $container = new Container();

        $container->register('service', fn () => new Service());

        $serviceOne = $container->get('service');
        self::assertInstanceOf(Service::class, $serviceOne);

        $serviceTwo = $container->get('service');
        self::assertInstanceOf(Service::class, $serviceTwo);

        self::assertSame($serviceOne, $serviceTwo);
    }

    public function testFactoriesShouldBeDifferent(): void
    {
        $container = new Container();

        $container->factory('service', fn() => new Service());

        $serviceOne = $container->get('service');
        self::assertInstanceOf(Service::class, $serviceOne);

        $serviceTwo = $container->get('service');
        self::assertInstanceOf(Service::class, $serviceTwo);

        self::assertNotSame($serviceOne, $serviceTwo);
    }

    public function testSettingParameters(): void
    {
        $container = new Container();

        $container->parameter('foo', 'bar');

        self::assertSame('bar', $container->get('foo'));
    }

    public function testParametersAreNotResolved(): void
    {
        $container = new Container();

        $container->parameter('foo', fn () => 'bazz');

        $foo = $container->get('foo');

        self::assertInstanceOf(Closure::class, $foo);
        self::assertSame('bazz', $foo());
    }

    public function testResolvesNullValueParameters(): void
    {
        $container = new Container();

        $container->parameter('foo', null);

        self::assertTrue($container->has('foo'));
        self::assertNull($container->get('foo'));
    }

    public function testContainerShouldBeInjectedToServiceResolver(): void
    {
        $container = new Container();

        $container->parameter('value2', 78);
        $container->register('service2', fn(Container $c) => new Service($c->get('value2')));

        $service2 = $container->get('service2');

        self::assertInstanceOf(Service::class, $service2);
        self::assertSame(78, $service2->value);
    }

    public function testContainerShouldBeInjectedToFactoryResolver(): void
    {
        $container = new Container();

        $container->parameter('value', 67);
        $container->factory('service', fn (Container $c) => new Service($c->get('value')));

        $service = $container->get('service');

        self::assertInstanceOf(Service::class, $service);
        self::assertSame(67, $service->value);
    }

    public function testExceptionShouldBeThrownSettingParameterWithEmptyId(): void
    {
        $container = new Container();

        self::expectException(InvalidArgumentException::class);
        $container->parameter('', 'empty');
    }

    public function testExceptionShouldBeThrownSettingServiceWithEmptyId(): void
    {
        $container = new Container();

        self::expectException(InvalidArgumentException::class);
        $container->register('', fn () => new Service());
    }

    public function testExceptionShouldBeThrownSettingFactoryWithEmptyId(): void
    {
        $container = new Container();

        self::expectException(InvalidArgumentException::class);
        $container->factory('', fn() => new Service());
    }
}
