<?php

namespace GeekCell\DddBundle\Domain;

use Assert\AssertionFailedException;
use GeekCell\Ddd\Domain\AggregateRoot as BaseAggregateRoot;
use GeekCell\DddBundle\Support\Traits\DispatchableTrait;

abstract class AggregateRoot extends BaseAggregateRoot
{
    use DispatchableTrait;

    /**
     * Dispatches all events that have been recorded since the last commit.
     *
     *
     * @throws AssertionFailedException
     */
    public function commit(): void
    {
        foreach ($this->releaseEvents() as $event) {
            $this->dispatch($event);
        }
    }
}
