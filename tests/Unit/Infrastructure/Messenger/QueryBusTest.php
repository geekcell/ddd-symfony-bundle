<?php

declare(strict_types=1);

namespace GeekCell\DddBundle\Tests\Unit\Infrastructure\Messenger;

use Exception;
use GeekCell\Ddd\Contracts\Application\Query;
use GeekCell\DddBundle\Infrastructure\Messenger\CommandBus;
use GeekCell\DddBundle\Infrastructure\Messenger\QueryBus;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;

class TestQuery implements Query
{
}

class ThrowingQueryHandler
{
    public function __construct(private readonly Exception $exceptionToThrow)
    {
    }

    public function __invoke(TestQuery $query): mixed
    {
        throw $this->exceptionToThrow;
    }
}

class TestQueryHandler
{
    public function __invoke(TestQuery $query): mixed
    {
        return $query::class;
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

    public function testDispatchFailsAndRethrowsException(): void
    {
        // Given
        $expectedException = new Exception('Not good enough');
        $bus = $this->createMessageBus(
            TestQuery::class,
            new ThrowingQueryHandler($expectedException)
        );
        $commandBus = new QueryBus($bus);

        // Then
        $this->expectExceptionObject($expectedException);

        // When
        $commandBus->dispatch(new TestQuery());
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
