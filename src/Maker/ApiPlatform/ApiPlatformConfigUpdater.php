<?php

declare(strict_types=1);

namespace GeekCell\DddBundle\Maker\ApiPlatform;

use Assert;
use GeekCell\DddBundle\Maker\AbstractBaseConfigUpdater;

class ApiPlatformConfigUpdater extends AbstractBaseConfigUpdater
{
    public function addCustomPath(string $yamlSource, string $path): string
    {
        $data = $this->read($yamlSource);

        if (isset($data['api_platform']['mapping']) && is_array($data['api_platform']['mapping'])) {
            $currentPaths = $data['api_platform']['mapping']['paths'] ?? [];
            $data['api_platform']['mapping']['paths'] = array_unique(array_merge($currentPaths, [$path]));
        }

        return $this->write($data);
    }
}
