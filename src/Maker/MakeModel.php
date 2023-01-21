<?php

declare(strict_types=1);

namespace GeekCell\DddBundle\Maker;

use GeekCell\Ddd\Domain\ValueObject\Id;
use GeekCell\Ddd\Domain\ValueObject\Uuid;
use GeekCell\DddBundle\Domain\AggregateRoot;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputAwareMakerInterface;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Util\UseStatementGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

final class MakeModel extends AbstractMaker implements InputAwareMakerInterface
{
    public static function getCommandName(): string
    {
        return 'make:ddd:model';
    }

    public static function getCommandDescription(): string
    {
        return 'Creates a new domain model class';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $command
            ->addArgument(
                'name',
                InputArgument::OPTIONAL,
                'The name of the model class (e.g. <fg=yellow>Customer</>)',
            )
            ->addOption(
                'aggregate-root',
                null,
                InputOption::VALUE_NONE,
                'Marks the model as aggregate root',
            )
            ->addOption(
                'with-identity',
                null,
                InputOption::VALUE_REQUIRED,
                'Whether an identity value object should be created',
            )
            ->addOption(
                'with-suffix',
                null,
                InputOption::VALUE_NONE,
                'Adds the suffix "Model" to the model class name',
            )
        ;
    }

    public function interact(InputInterface $input, ConsoleStyle $io, Command $command): void
    {
        /** @var string $modelName */
        $modelName = $input->getArgument('name');
        $useSuffix = $io->confirm(
            sprintf(
                'Do you want to suffix the model class name? (<fg=yellow>%sModel</>)',
                $modelName,
            ),
            false,
        );
        $input->setOption('with-suffix', $useSuffix);

        if (false === $input->getOption('aggregate-root')) {
            $asAggregateRoot = $io->confirm(
                sprintf(
                    'Do you want create <fg=yellow>%s%s</> as aggregate root?',
                    $modelName,
                    $useSuffix ? 'Model' : '',
                ),
                true,
            );
            $input->setOption('aggregate-root', $asAggregateRoot);
        }

        if (null === $input->getOption('with-identity')) {
            $withIdentity = $io->choice(
                sprintf(
                    'How do you want to identify <fg=yellow>%s%s</>?',
                    $modelName,
                    $useSuffix ? 'Model' : '',
                ),
                [
                    'id' => sprintf(
                        'Numeric identity representation (<fg=yellow>%sId</>)',
                        $modelName,
                    ),
                    'uuid' => sprintf(
                        'UUID representation (<fg=yellow>%sUuid</>)',
                        $modelName,
                    ),
                    'none' => 'I\'ll add it later myself',
                ],
            );
            $input->setOption('with-identity', $withIdentity);
        }
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        /** @var string $modelName */
        $modelName = $input->getArgument('name');
        $suffix = $input->getOption('with-suffix') ? 'Model' : '';

        $classesToImport = [];

        /** @var string $withIdentity */
        $withIdentity = $input->getOption('with-identity');
        if ('none' !== $withIdentity) {
            $identityClassNameDetails = $generator->createClassNameDetails(
                $modelName,
                'Domain\\Model\\ValueObject\\Identity\\',
                ucfirst($withIdentity),
            );

            $templatePath = sprintf(
                '%s/../Resources/skeleton/identity/%s.tpl.php',
                __DIR__,
                ucfirst($withIdentity),
            );

            $extendsAlias = match ($withIdentity) {
                'id' => 'AbstractId',
                'uuid' => 'AbstractUuid',
                default => throw new \InvalidArgumentException('Invalid identity type'),
            };

            $baseClass = match ($withIdentity) {
                'id' => [Id::class => $extendsAlias],
                'uuid' => [Uuid::class => $extendsAlias],
                default => throw new \InvalidArgumentException('Invalid identity type'),
            };

            $generator->generateClass(
                $identityClassNameDetails->getFullName(),
                $templatePath,
                [
                    'extends_alias' => $extendsAlias,
                    // @phpstan-ignore-next-line
                    'use_statements' => new UseStatementGenerator([$baseClass]),
                ]
            );

            $classesToImport[] = $identityClassNameDetails->getFullName();
        }

        $modelClassNameDetails = $generator->createClassNameDetails(
            $modelName,
            'Domain\\Model\\',
            $suffix,
        );

        if ($input->getOption('aggregate-root')) {
            $classesToImport[] = AggregateRoot::class;
        }

        $templatePath = __DIR__.'/../Resources/skeleton/model/Model.tpl.php';
        $generator->generateClass(
            $modelClassNameDetails->getFullName(),
            $templatePath,
            [
                'aggregate_root' => $input->getOption('aggregate-root'),
                'entity' => $input->getOption('entity'),
                'use_statements' => new UseStatementGenerator($classesToImport),
                'with_identity' => 'none' !== $withIdentity ? $withIdentity : null,
            ]
        );

        $generator->writeChanges();

        $this->writeSuccessMessage($io);
    }

    public function configureDependencies(DependencyBuilder $dependencies, InputInterface $input = null): void
    {
        // TODO: Implement configureDependencies() method.
    }
}
