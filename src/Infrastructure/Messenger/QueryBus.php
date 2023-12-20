<?php

declare(strict_types=1);

namespace GeekCell\DddBundle\Infrastructure\Messenger;

use GeekCell\Ddd\Contracts\Application\Query;
use GeekCell\Ddd\Contracts\Application\QueryBus as QueryBusInterface;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;

class QueryBus implements QueryBusInterface
{
    use HandleTrait;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    public function dispatch(Query $query): mixed
    {
        try {
            return $this->handle($query);
        } catch (HandlerFailedException $e) {
            $exceptions = $e->getWrappedExceptions();
            $first = array_shift($exceptions);
            if ($first) {
                throw $first;
            }

            throw $e;
        }
    }
}
