<?php

namespace GeekCell\DddBundle\Tests\Integration;

use GeekCell\DddBundle\GeekCellDddBundle;
use Symfony\Bundle\MakerBundle\Test\MakerTestKernel;

class GeekCellMakerTestKernel extends MakerTestKernel
{
    public function registerBundles(): iterable
    {
        $bundles = parent::registerBundles();
        $bundles[] = new GeekCellDddBundle();

        return $bundles;
    }
}
