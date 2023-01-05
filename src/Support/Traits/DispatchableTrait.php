<?php

declare(strict_types=1);

namespace GeekCell\DDDBundle\Support\Traits;

use Assert\Assertion;
use GeekCell\DDDBundle\Domain\Event\DomainEvent;
use GeekCell\DDDBundle\Support\Facades\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Trait DispatchableTrait.
 * This trait provides methods to record and dispatch domain events.
 *
 * @package GeekCell\DDDBundle\Support\Traits
 * @codeCoverageIgnore
 */
trait DispatchableTrait
{
    /** @var DomainEvent[] */
    private array $recordedDomainEvents = [];

    /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface|null */
    private ?EventDispatcherInterface $eventDispatcher;

    /**
     * Records an event for later dispatching.
     *
     * @param DomainEvent $event
     */
    public function record(DomainEvent $event): void
    {
        $this->recordedDomainEvents[] = $event;
    }

    /**
     * Alias for record().
     *
     * @codeCoverageIgnore
     *
     * @param DomainEvent $domainEvent
     */
    public function log(DomainEvent $domainEvent): void
    {
        $this->record($domainEvent);
    }

    /**
     * Dispatches an event directly.
     *
     * @param DomainEvent $event
     */
    public function dispatch(DomainEvent $event): void
    {
        $this->getEventDispatcher()->dispatch($event, DomainEvent::class);
    }

    /**
     * Dispatches all recorded events.
     */
    public function commit(): void
    {
        foreach ($this->recordedDomainEvents as $event) {
            $this->getEventDispatcher()->dispatch($event, DomainEvent::class);
        }

        $this->recordedDomainEvents = [];
    }

    /**
     * @codeCoverageIgnore
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function setEventDispatcher(
        EventDispatcherInterface $eventDispatcher
    ): void {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @return EventDispatcherInterface
     * @throws \Assert\AssertionFailedException
     */
    public function getEventDispatcher(): EventDispatcherInterface
    {
        if (!isset($this->eventDispatcher)) {
            $eventDispatcher = EventDispatcher::getFacadeRoot();
            Assertion::notNull($eventDispatcher, 'EventDispatcher is not set');

            /** @var EventDispatcherInterface $eventDispatcher */
            $this->eventDispatcher = $eventDispatcher;
        }

        return $this->eventDispatcher;
    }
}
