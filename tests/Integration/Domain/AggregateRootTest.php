<?php

declare(strict_types=1);

namespace GeekCell\DddBundle\Tests\Integration\Domain;

use GeekCell\DddBundle\Tests\Integration\Fixtures\Domain\Event\UserStateChangedEvent;
use GeekCell\DddBundle\Tests\Integration\Fixtures\Domain\Event\UserUpdatedEvent;
use GeekCell\DddBundle\Tests\Integration\Fixtures\Domain\Model\User;
use GeekCell\DddBundle\Tests\Integration\Fixtures\TestKernel;
use GeekCell\Facade\Facade;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class AggregateRootTest extends KernelTestCase
{
    /**
     * @param array<string, mixed> $options
     */
    protected static function createKernel(array $options = []): KernelInterface
    {
        return new TestKernel('test', true);
    }

    public function setUp(): void
    {
        parent::setUp();
        self::bootKernel(['debug' => false]);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        self::ensureKernelShutdown();
        Facade::clear();
    }

    public function testDomainEvents(): void
    {
        // Given
        $container = static::getContainer();

        $numEventsDispatched = 0;

        /** @var EventDispatcherInterface $eventDispatcher */
        $eventDispatcher = $container->get('event_dispatcher');
        $eventDispatcher->addListener(
            UserUpdatedEvent::class,
            function ($event) use (&$numEventsDispatched) {
                $this->assertInstanceOf(UserUpdatedEvent::class, $event);
                $numEventsDispatched++;
            }
        );
        $eventDispatcher->addListener(
            UserStateChangedEvent::class,
            function ($event) use (&$numEventsDispatched) {
                $this->assertInstanceOf(UserStateChangedEvent::class, $event);
                $numEventsDispatched++;
            }
        );

        // When
        $user = new User('jd', 'jd@example.org');
        $user->setUsername('john.doe');
        $user->setEmail('john.doe@example.com');
        $user->activate();
        $user->commit();

        // Then
        $this->assertEquals(3, $numEventsDispatched);
    }
}
