<?php

declare(strict_types=1);

namespace GeekCell\DddBundle\Maker;

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

abstract class AbstractBaseMakerCQRS extends AbstractMaker implements InputAwareMakerInterface
{
    /**
     * Should return the target for the extending command (query|command)
     * @return string
     */
    abstract function getTarget(): string;

    /**
     * Should return an array of classes to import when generating the entity
     * @return string[]
     */
    abstract function getEntityUseStatements(): array;

    /**
     * Should return an array of classes to import when generating the entity handler
     * @return string[]
     */
    abstract function getEntityHandlerUseStatements(): array;

    /**
     * @return string
     */
    function getClassSuffix(): string
    {
        return ucfirst($this->getTarget());
    }

    /**
     * @return string
     */
    function getNamespacePrefix(): string
    {
        return 'Application\\' . $this->getClassSuffix() . '\\';
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
                'The name of the ' . $this->getTarget() . ' class (e.g. <fg=yellow>Customer</>)',
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
        $entityClassNameDetails = $generator->createClassNameDetails(
            $input->getArgument('name'),
            $this->getNamespacePrefix(),
            $this->getClassSuffix(),
        );

        $this->generateEntity($entityClassNameDetails, $generator);
        $this->generateHandler($entityClassNameDetails, $generator);

        $this->writeSuccessMessage($io);
    }

    /**
     * @param ClassNameDetails $queryClassNameDetails
     * @param Generator $generator
     * @return void
     */
    private function generateEntity(ClassNameDetails $queryClassNameDetails, Generator $generator): void
    {
        $templateVars = [
            'use_statements' => new UseStatementGenerator($this->getEntityUseStatements()),
        ];

        $templatePath = __DIR__."/../Resources/skeleton/{$this->getTarget()}/{$this->getClassSuffix()}.tpl.php";
        $generator->generateClass(
            $queryClassNameDetails->getFullName(),
            $templatePath,
            $templateVars,
        );

        $generator->writeChanges();
    }

    /**
     * @param ClassNameDetails $queryClassNameDetails
     * @param Generator $generator
     * @return void
     */
    private function generateHandler(ClassNameDetails $queryClassNameDetails, Generator $generator): void
    {
        $classNameDetails = $generator->createClassNameDetails(
            $queryClassNameDetails->getShortName(),
            $this->getNamespacePrefix(),
            'Handler',
        );

        $templateVars = [
            'use_statements' => new UseStatementGenerator($this->getEntityHandlerUseStatements()),
            'query_class_name' => $queryClassNameDetails->getShortName()
        ];

        $templatePath = __DIR__."/../Resources/skeleton/{$this->getTarget()}/{$this->getClassSuffix()}Handler.tpl.php";
        $generator->generateClass(
            $classNameDetails->getFullName(),
            $templatePath,
            $templateVars,
        );

        $generator->writeChanges();
    }
}
