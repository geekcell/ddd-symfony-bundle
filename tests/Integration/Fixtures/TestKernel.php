<?php

declare(strict_types=1);

namespace GeekCell\DddBundle\Tests\Integration\Fixtures;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Kernel;

class TestKernel extends Kernel
{
    public function registerBundles(): iterable
    {
        return [
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \GeekCell\DddBundle\GeekCellDddBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__.'/config/test_config.yaml');
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir().'/GeekCellDddBundleTests/cache';
    }

    public function shutdown(): void
    {
        parent::shutdown();
        (new Filesystem())->remove($this->getCacheDir());
    }
}
