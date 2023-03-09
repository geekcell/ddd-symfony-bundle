<?php

declare(strict_types=1);

namespace GeekCell\DddBundle\Maker;

use GeekCell\Ddd\Contracts\Application\Query;
use GeekCell\Ddd\Contracts\Application\QueryHandler;
use GeekCell\Ddd\Domain\Collection;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputAwareMakerInterface;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Util\ClassNameDetails;
use Symfony\Bundle\MakerBundle\Util\UseStatementGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

const NAMESPACE_PREFIX = 'Application\\Query\\';

final class MakeQuery extends AbstractMaker implements InputAwareMakerInterface
{
    public function __construct() {}

    /**
     * @inheritDoc
     */
    public static function getCommandName(): string
    {
        return 'make:ddd:query';
    }

    /**
     * @inheritDoc
     */
    public static function getCommandDescription(): string
    {
        return 'Creates a new query class';
    }

    /**
     * @inheritDoc
     */
    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $command
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'The name of the query class (e.g. <fg=yellow>Customer</>)',
            )
        ;
    }

    /**
     * @inheritDoc
     */
    public function configureDependencies(DependencyBuilder $dependencies, InputInterface $input = null): void
    {
    }

    /**
     * @inheritDoc
     */
    public function interact(InputInterface $input, ConsoleStyle $io, Command $command): void
    {
    }

    /**
     * @inheritDoc
     */
    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $queryClassNameDetails = $generator->createClassNameDetails(
            $input->getArgument('name'),
            NAMESPACE_PREFIX,
            'Query',
        );

        $this->generateQuery($queryClassNameDetails, $generator);
        $this->generateQueryHandler($queryClassNameDetails, $generator);

        $this->writeSuccessMessage($io);
    }

    private function generateQuery(ClassNameDetails $queryClassNameDetails, Generator $generator)
    {
        $templateVars = [
            'use_statements' => new UseStatementGenerator([
                Query::class,
            ]),
        ];

        $templatePath = __DIR__.'/../Resources/skeleton/query/Query.tpl.php';
        $generator->generateClass(
            $queryClassNameDetails->getFullName(),
            $templatePath,
            $templateVars,
        );

        $generator->writeChanges();
    }

    private function generateQueryHandler(ClassNameDetails $queryClassNameDetails, Generator $generator)
    {
        $classNameDetails = $generator->createClassNameDetails(
            $queryClassNameDetails->getShortName(),
            NAMESPACE_PREFIX,
            'Handler',
        );

        $templateVars = [
            'use_statements' => new UseStatementGenerator([
                QueryHandler::class,
                Collection::class,
                AsMessageHandler::class
            ]),
            'query_class_name' => $queryClassNameDetails->getShortName()
        ];

        $templatePath = __DIR__.'/../Resources/skeleton/query/QueryHandler.tpl.php';
        $generator->generateClass(
            $classNameDetails->getFullName(),
            $templatePath,
            $templateVars,
        );

        $generator->writeChanges();
    }
}
