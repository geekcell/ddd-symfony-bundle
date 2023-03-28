<?php

declare(strict_types=1);

namespace GeekCell\DddBundle\Maker\ApiPlatform;

use Assert;
use GeekCell\DddBundle\Maker\AbstractBaseConfigUpdater;

class ApiPlatformConfigUpdater extends AbstractBaseConfigUpdater
{
    /**
     * @param string $yamlSource
     * @param string $path
     * @return string
     */
    public function addCustomPath(string $yamlSource, string $path): string
    {
        $data = $this->read($yamlSource);

        /** @var array|null $currentPaths */
        $currentPaths = $data['api_platform']['mapping']['paths'];
        $data['api_platform']['mapping']['paths'] = array_unique(array_merge($currentPaths, [$path]));

        return $this->write($data);
    }
}
