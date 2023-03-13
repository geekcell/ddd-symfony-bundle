<?php

declare(strict_types=1);

namespace GeekCell\DddBundle\Maker;

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

final class MakeResource extends AbstractMaker implements InputAwareMakerInterface
{
    const NAMESPACE_PREFIX = 'Infrastructure\\ApiPlatform\\';
    const CONFIG_PATH = 'config/packages/api_platform.yaml';

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
        if (!class_exists(ApiPlatformBundle::class)) {
            throw new RuntimeCommandException('This command requires Api Platform >2.7 to be installed.');
        }
    }

    /**
     * @inheritDoc
     */
    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $baseName = $input->getArgument('name');

        $modelClassNameDetails = $generator->createClassNameDetails(
            $baseName,
            'Domain\\Model\\',
            '',
        );

        if (!class_exists($modelClassNameDetails->getFullName())) {
            throw new RuntimeCommandException("Could not find model {$modelClassNameDetails->getFullName()}!");
        }

        $classNameDetails = $generator->createClassNameDetails(
            $baseName,
            self::NAMESPACE_PREFIX . 'Resource',
            'Resource',
        );

        $this->ensureConfig($generator);

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

        $templateVars = [
            'use_statements' => new UseStatementGenerator([
                ApiResource::class,
                $modelClassNameDetails->getFullName(),
                $providerClassNameDetails->getFullName(),
                $processorClassNameDetails->getFullName(),
            ]),
            'entity_class_name' => $modelClassNameDetails->getShortName(),
            'provider_class_name' => $providerClassNameDetails->getShortName(),
            'processor_class_name' => $processorClassNameDetails->getShortName(),
        ];

        $generator->generateClass(
            $classNameDetails->getFullName(),
            __DIR__.'/../Resources/skeleton/resource/Resource.tpl.php',
            $templateVars,
        );

        $generator->writeChanges();

        $this->writeSuccessMessage($io);
    }

    /**
     * ensure custom resource path is added to config
     *
     * @param Generator $generator
     * @return void
     */
    private function ensureConfig(Generator $generator): void
    {
        $customResourcePath = '%kernel.project_dir%/src/Infrastructure/ApiPlatform/Resource';
        if ($this->fileManager->fileExists(self::CONFIG_PATH)) {
            $newYaml = $this->configUpdater->addCustomPath(
                $this->fileManager->getFileContents(self::CONFIG_PATH),
                $customResourcePath
            );

            $generator->dumpFile(self::CONFIG_PATH, $newYaml);
        } else {
            $generator->generateFile(
                self::CONFIG_PATH,
                __DIR__ . '/../Resources/skeleton/resource/ApiPlatformConfig.tpl.php',
                [
                    'path' => $customResourcePath,
                ]
            );
        }

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
}
