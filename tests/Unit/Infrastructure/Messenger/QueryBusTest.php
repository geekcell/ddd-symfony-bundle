<?php

declare(strict_types=1);

namespace GeekCell\DddBundle\Tests\Unit\Infrastructure\Messenger;

use GeekCell\Ddd\Contracts\Application\Query;
use GeekCell\DddBundle\Infrastructure\Messenger\QueryBus;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;

/**
 * Test fixture for QueryBus.
 */
class TestQuery implements Query
{
}

/**
 * Test fixture for CommandBus.
 */
class TestQueryHandler
{
    public function __invoke(TestQuery $query): mixed
    {
        return get_class($query);
    }
}

class QueryBusTest extends TestCase
{
    public function testDispatch(): void
    {
        // Given
        $bus = $this->createMessageBus(
            TestQuery::class,
            new TestQueryHandler()
        );
        $queryBus = new QueryBus($bus);

        // When
        $result = $queryBus->dispatch(new TestQuery());

        // Then
        $this->assertSame(TestQuery::class, $result);
    }

    private function createMessageBus(
        string $type,
        callable $handler
    ): MessageBus {
        return new MessageBus([
            new HandleMessageMiddleware(new HandlersLocator([
                $type => [$handler],
            ])),
        ]);
    }
}
