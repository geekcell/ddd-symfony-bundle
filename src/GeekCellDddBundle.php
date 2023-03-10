<?php

declare(strict_types=1);

namespace GeekCell\DddBundle;

use GeekCell\DddBundle\DependencyInjection\GeekCellDddExtension;
use GeekCell\Facade\Facade;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class GeekCellDddBundle.
 * This is the main bundle class.
 *
 * @codeCoverageIgnore
 */
class GeekCellDddBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new GeekCellDddExtension();
    }

    /**
     * {@inheritdoc}
     */
    public function boot(): void
    {
        parent::boot();
        Facade::setContainer($this->container);
    }
}
