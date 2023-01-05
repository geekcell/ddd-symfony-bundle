<?php

namespace Tests\Unit\Support\Traits;

use GeekCell\DDDBundle\Domain\Event\DomainEvent;
use GeekCell\DDDBundle\Support\Facades\EventDispatcher;
use GeekCell\DDDBundle\Support\Traits\DispatchableTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher as SymfonyEventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DispatchableTraitTest extends TestCase
{
    use DispatchableTrait;

    public function tearDown(): void
    {
        parent::tearDown();
        EventDispatcher::clear();
    }

    public function testDispatch(): void
    {
        // Given
        $dispatcherMock = $this
            ->createMock(EventDispatcherInterface::class);

        /** @var EventDispatcherInterface $dispatcherMock */
        $this->setEventDispatcher($dispatcherMock);

        $event = $this->createDomainEvent('some-event');

        /** @var MockObject $dispatcherMock */
        $dispatcherMock
            ->expects($this->once())
            ->method('dispatch')
            ->with($event, DomainEvent::class);

        // When - Then
        $this->dispatch($event);
    }

    public function testRecordAndCommit(): void
    {
        // Given
        $dispatcherMock = $this
            ->createMock(EventDispatcherInterface::class);

        /** @var EventDispatcherInterface $dispatcherMock */
        $this->setEventDispatcher($dispatcherMock);

        $event1 = $this->createDomainEvent('some-event');
        $event2 = $this->createDomainEvent('some-other-event');
        $event3 = $this->createDomainEvent('and-another-event');

        /** @var MockObject $dispatcherMock */
        $dispatcherMock
            ->expects($this->exactly(3))
            ->method('dispatch')
            ->withConsecutive(
                [$event1, DomainEvent::class],
                [$event2, DomainEvent::class],
                [$event3, DomainEvent::class],
            );

        // When
        $this->record($event1);
        $this->record($event2);
        $this->record($event3);
        $this->commit();
    }

    public function testGetDefaultEventDispatcher(): void
    {
        // Given
        $dispatcher = new SymfonyEventDispatcher();
        EventDispatcher::swap($dispatcher);

        // When
        $default = $this->getEventDispatcher();

        // Then
        $this->assertSame($dispatcher, $default);
    }

    public function testRecordWithoutCommit(): void
    {
        // Given
        // Given
        $dispatcherMock = $this
            ->createMock(EventDispatcherInterface::class);

        $event1 = $this->createDomainEvent('some-event');
        $event2 = $this->createDomainEvent('some-other-event');
        $event3 = $this->createDomainEvent('and-another-event');

        /** @var EventDispatcherInterface $dispatcherMock */
        $this->setEventDispatcher($dispatcherMock);

        /** @var MockObject $dispatcherMock */
        $dispatcherMock
            ->expects($this->never())
            ->method('dispatch');

        // When
        $this->record($event1);
        $this->record($event2);
        $this->record($event3);
    }

    /**
     * Helper method to create an event instance that implements the
     * DomainEvent interface.
     *
     * @param string $name  The name of the event.
     *
     * @return DomainEvent
     */
    private function createDomainEvent(
        string $name
    ): DomainEvent {
        return new class ($name) implements DomainEvent {
            public function __construct(
                private string $name,
            ) {
            }

            public function getName(): string
            {
                return $this->name;
            }
        };
    }
}
