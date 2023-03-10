<?php

declare(strict_types=1);

namespace GeekCell\DddBundle\Maker;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use GeekCell\Ddd\Domain\ValueObject\Id;
use GeekCell\Ddd\Domain\ValueObject\Uuid;
use GeekCell\DddBundle\Domain\AggregateRoot;
use GeekCell\DddBundle\Infrastructure\Doctrine\Type\AbstractIdType;
use GeekCell\DddBundle\Infrastructure\Doctrine\Type\AbstractUuidType;
use GeekCell\DddBundle\Maker\Doctrine\DoctrineConfigUpdater;
use Symfony\Bundle\MakerBundle\Exception\RuntimeCommandException;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\Doctrine\ORMDependencyBuilder;
use Symfony\Bundle\MakerBundle\FileManager;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputAwareMakerInterface;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Util\ClassNameDetails;
use Symfony\Bundle\MakerBundle\Util\UseStatementGenerator;
use Symfony\Bundle\MakerBundle\Util\YamlManipulationFailedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

use function Symfony\Component\String\u;

const DOCTRINE_CONFIG_PATH = 'config/packages/doctrine.yaml';

final class MakeModel extends AbstractMaker implements InputAwareMakerInterface
{
    /**
     * @var array<string|array<string, string>>
     */
    private $classesToImport = [];

    /**
     * @var array<string, mixed>
     */
    private $templateVariables = [];


    /**
     * Constructor.
     *
     * @param DoctrineConfigUpdater $doctrineUpdater
     * @param FileManager $fileManager
     */
    public function __construct(
        private DoctrineConfigUpdater $doctrineUpdater,
        private FileManager $fileManager,
    ) {}

    /**
     * @inheritDoc
     */
    public static function getCommandName(): string
    {
        return 'make:ddd:model';
    }

