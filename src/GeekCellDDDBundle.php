<?php

declare(strict_types=1);

namespace GeekCell\DDDBundle;

use GeekCell\DDDBundle\DependencyInjection\GeekCellDDDExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

/**
 * Class GeekCellDDDBundle.
 * This is the main bundle class.
 *
 * @package GeekCell\DDDBundle
 * @codeCoverageIgnore
 */

class GeekCellDDDBundle extends AbstractBundle
{
    public function getContainerExtension(): null|ExtensionInterface
    {
        return new GeekCellDDDExtension();
    }
}
