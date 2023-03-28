<?php

declare(strict_types=1);

namespace GeekCell\DddBundle\Maker;

use GeekCell\Ddd\Contracts\Application\Query;
use GeekCell\Ddd\Contracts\Application\QueryHandler;
use GeekCell\Ddd\Domain\Collection;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

final class MakeQuery extends AbstractBaseMakerCQRS
{
    const TARGET = 'query';

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
    function getTarget(): string
    {
        return self::TARGET;
    }

    /**
     * @inheritDoc
     */
    function getEntityUseStatements(): array
    {
        return [
            Query::class
        ];
    }

    /**
     * @inheritDoc
     */
    function getEntityHandlerUseStatements(): array
    {
        return [
            QueryHandler::class,
            Collection::class,
            AsMessageHandler::class
        ];
    }
}
