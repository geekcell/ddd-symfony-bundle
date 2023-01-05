<?php

namespace GeekCell\DDDBundle\Tests\Unit\Support\Facades;

use BadMethodCallException;
use GeekCell\DDDBundle\Support\Facades\Facade;
use Mockery;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Test fixture for the Facade class.
 *
 * @package Tests\Unit\Support\Facades
 */
class FooFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'foo';
    }

    public static function reset(): void
    {
        self::clear();
        self::$kernel = null;
    }
}

/**
 * Test fixture for resolved service.
 *
 * @package Tests\Unit\Support\Facades
 */
class Foo
{
    public function bar(): string
    {
        return 'bar';
    }
}

class FacadeTest extends TestCase
{
    /** @var Kernel|Mockery\MockInterface */
    private mixed $kernelMock;

    /** @var ContainerInterface|Mockery\MockInterface */
    private mixed $containerMock;

    public function setUp(): void
    {
        parent::setUp();

        $this->kernelMock = Mockery::mock(Kernel::class);
        $this->containerMock = Mockery::mock(ContainerInterface::class);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        FooFacade::reset();
        Mockery::close();
    }

    public function testGetFacadeRoot(): void
    {
        // Given
        $instance = new Foo();
        FooFacade::setFacadeKernel($this->kernelMock);

        $this->kernelMock
            ->shouldReceive('getContainer')
            ->once()
            ->withNoArgs()
            ->andReturn($this->containerMock);

        $this->containerMock
            ->shouldReceive('get')
            ->once()
            ->with('foo')
            ->andReturn($instance);

        // When
        $result = FooFacade::getFacadeRoot();

        $this->assertSame($instance, $result);
    }

    public function testGetFacadeRootWithCachedInstance(): void
    {
        // Given
        $instance = new Foo();
        FooFacade::setFacadeKernel($this->kernelMock);

        $this->kernelMock
            ->shouldReceive('getContainer')
            ->once()
            ->withNoArgs()
            ->andReturn($this->containerMock);

        $this->containerMock
            ->shouldReceive('get')
            ->once()
            ->with('foo')
            ->andReturn($instance);

        // When
        $result1 = FooFacade::getFacadeRoot();
        $result2 = FooFacade::getFacadeRoot();
        $result3 = FooFacade::getFacadeRoot();

        // Then
        $this->assertSame($instance, $result1);
        $this->assertSame($result1, $result2);
        $this->assertSame($result2, $result3);
    }

    public function testGetFacadeRootWithoutKernel(): void
    {
        // Given - When
        $result = FooFacade::getFacadeRoot();

        // Then
        $this->assertNull($result);
    }

    /**
     * @expectedException ServiceNotFoundException
     */
    public function testGetFacadeRootWithoutMatchingAccessor(): void
    {
        // Given
        FooFacade::setFacadeKernel($this->kernelMock);

        $this->kernelMock
            ->shouldReceive('getContainer')
            ->once()
            ->withNoArgs()
            ->andReturn($this->containerMock);

        $this->containerMock
            ->shouldReceive('get')
            ->once()
            ->with('foo')
            ->andThrow(new ServiceNotFoundException('foo'));

        $this->expectException(ServiceNotFoundException::class);

        // When - Then
        FooFacade::getFacadeRoot();
    }

    public function testCallStatic(): void
    {
        // Given
        $instance = new Foo();
        FooFacade::setFacadeKernel($this->kernelMock);

        $this->kernelMock
            ->shouldReceive('getContainer')
            ->andReturn($this->containerMock);

        $this->containerMock
            ->shouldReceive('get')
            ->with('foo')
            ->andReturn($instance);

        // When
        $result = FooFacade::bar(); // @phpstan-ignore-line

        // Then
        $this->assertEquals($result, $instance->bar());
    }

    public function testCallStaticWithoutKernel(): void
    {
        // Given
        $this->expectException(BadMethodCallException::class);

        // When - Then
        FooFacade::bar(); // @phpstan-ignore-line
    }

    public function testCallStaticWithUnknownMethod(): void
    {
        // Given
        FooFacade::setFacadeKernel($this->kernelMock);

        $this->kernelMock
            ->shouldReceive('getContainer')
            ->andReturn($this->containerMock);

        $this->containerMock
            ->shouldReceive('get')
            ->andReturn(new Foo());

        $this->expectException(BadMethodCallException::class);

        // When - Then
        FooFacade::baz(); // @phpstan-ignore-line
    }

    public function testSwap(): void
    {
        // Given
        $instance = new Foo();
        FooFacade::setFacadeKernel($this->kernelMock);

        $swapInstance = new \stdClass();

        $this->kernelMock
            ->shouldReceive('getContainer')
            ->andReturn($this->containerMock);

        $this->containerMock
            ->shouldReceive('get')
            ->andReturn($instance);

        $this->containerMock
            ->shouldReceive('set');

        // When
        $result1 = FooFacade::getFacadeRoot();

        FooFacade::swap($swapInstance);
        $result2 = FooFacade::getFacadeRoot();

        // Then
        $this->assertSame($instance, $result1);
        $this->assertSame($swapInstance, $result2);
    }

    public function testCreateMock(): void
    {
        // Given
        $instance = new Foo();
        FooFacade::setFacadeKernel($this->kernelMock);

        $this->kernelMock
            ->shouldReceive('getContainer')
            ->andReturn($this->containerMock);

        $this->containerMock
            ->shouldReceive('get')
            ->andReturn($instance);

        // When
        $result = FooFacade::createMock();

        // Then
        $this->assertInstanceOf(Mockery\MockInterface::class, $result);
    }

    public function testCreateFreshMock(): void
    {
        // Given
        $instance = new Foo();
        FooFacade::setFacadeKernel($this->kernelMock);

        $this->kernelMock
            ->shouldReceive('getContainer')
            ->andReturn($this->containerMock);

        $this->containerMock
            ->shouldReceive('get')
            ->andReturn($instance);

        $this->containerMock
            ->shouldReceive('set');

        // When
        $result1 = FooFacade::createFreshMock();
        $result2 = FooFacade::getFacadeRoot();

        // Then
        $this->assertInstanceOf(Mockery\MockInterface::class, $result1);
        $this->assertInstanceOf(Mockery\MockInterface::class, $result2);
        $this->assertSame($result1, $result2);
    }
}
