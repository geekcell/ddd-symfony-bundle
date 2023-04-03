<?php

declare(strict_types=1);

namespace GeekCell\DddBundle\Maker;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\State\ProviderInterface;
use ApiPlatform\Symfony\Bundle\ApiPlatformBundle;
use GeekCell\Ddd\Contracts\Application\CommandBus;
use GeekCell\Ddd\Contracts\Application\QueryBus;
use GeekCell\DddBundle\Maker\ApiPlatform\ApiPlatformConfigUpdater;
use Symfony\Bundle\MakerBundle\Exception\RuntimeCommandException;
use Symfony\Bundle\MakerBundle\FileManager;
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
use Symfony\Component\Console\Input\InputOption;

final class MakeResource extends AbstractMaker implements InputAwareMakerInterface
{
    const NAMESPACE_PREFIX = 'Infrastructure\\ApiPlatform\\';
    const CONFIG_PATH = 'config/packages/api_platform.yaml';
    const CONFIG_PATH_XML = 'src/Infrastructure/ApiPlatform/Config';

    const CONFIG_FLAVOR_ATTRIBUTE = 'attribute';
    const CONFIG_FLAVOR_XML = 'xml';

    public function __construct(
        private FileManager $fileManager,
        private ApiPlatformConfigUpdater $configUpdater
    ) {}

    /**
     * @inheritDoc
     */
    public static function getCommandName(): string
    {
        return 'make:ddd:resource';
    }

    /**
     * @inheritDoc
     */
    public static function getCommandDescription(): string
    {
        return 'Creates a new API Platform resource';
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
                'The name of the model class to create the resource for (e.g. <fg=yellow>Customer</>). Model must exist already.',
            )
            ->addOption(
                'config',
                null,
                InputOption::VALUE_REQUIRED,
                'Config flavor to create (attribute|xml).',
                null
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
        // Check for bundle to make sure API Platform package is installed.
        // Then check if the new ApiResource class in the Metadata namespace exists.
        //  -> Was only introduced in v2.7.
        if (!class_exists(ApiPlatformBundle::class) || !class_exists(ApiResource::class)) {
            throw new RuntimeCommandException('This command requires Api Platform >2.7 to be installed.');
        }

        if (null === $input->getOption('config')) {
            $configFlavor = $io->choice(
                'Config flavor to create (attribute|xml)',
                [
                    'attribute' => 'PHP attributes',
                    'xml' => 'XML mapping',
                ],
                'attribute'
            );
            $input->setOption('config', $configFlavor);
        }
    }

    /**
     * @inheritDoc
     */
    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $baseName = $input->getArgument('name');
        $configFlavor = $input->getOption('config');

        $modelClassNameDetails = $generator->createClassNameDetails(
            $baseName,
            'Domain\\Model\\',
            '',
        );

        if (!class_exists($modelClassNameDetails->getFullName())) {
            throw new RuntimeCommandException("Could not find model {$modelClassNameDetails->getFullName()}!");
        }

        $identityClassNameDetails = $this->ensureIdentity($modelClassNameDetails, $generator);

        $classNameDetails = $generator->createClassNameDetails(
            $baseName,
            self::NAMESPACE_PREFIX . 'Resource',
            'Resource',
        );

        $this->ensureConfig($generator, $configFlavor);

        $providerClassNameDetails = $generator->createClassNameDetails(
            $baseName,
            self::NAMESPACE_PREFIX . 'State',
            'Provider',
        );
        $this->generateProvider($providerClassNameDetails, $generator);

        $processorClassNameDetails = $generator->createClassNameDetails(
            $baseName,
            self::NAMESPACE_PREFIX . 'State',
            'Processor',
        );
        $this->generateProcessor($processorClassNameDetails, $generator);

        $classesToImport = [$modelClassNameDetails->getFullName()];
        if ($configFlavor === self::CONFIG_FLAVOR_ATTRIBUTE) {
            $classesToImport[] = ApiResource::class;
            $classesToImport[] = ApiProperty::class;
            $classesToImport[] = $providerClassNameDetails->getFullName();
            $classesToImport[] = $processorClassNameDetails->getFullName();
        }

        $configureWithUuid = str_contains(strtolower($identityClassNameDetails->getShortName()), 'uuid');
        $templateVars = [
            'use_statements' => new UseStatementGenerator($classesToImport),
            'entity_class_name' => $modelClassNameDetails->getShortName(),
            'provider_class_name' => $providerClassNameDetails->getShortName(),
            'processor_class_name' => $processorClassNameDetails->getShortName(),
            'configure_with_attributes' => $configFlavor === self::CONFIG_FLAVOR_ATTRIBUTE,
            'configure_with_uuid' => $configureWithUuid,
        ];

        $generator->generateClass(
            $classNameDetails->getFullName(),
            __DIR__.'/../Resources/skeleton/resource/Resource.tpl.php',
            $templateVars,
        );

