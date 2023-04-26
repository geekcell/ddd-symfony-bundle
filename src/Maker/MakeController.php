<?php

declare(strict_types=1);

namespace GeekCell\DddBundle\Maker;

use GeekCell\Ddd\Contracts\Application\CommandBus;
use GeekCell\Ddd\Contracts\Application\QueryBus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\MakerBundle\FileManager;
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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function Symfony\Component\String\u;

final class MakeController extends AbstractMaker implements InputAwareMakerInterface
{
    const NAMESPACE_PREFIX = 'Infrastructure\\Http\\Controller\\';
    const CONFIG_PATH = 'config/routes/ddd.yaml';

    public function __construct(private FileManager $fileManager)
    {
    }

    /**
     * @inheritDoc
     */
    public static function getCommandName(): string
    {
        return 'make:ddd:controller';
    }

    /**
     * @inheritDoc
     */
    public static function getCommandDescription(): string
    {
        return 'Creates a new controller class';
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
                'The name of the controller class (e.g. <fg=yellow>Customer</>)',
            )
            ->addOption(
                'include-query-bus',
                null,
                InputOption::VALUE_REQUIRED,
                'Add a query bus dependency.',
                null
            )
            ->addOption(
                'include-command-bus',
                null,
                InputOption::VALUE_REQUIRED,
                'Add a command bus dependency.',
                null
            )
            ->addOption(
                'base-path',
                null,
                InputOption::VALUE_REQUIRED,
                'Base path from which to generate model & config.',
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
        if (null === $input->getOption('include-query-bus')) {
            $includeQueryBus = $io->confirm(
                'Do you want to add a query bus dependency?',
                false,
            );
            $input->setOption('include-query-bus', $includeQueryBus);
        }

        if (null === $input->getOption('include-command-bus')) {
            $includeCommandBus = $io->confirm(
                'Do you want to add a command bus dependency?',
                false,
            );
            $input->setOption('include-command-bus', $includeCommandBus);
        }

        if (null === $input->getOption('base-path')) {
            $basePath = $io->ask(
                'Which base path should be used? Default is "' . PathGenerator::DEFAULT_BASE_PATH . '"',
                PathGenerator::DEFAULT_BASE_PATH,
            );
            $input->setOption('base-path', $basePath);
        }
    }

    /**
     * @inheritDoc
     */
    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $pathGenerator = new PathGenerator($input->getOption('base-path'));

        $classNameDetails = $generator->createClassNameDetails(
            $input->getArgument('name'),
            $pathGenerator->namespacePrefix(self::NAMESPACE_PREFIX),
            'Controller',
        );

        $classesToImport = [
            AbstractController::class,
            Route::class,
            Response::class
        ];

        $routeName = lcfirst($input->getArgument('name'));
        $templateVars = [
            'route_name' => $routeName,
            'route_name_snake' => u($routeName)->snake()->lower(),
            'dependencies' => []
        ];

        if ($input->getOption('include-query-bus')) {
            $templateVars['dependencies'][] = 'private QueryBus $queryBus';
            $classesToImport[] = QueryBus::class;
        }

        if ($input->getOption('include-command-bus')) {
            $templateVars['dependencies'][] = 'private CommandBus $commandBus';
            $classesToImport[] = CommandBus::class;
        }

        $templateVars['use_statements'] = new UseStatementGenerator($classesToImport);

        $templatePath = __DIR__.'/../Resources/skeleton/controller/Controller.tpl.php';
        $generator->generateClass(
            $classNameDetails->getFullName(),
            $templatePath,
            $templateVars,
        );

        // ensure controller config has been created
        if (!$this->fileManager->fileExists(self::CONFIG_PATH)) {
            $templatePathConfig = __DIR__ . '/../Resources/skeleton/controller/RouteConfig.tpl.php';
            $generator->generateFile(
                self::CONFIG_PATH,
                $templatePathConfig,
                [
                    'path' => $pathGenerator->path('../../src/', 'Infrastructure/Http/Controller/'),
                    'namespace' => str_replace('\\' . $classNameDetails->getShortName(), '', $classNameDetails->getFullName())
                ]
            );
        }

        $generator->writeChanges();

        $this->writeSuccessMessage($io);
    }
}
