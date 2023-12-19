<?php

declare(strict_types=1);

namespace GeekCell\DddBundle\Infrastructure\Messenger;

use GeekCell\Ddd\Contracts\Application\Command;
use GeekCell\Ddd\Contracts\Application\CommandBus as CommandBusInterface;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;

class CommandBus implements CommandBusInterface
{
    use HandleTrait;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    public function dispatch(Command $command): mixed
    {
        try {
            return $this->handle($command);
        } catch (HandlerFailedException $e) {
            $exceptions = $e->getWrappedExceptions();
            throw $exceptions[0];
        }
    }
}
