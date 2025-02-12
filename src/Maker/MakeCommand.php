<?php

declare(strict_types=1);

namespace GeekCell\DddBundle\Maker;

use GeekCell\Ddd\Contracts\Application\Command;
use GeekCell\Ddd\Contracts\Application\CommandHandler;
use GeekCell\Ddd\Domain\Collection;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

final class MakeCommand extends AbstractBaseMakerCQRS
{
    public const TARGET = 'command';

    /**
     * @inheritDoc
     */
    public static function getCommandName(): string
    {
        return 'make:ddd:' . self::TARGET;
    }

    /**
     * @inheritDoc
     */
    public static function getCommandDescription(): string
    {
        return 'Creates a new ' . self::TARGET . ' class and handler';
    }

    /**
     * @inheritDoc
     */
    public function getTarget(): string
    {
        return self::TARGET;
    }

    /**
     * @inheritDoc
     */
    public function getEntityUseStatements(): array
    {
        return [
            Command::class
        ];
    }

    /**
     * @inheritDoc
     */
    public function getEntityHandlerUseStatements(): array
    {
        return [
            CommandHandler::class,
            AsMessageHandler::class
        ];
    }
}
