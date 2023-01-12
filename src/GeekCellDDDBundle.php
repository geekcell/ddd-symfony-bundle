<?php

declare(strict_types=1);

namespace GeekCell\DddBundle;

use GeekCell\DddBundle\DependencyInjection\GeekCellDddExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class GeekCellDddBundle.
 * This is the main bundle class.
 *
 * @package GeekCell\DddBundle
 * @codeCoverageIgnore
 */
class GeekCellDddBundle extends Bundle
{
    public function getContainerExtension(): null|ExtensionInterface
    {
        return new GeekCellDddExtension();
    }
}
