<?php

declare(strict_types=1);

namespace GeekCell\DddBundle\Tests\Unit\Infrastructure\Messenger;

use GeekCell\Ddd\Contracts\Application\Command;
use GeekCell\DddBundle\Infrastructure\Messenger\CommandBus;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;

class TestCommand implements Command
{
}

class TestCommandHandler
{
    public function __invoke(TestCommand $command): mixed
    {
        return get_class($command);
    }
}

class ThrowingCommandHandler
{
    public function __construct(private readonly \Exception $exceptionToThrow)
    {
    }

    public function __invoke(TestCommand $command): mixed
    {
        throw $this->exceptionToThrow;
    }
}

class CommandBusTest extends TestCase
{
    public function testDispatch(): void
    {
        // Given
        $bus = $this->createMessageBus(
            TestCommand::class,
            new TestCommandHandler()
        );
        $commandBus = new CommandBus($bus);

        // When
        $result = $commandBus->dispatch(new TestCommand());

        // Then
        $this->assertSame(TestCommand::class, $result);
    }

    public function testDispatchFailsAndRethrowsException(): void
    {
        // Given
        $expectedException = new \Exception('Not good enough');
        $bus = $this->createMessageBus(
            TestCommand::class,
            new ThrowingCommandHandler($expectedException)
        );
        $commandBus = new CommandBus($bus);

        // Then
        $this->expectExceptionObject($expectedException);

        // When
        $commandBus->dispatch(new TestCommand());
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
