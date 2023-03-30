<?php

declare(strict_types=1);

namespace GeekCell\DddBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * @codeCoverageIgnozre
 *
 * @package GeekCell\DddBundle\DependencyInjection
 * @codeCoverageIgnore
 */
class GeekCellDddExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $locator = new FileLocator(__DIR__ . '/../../config');
        $loader = new YamlFileLoader($container, $locator);

        $loader->load('services.yaml');
    }
}
