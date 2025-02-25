<?php

declare(strict_types=1);

namespace GeekCell\DddBundle\Support\Traits;

use Assert\Assertion;
use GeekCell\Ddd\Contracts\Domain\Event as DomainEvent;
use GeekCell\DddBundle\Support\Facades\EventDispatcher;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Trait DispatchableTrait.
 * This trait provides methods to dispatch domain events.
 *
 * @package \GeekCell\DddBundle\Support\Traits
 */
trait DispatchableTrait
{
    private ?EventDispatcherInterface $eventDispatcher;

    /**
     * @see \GeekCell\Ddd\Contracts\Core\Dispatchable
     */
    public function dispatch(object $event): void
    {
        Assertion::isInstanceOf($event, DomainEvent::class);
        $this->getEventDispatcher()->dispatch($event);
    }

    /**
     * @codeCoverageIgnore
     */
    public function setEventDispatcher(
        EventDispatcherInterface $eventDispatcher
    ): void {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getEventDispatcher(): EventDispatcherInterface
    {
        if (!isset($this->eventDispatcher)) {
            $eventDispatcher = EventDispatcher::getFacadeRoot();

            /** @var EventDispatcherInterface $eventDispatcher */
            $this->eventDispatcher = $eventDispatcher;
        }

        return $this->eventDispatcher;
    }
}
