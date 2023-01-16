<?php

declare(strict_types=1);

namespace GeekCell\DddBundle\Support\Facades;

use Mockery;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Class Facade.
 * This is the base class for all facades.
 *
 * @package GeekCell\DddBundle\Support\Facades
 */
abstract class Facade
{
    /** @var \Symfony\Component\HttpKernel\Kernel|null */
    protected static ?Kernel $kernel;

    /** @var object[] */
    protected static array $resolvedInstances = [];

    /**
     * The sets the kernel instance.
     *
     * @codeCoverageIgnore
     *
     * @param Kernel $kernel
     */
    public static function setFacadeKernel(Kernel $kernel): void
    {
        self::$kernel = $kernel;
    }

    /**
     * Gets the kernel instance.
     *
     * @codeCoverageIgnore
     *
     * @return Kernel
     */
    public static function getFacadeKernel(): ?Kernel
    {
        return self::$kernel;
    }

    /**
     * Get the instance behind the facade.
     *
     * @return null|object
     *
     * @throws ServiceCircularReferenceException
     * @throws ServiceNotFoundException
     */
    public static function getFacadeRoot(): ?object
    {
        $accessor = static::getFacadeAccessor();
        if (isset(self::$resolvedInstances[$accessor])) {
            return self::$resolvedInstances[$accessor];
        }

        if (isset(self::$kernel)) {
            $instance = self::$kernel->getContainer()->get(static::getFacadeAccessor());
            if ($instance) {
                self::$resolvedInstances[$accessor] = $instance;
            }

            return $instance;
        }

        return null;
    }

    /**
     * Calls the method on the facade root instance.
     *
     * @param string $method
     * @param mixed[] $args
     *
     * @return mixed
     *
     * @throws \BadMethodCallException
     */
    public static function __callStatic(string $method, array $args): mixed
    {
        $instance = static::getFacadeRoot();

        if (!$instance) {
            throw new \BadMethodCallException(
                sprintf(
                    'Facade root %s not found.',
                    static::getFacadeAccessor()
                )
            );
        }

        if (!method_exists($instance, $method)) {
            throw new \BadMethodCallException(
                sprintf(
                    'Method %s::%s does not exist.',
                    get_class($instance),
                    $method
                )
            );
        }

        return $instance->$method(...$args);
    }

    /**
     * Clear all resolved facade instances.
     *
     * @codeCoverageIgnore
     *
     * @return void
     */
    public static function clear(): void
    {
        self::$resolvedInstances = [];
    }

    /**
     * Swap the underlying instance behind the facade.
     *
     * @param object $instance
     *
     * @return void
     */
    public static function swap(object $instance): void
    {
        $accessor = static::getFacadeAccessor();
        self::$resolvedInstances[$accessor] = $instance;

        if (isset(self::$kernel)) {
            self::$kernel->getContainer()->set($accessor, $instance);
        }
    }

    /**
     * Creates a mock object for the facade.
     *
     * @return Mockery\MockInterface|Mockery\LegacyMockInterface
     */
    public static function createMock(): object
    {
        $mockableClass = static::getMockableClass();
        return $mockableClass ? Mockery::mock($mockableClass) : Mockery::mock();
    }

    /**
     * Creates a mock object for the facade and swaps it.
     *
     * @return Mockery\MockInterface|Mockery\LegacyMockInterface
     */
    public static function createFreshMock(): object
    {
        $mock = static::createMock();
        static::swap($mock);

        return $mock;
    }

    /**
     * Get the mockable class for the bound instance.
     *
     * @return string|null
     */
    protected static function getMockableClass(): ?string
    {
        $instance = static::getFacadeRoot();
        if (!$instance) {
            return null;
        }

        return get_class($instance);
    }

    /**
     * Get the registered name of the service within the container.
     *
     * @return string
     */
    abstract protected static function getFacadeAccessor(): string;
}