    /**
     * @inheritDoc
     */
    public static function getCommandDescription(): string
    {
        return 'Creates a new domain model class';
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
                'The name of the model class (e.g. <fg=yellow>Customer</>)',
            )
            ->addOption(
                'aggregate-root',
                null,
                InputOption::VALUE_REQUIRED,
                'Marks the model as aggregate root',
            )
            ->addOption(
                'entity',
                null,
                InputOption::VALUE_REQUIRED,
                'Use this model as Doctrine entity',
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
                InputOption::VALUE_REQUIRED,
                'Adds the suffix "Model" to the model class name',
            )
        ;
    }

    /**
     * @inheritDoc
     */
    public function configureDependencies(DependencyBuilder $dependencies, InputInterface $input = null): void
    {
        if (null === $input || !$this->shouldGenerateEntity($input)) {
            return;
        }

        ORMDependencyBuilder::buildDependencies($dependencies);
    }

    /**
     * @inheritDoc
     */
    public function interact(InputInterface $input, ConsoleStyle $io, Command $command): void
    {
        if (!$this->fileManager->fileExists(DOCTRINE_CONFIG_PATH)) {
            throw new RuntimeCommandException('The file "' . DOCTRINE_CONFIG_PATH . '" does not exist. This command requires that file to exist so that it can be updated.');
        }

        /** @var string $modelName */
        $modelName = $input->getArgument('name');

        if (false === $input->getOption('with-suffix')) {
            $useSuffix = $io->confirm(
                sprintf(
                    'Do you want to suffix the model class name? (<fg=yellow>%sModel</>)',
                    $modelName,
                ),
                false,
            );
            $input->setOption('with-suffix', $useSuffix);
        }

        if (false === $input->getOption('aggregate-root')) {
            $asAggregateRoot = $io->confirm(
                sprintf(
                    'Do you want create <fg=yellow>%s%s</> as aggregate root?',
                    $modelName,
                    $useSuffix ? 'Model' : '',
                ),
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
                    'n/a' => 'I\'ll take care later myself',
                ],
            );
            $input->setOption('with-identity', $withIdentity);
        }

        if (null === $input->getOption('entity')) {
            $asEntity = $io->choice(
                sprintf(
                    'Do you want <fg=yellow>%s%s</> to be a (Doctrine) database entity?',
                    $modelName,
                    $useSuffix ? 'Model' : '',
                ),
                [
                    'attributes' => 'Yes, via PHP attributes',
                    'xml' => 'Yes, via XML mapping',
                    'n/a' => 'No, I\'ll handle it separately',
                ],
            );
            $input->setOption('entity', $asEntity);
        }
    }

    /**
     * @inheritDoc
     */
    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        /** @var string $modelName */
        $modelName = $input->getArgument('name');
        $suffix = $input->getOption('with-suffix') ? 'Model' : '';

        $modelClassNameDetails = $generator->createClassNameDetails(
            $modelName,
            'Domain\\Model\\',
            $suffix,
        );

        $this->templateVariables['class_name'] = $modelClassNameDetails->getShortName();

        $this->generateIdentity($modelName, $input, $io, $generator);
        $this->generateEntityMappings($modelClassNameDetails, $input, $io, $generator);
        $this->generateEntity($modelClassNameDetails, $input, $io, $generator);
        $this->generateRepository($modelClassNameDetails, $input, $io, $generator);

        $this->writeSuccessMessage($io);
    }

    /**
     * Optionally, generate the identity value object for the model.
     *
     * @param string $modelName
     * @param InputInterface $input
     * @param ConsoleStyle $io
     * @param Generator $generator
     */
    private function generateIdentity(
        string $modelName,
        InputInterface $input,
        ConsoleStyle $io,
        Generator $generator
    ): void {
        if (!$this->shouldGenerateIdentity($input)) {
            return;
        }

        // 1. Generate the identity value object.

        /** @var string $identityType */
        $identityType = $input->getOption('with-identity');
        $identityClassNameDetails = $generator->createClassNameDetails(
            $modelName,
            'Domain\\Model\\ValueObject\\Identity\\',
            ucfirst($identityType),
        );

        $extendsAlias = match ($identityType) {
            'id' => 'AbstractId',
            'uuid' => 'AbstractUuid',
            default => null,
        };

        $baseClass = match ($identityType) {
            'id' => [Id::class => $extendsAlias],
            'uuid' => [Uuid::class => $extendsAlias],
            default => null,
        };

        if (!$extendsAlias || !$baseClass) {
            throw new \InvalidArgumentException(sprintf('Unknown identity type "%s"', $identityType));
        }

        // @phpstan-ignore-next-line
        $useStatements = new UseStatementGenerator([$baseClass]);

        $generator->generateClass(
            $identityClassNameDetails->getFullName(),
            __DIR__.'/../Resources/skeleton/model/Identity.tpl.php',
            [
                'identity_class' => $identityClassNameDetails->getShortName(),
                'extends_alias' => $extendsAlias,
                'use_statements' => $useStatements,
            ],
        );

        $this->classesToImport[] = $identityClassNameDetails->getFullName();
        $this->templateVariables['identity_type'] = $identityType;
        $this->templateVariables['identity_class'] = $identityClassNameDetails->getShortName();

        if (!$this->shouldGenerateEntity($input)) {
            return;
        }

        // 2. Generate custom Doctrine mapping type for the identity.

        $mappingTypeClassNameDetails = $generator->createClassNameDetails(
            $modelName.ucfirst($identityType),
            'Infrastructure\\Doctrine\\DBAL\\Type\\',
            'Type',
        );

        $baseTypeClass = match ($identityType) {
            'id' => AbstractIdType::class,
            'uuid' => AbstractUuidType::class,
            default => null,
        };

        if (!$baseTypeClass) {
            throw new \InvalidArgumentException(sprintf('Unknown identity type "%s"', $identityType));
        }

        $useStatements = new UseStatementGenerator([
            $identityClassNameDetails->getFullName(),
            $baseTypeClass
        ]);

        $typeName = u($identityClassNameDetails->getShortName())->snake()->toString();
        $generator->generateClass(
            $mappingTypeClassNameDetails->getFullName(),
            __DIR__.'/../Resources/skeleton/model/DoctrineMappingType.tpl.php',
            [
                'type_name' => $typeName,
                'type_class' => $mappingTypeClassNameDetails->getShortName(),
                'extends_type_class' => sprintf('Abstract%sType', ucfirst($identityType)),
                'identity_class' => $identityClassNameDetails->getShortName(),
                'use_statements' => $useStatements,
            ],
        );

        $configPath = 'config/packages/doctrine.yaml';
        if (!$this->fileManager->fileExists($configPath)) {
            $io->error(sprintf('Doctrine configuration at path "%s" does not exist.', $configPath));
            return;
        }

        // 2.1 Add the custom mapping type to the Doctrine configuration.

        $newYaml = $this->doctrineUpdater->addCustomDBALMappingType(
            $this->fileManager->getFileContents($configPath),
            $typeName,
            $mappingTypeClassNameDetails->getFullName(),
        );
        $generator->dumpFile($configPath, $newYaml);

        $this->classesToImport[] = $mappingTypeClassNameDetails->getFullName();
        $this->templateVariables['type_class'] = $mappingTypeClassNameDetails->getShortName();
        $this->templateVariables['type_name'] = $typeName;

        // Write out the changes.
        $generator->writeChanges();
    }

    /**
     * Optionally, generate entity mappings for the model.
     *
     * @param ClassNameDetails $modelClassNameDetails
     * @param InputInterface $input
     * @param ConsoleStyle $io
     * @param Generator $generator
     */
    private function generateEntityMappings(
        ClassNameDetails $modelClassNameDetails,
        InputInterface $input,
        ConsoleStyle $io,
        Generator $generator
    ): void {
        if (!$this->shouldGenerateEntity($input)) {
            return;
        }

        $modelName = $modelClassNameDetails->getShortName();

        if ($this->shouldGenerateEntityAttributes($input)) {
            try {
                $newYaml = $this->doctrineUpdater->updateORMDefaultEntityMapping(
                    $this->fileManager->getFileContents(DOCTRINE_CONFIG_PATH),
                    'attribute',
                    '%kernel.project_dir%/src/Domain/Model',
                );
                $generator->dumpFile(DOCTRINE_CONFIG_PATH, $newYaml);
                $this->classesToImport[] = ['Doctrine\\ORM\\Mapping' => 'ORM'];
                $this->templateVariables['as_entity'] = true;
            } catch (YamlManipulationFailedException $e) {
                $io->error($e->getMessage());
                $this->templateVariables['as_entity'] = false;
            }

            return;
        }

        if ($this->shouldGenerateEntityXml($input)) {
            $tableName = u($modelClassNameDetails->getShortName())->before('Model')->snake()->toString();
            $hasIdentity = $this->shouldGenerateIdentity($input);
            if ($hasIdentity && !isset($this->templateVariables['type_name'])) {
                throw new \LogicException(
                    'Cannot generate entity XML mapping without identity type (which should have been generated).'
                );
            }

            $this->templateVariables['as_entity'] = false;

            try {
                $mappingsDirectory = '/src/Infrastructure/Doctrine/ORM/Mapping';
                $newYaml = $this->doctrineUpdater->updateORMDefaultEntityMapping(
                    $this->fileManager->getFileContents(DOCTRINE_CONFIG_PATH),
                    'xml',
                    '%kernel.project_dir%'.$mappingsDirectory,
                );
                $generator->dumpFile(DOCTRINE_CONFIG_PATH, $newYaml);

                $targetPath = sprintf(
                    '%s%s/%s.orm.xml',
                    $this->fileManager->getRootDirectory(),
                    $mappingsDirectory,
                    $modelName
                );
                $generator->generateFile(
                    $targetPath,
                    __DIR__.'/../Resources/skeleton/doctrine/Mapping.tpl.xml.php',
                    [
                        'model_class' => $modelClassNameDetails->getFullName(),
                        'has_identity' => $hasIdentity,
                        'type_name' => $this->templateVariables['type_name'],
                        'table_name' => $tableName,
                        'identity_column_name' => $this->templateVariables['identity_type'],
                    ],
                );
            } catch (YamlManipulationFailedException $e) {
                $io->error($e->getMessage());
            }
        }

        // Write out the changes.
        $generator->writeChanges();
    }

    /**
     * Generate model entity
     *
     * @param ClassNameDetails $modelClassNameDetails
     * @param InputInterface $input
     * @param ConsoleStyle $io
     * @param Generator $generator
     */
    private function generateEntity(
        ClassNameDetails $modelClassNameDetails,
        InputInterface $input,
        ConsoleStyle $io,
        Generator $generator
    ): void {
        if ($input->getOption('aggregate-root')) {
            $this->classesToImport[] = AggregateRoot::class;
            $this->templateVariables['extends_aggregate_root'] = true;
        }

        // @phpstan-ignore-next-line
        $this->templateVariables['use_statements'] = new UseStatementGenerator($this->classesToImport);

        $templatePath = __DIR__.'/../Resources/skeleton/model/Model.tpl.php';
        $generator->generateClass(
            $modelClassNameDetails->getFullName(),
            $templatePath,
            $this->templateVariables,
        );

        $generator->writeChanges();
    }

    /**
     * Generate model repository
     *
     * @param InputInterface $input
     * @param ConsoleStyle $io
     * @param Generator $generator
     */
    private function generateRepository(
        ClassNameDetails $modelClassNameDetails,
        InputInterface $input,
        ConsoleStyle $io,
        Generator $generator
    ): void {
        $classNameDetails = $generator->createClassNameDetails(
            $input->getArgument('name'),
            'Repository\\',
            'Repository',
        );

        $templateVars = [
            'use_statements' => new UseStatementGenerator([
                $modelClassNameDetails->getFullName(),
                ServiceEntityRepository::class,
                ManagerRegistry::class,
                QueryBuilder::class
            ]),
            'entity_class_name' => $modelClassNameDetails->getShortName()
        ];

        $templatePath = __DIR__.'/../Resources/skeleton/model/Repository.tpl.php';
        $generator->generateClass(
            $classNameDetails->getFullName(),
            $templatePath,
            $templateVars,
        );

        $generator->writeChanges();
    }

    // Helper methods

    /**
     * Returns whether the user wants to generate entity mappings as PHP attributes.
     *
     * @param InputInterface $input
     * @return bool
     */
    private function shouldGenerateEntityAttributes(InputInterface $input): bool
    {
        return 'attributes' === $input->getOption('entity');
    }

    /**
     * Returns whether the user wants to generate entity mappings as XML.
     *
     * @param InputInterface $input
     * @return bool
     */
    private function shouldGenerateEntityXml(InputInterface $input): bool
    {
        return 'xml' === $input->getOption('entity');
    }

    /**
     * Returns whether the user wants to generate entity mappings.
     *
     * @param InputInterface $input
     * @return bool
     */
    private function shouldGenerateEntity(InputInterface $input): bool
    {
        return (
            $this->shouldGenerateEntityAttributes($input) ||
            $this->shouldGenerateEntityXml($input)
        );
    }

    /**
     * Returns whether the user wants to generate an identity value object for the model.
     *
     * @param InputInterface $input
     * @return bool
     */
    private function shouldGenerateId(InputInterface $input): bool
    {
        return 'id' === $input->getOption('with-identity');
    }

    /**
     * Returns whether the user wants to generate a UUID value object for the model.
     *
     * @param InputInterface $input
     * @return bool
     */
    private function shouldGenerateUuid(InputInterface $input): bool
    {
        return 'uuid' === $input->getOption('with-identity');
    }

    /**
     * Returns whether the user wants to generate an identity value object for the model.
     *
     * @param InputInterface $input
     * @return bool
     */
    private function shouldGenerateIdentity(InputInterface $input): bool
    {
        return (
            $this->shouldGenerateId($input) ||
            $this->shouldGenerateUuid($input)
        );
    }
}
