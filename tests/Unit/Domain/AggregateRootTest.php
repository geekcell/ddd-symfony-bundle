<?php

declare(strict_types=1);

namespace GeekCell\DddBundle\Tests\Unit\Domain;

use GeekCell\Ddd\Contracts\Domain\Event as DomainEvent;
use GeekCell\DddBundle\Domain\AggregateRoot;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class TestDomain extends AggregateRoot
{
}

class AggregateRootTest extends TestCase
{
    public function testRecordAndCommit(): void
    {
        // Given
        $testDomain = new TestDomain();

        /** @var EventDispatcherInterface&MockObject $dispatcherMock */
        $dispatcherMock = $this->createMock(EventDispatcherInterface::class);

        $testDomain->setEventDispatcher($dispatcherMock);

        $event1 = $this->createDomainEvent('some-event');
        $event2 = $this->createDomainEvent('some-other-event');
        $event3 = $this->createDomainEvent('and-another-event');

        $expectedEvents = [$event1, $event2, $event3];

        $nthEvent = 0;
        /** @var MockObject $dispatcherMock */
        $dispatcherMock
            ->expects($this->exactly(3))
            ->method('dispatch')
            ->with(
                $this->callback(function (DomainEvent $event) use ($expectedEvents, &$nthEvent) {
                    return $expectedEvents[$nthEvent++] === $event;
                })
            );

        // When
        $testDomain->record($event1);
        $testDomain->record($event2);
        $testDomain->record($event3);
        $testDomain->commit();
    }

    public function testRecordWithoutCommit(): void
    {
        // Given
        $testDomain = new TestDomain();

        $event1 = $this->createDomainEvent('some-event');
        $event2 = $this->createDomainEvent('some-other-event');
        $event3 = $this->createDomainEvent('and-another-event');

        /** @var EventDispatcherInterface&MockObject $dispatcherMock */
        $dispatcherMock = $this->createMock(EventDispatcherInterface::class);

        $testDomain->setEventDispatcher($dispatcherMock);


        /** @var MockObject $dispatcherMock */
        $dispatcherMock
            ->expects($this->never())
            ->method('dispatch');

        // When
        $testDomain->record($event1);
        $testDomain->record($event2);
        $testDomain->record($event3);
    }

    public function testDispatch(): void
    {
        // Given
        $testDomain = new TestDomain();

        /** @var EventDispatcherInterface&MockObject $dispatcherMock */
        $dispatcherMock = $this->createMock(EventDispatcherInterface::class);

        $testDomain->setEventDispatcher($dispatcherMock);

        $event = $this->createDomainEvent('some-event');

        /** @var MockObject $dispatcherMock */
        $dispatcherMock
            ->expects($this->once())
            ->method('dispatch')
            ->with($event);

        // When - Then
        $testDomain->dispatch($event);
    }

    /**
     * Helper method to create an event instance that implements the
     * DomainEvent interface.
     *
     * @param string $name The name of the event.
     */
    private function createDomainEvent(
        string $name
    ): DomainEvent {
        return new class ($name) implements DomainEvent {
            public function __construct(
                private readonly string $name,
            ) {
            }

            public function getName(): string
            {
                return $this->name;
            }
        };
    }
}
