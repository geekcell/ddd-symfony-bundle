<?php

declare(strict_types=1);

namespace GeekCell\DddBundle\Maker;

use Assert;
use Assert\AssertionFailedException;
use Symfony\Bundle\MakerBundle\Util\YamlSourceManipulator;

abstract class AbstractBaseConfigUpdater
{
    private ?YamlSourceManipulator $manipulator;

    /**
     * Creates a YamlSourceManipulator from a YAML source.
     *
     * @param string $yamlSource
     * @return array<string, mixed>
     */
    protected function read(string $yamlSource): array
    {
        $this->manipulator = new YamlSourceManipulator($yamlSource);
        return $this->manipulator->getData();
    }

    /**
     * Returns the updated YAML contents for the given data.
     *
     * @param array<string, mixed> $yamlData
     * @return string
     * @throws AssertionFailedException
     */
    protected function write(array $yamlData): string
    {
        Assert\Assertion::notNull($this->manipulator);
        $this->manipulator->setData($yamlData);

        return $this->manipulator->getContents();
    }
}
