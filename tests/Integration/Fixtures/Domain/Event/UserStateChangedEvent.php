<?php

declare(strict_types=1);

namespace GeekCell\DddBundle\Tests\Integration\Fixtures\Domain\Event;

use GeekCell\Ddd\Contracts\Domain\Event as DomainEvent;
use GeekCell\DddBundle\Tests\Integration\Fixtures\Domain\Model\User;

class UserStateChangedEvent implements DomainEvent
{
    public function __construct(
        public User $user,
        public bool $isActivated,
    ) {
    }
}
