<?php

declare(strict_types=1);

namespace GeekCell\DddBundle\Tests\Integration\Fixtures\Domain\Model;

use GeekCell\DddBundle\Domain\AggregateRoot;
use GeekCell\DddBundle\Tests\Integration\Fixtures\Domain\Event\UserStateChangedEvent;
use GeekCell\DddBundle\Tests\Integration\Fixtures\Domain\Event\UserUpdatedEvent;

class User extends AggregateRoot
{
    public function __construct(
        private string $username,
        private string $email,
        private bool $isActivated = false,
    ) {
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
        $this->record(new UserUpdatedEvent($this));
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
        $this->record(new UserUpdatedEvent($this));
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function activate(): void
    {
        $this->isActivated = true;
        $this->record(new UserStateChangedEvent($this, $this->isActivated));
    }

    public function deactivate(): void
    {
        $this->isActivated = false;
        $this->record(new UserStateChangedEvent($this, $this->isActivated));
    }

    public function isActivated(): bool
    {
        return $this->isActivated;
    }
}