        if ($configFlavor === self::CONFIG_FLAVOR_XML) {
            $targetPathResourceConfig = self::CONFIG_PATH_XML . '/' . $classNameDetails->getShortName() . '.xml';
            $generator->generateFile(
                $targetPathResourceConfig,
                __DIR__.'/../Resources/skeleton/resource/ResourceXmlConfig.tpl.php',
                [
                    'class_name' => $classNameDetails->getFullName(),
                    'entity_short_class_name' => $modelClassNameDetails->getShortName(),
                    'provider_class_name' => $providerClassNameDetails->getFullName(),
                    'processor_class_name' => $processorClassNameDetails->getFullName(),
                ]
            );

            $targetPathPropertiesConfig = self::CONFIG_PATH_XML . '/' . $classNameDetails->getShortName() . 'Properties.xml';
            $generator->generateFile(
                $targetPathPropertiesConfig,
                __DIR__.'/../Resources/skeleton/resource/PropertiesXmlConfig.tpl.php',
                [
                    'class_name' => $classNameDetails->getFullName(),
                    'identifier_field_name' => $configureWithUuid ? 'uuid' : 'id',
                ]
            );
        }

        $generator->writeChanges();

        $this->writeSuccessMessage($io);
    }

    /**
     * ensure custom resource path(s) are added to config
     *
     * @param Generator $generator
     * @param string $configFlavor
     * @return void
     */
    private function ensureConfig(Generator $generator, string $configFlavor): void
    {
        $customResourcePath = '%kernel.project_dir%/src/Infrastructure/ApiPlatform/Resource';
        $customConfigPath = '%kernel.project_dir%/' . self::CONFIG_PATH_XML;

        if (!$this->fileManager->fileExists(self::CONFIG_PATH)) {
            $generator->generateFile(
                self::CONFIG_PATH,
                __DIR__ . '/../Resources/skeleton/resource/ApiPlatformConfig.tpl.php',
                [
                    'path' => $customResourcePath,
                ]
            );

            $generator->writeChanges();
        }

        $newYaml = $this->configUpdater->addCustomPath(
            $this->fileManager->getFileContents(self::CONFIG_PATH),
            $customResourcePath
        );

        if ($configFlavor === self::CONFIG_FLAVOR_XML) {
            $newYaml = $this->configUpdater->addCustomPath($newYaml, $customConfigPath);
        }

        $generator->dumpFile(self::CONFIG_PATH, $newYaml);

        $generator->writeChanges();
    }

    /**
     * @param ClassNameDetails $providerClassNameDetails
     * @param Generator $generator
     * @return void
     */
    private function generateProvider(ClassNameDetails $providerClassNameDetails, Generator $generator)
    {
        $templateVars = [
            'use_statements' => new UseStatementGenerator([
                ProviderInterface::class,
                QueryBus::class,
                Operation::class
            ]),
        ];

        $generator->generateClass(
            $providerClassNameDetails->getFullName(),
            __DIR__.'/../Resources/skeleton/resource/Provider.tpl.php',
            $templateVars,
        );

        $generator->writeChanges();
    }

    /**
     * @param ClassNameDetails $processorClassNameDetails
     * @param Generator $generator
     * @return void
     */
    private function generateProcessor(ClassNameDetails $processorClassNameDetails, Generator $generator)
    {
        $templateVars = [
            'use_statements' => new UseStatementGenerator([
                ProcessorInterface::class,
                CommandBus::class,
                Operation::class
            ]),
        ];

        $generator->generateClass(
            $processorClassNameDetails->getFullName(),
            __DIR__.'/../Resources/skeleton/resource/Processor.tpl.php',
            $templateVars,
        );

        $generator->writeChanges();
    }

    /**
     * @param ClassNameDetails $modelClassNameDetails
     * @param Generator $generator
     * @return ClassNameDetails
     */
    private function ensureIdentity(ClassNameDetails $modelClassNameDetails, Generator $generator): ClassNameDetails
    {
        $idEntity = $generator->createClassNameDetails(
            $modelClassNameDetails->getShortName(),
            'Domain\\Model\\ValueObject\\Identity',
            'Id',
        );

        if (class_exists($idEntity->getFullName())) {
            return $idEntity;
        }

        $uuidEntity = $generator->createClassNameDetails(
            $modelClassNameDetails->getShortName(),
            'Domain\\Model\\ValueObject\\Identity',
            'Uuid',
        );

        if (class_exists($uuidEntity->getFullName())) {
            return $uuidEntity;
        }

        throw new RuntimeCommandException("Could not find model identity for {$modelClassNameDetails->getFullName()}. Checked for id class ({$idEntity->getFullName()}) and uuid class ({$uuidEntity->getFullName()})!");
    }
}
