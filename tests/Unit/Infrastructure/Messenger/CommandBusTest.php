<?php

declare(strict_types=1);

namespace GeekCell\DddBundle\Tests\Unit\Infrastructure\Messenger;

use GeekCell\Ddd\Contracts\Application\Command;
use GeekCell\DddBundle\Infrastructure\Messenger\CommandBus;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;

/**
 * Test fixture for CommandBus.
 */
class TestCommand implements Command
{
}

/**
 * Test fixture for CommandBus.
 */
class TestCommandHandler
{
    public function __invoke(TestCommand $command): mixed
    {
        return get_class($command);
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

    private function createMessageBus(
        string $type,
        callable $handler
    ): MessageBus
    {
        return new MessageBus([
            new HandleMessageMiddleware(new HandlersLocator([
                $type => [$handler],
            ])),
        ]);
    }
}
