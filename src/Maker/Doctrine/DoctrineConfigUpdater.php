<?php

declare(strict_types=1);

namespace GeekCell\DddBundle\Maker\Doctrine;

use Assert;
use Symfony\Bundle\MakerBundle\Util\YamlSourceManipulator;

class DoctrineConfigUpdater
{
    /**
     * @var null|YamlSourceManipulator
     */
    private ?YamlSourceManipulator $manipulator;

    /**
     * Registers a custom DBAL mapping type.
     *
     * @param string $yamlSource    The contents of current doctrine.yaml
     * @param string $identifier    The identifier of the custom mapping type
     * @param string $mappingClass  The class name of the custom mapping type
     *
     * @return string The updated doctrine.yaml contents
     */
    public function addCustomDBALMappingType(string $yamlSource, string $identifier, string $mappingClass): string
    {
        $data = $this->createYamlSourceManipulator($yamlSource);
        $data['doctrine']['dbal']['types'][$identifier] = $mappingClass;

        return $this->getYamlContentsFromData($data);
    }

    /**
     * Updates the default entity mapping configuration.
     *
     * @param string $yamlSource    The contents of current doctrine.yaml
     * @param string $mappingType   The type of the mapping (xml or annotation)
     * @param string $directory     The directory where the mapping files are located
     *
     * @return string The updated doctrine.yaml contents
     */
    public function updateORMDefaultEntityMapping(string $yamlSource, string $mappingType, string $directory): string
    {
        Assert\Assertion::inArray($mappingType, ['xml', 'annotation'], 'Invalid mapping type: %s');

        $data = $this->createYamlSourceManipulator($yamlSource);
        $data['doctrine']['orm']['mappings']['App']['type'] = $mappingType;
        $data['doctrine']['orm']['mappings']['App']['dir'] = $directory;
        $data['doctrine']['orm']['mappings']['App']['prefix'] = 'App\Domain\Model';
        $data['doctrine']['orm']['mappings']['App']['alias'] = 'App';
        $data['doctrine']['orm']['mappings']['App']['is_bundle'] = false;

        return $this->getYamlContentsFromData($data);
    }

    /**
     * Creates a YamlSourceManipulator from a YAML source.
     *
     * @param string $yamlSource
     * @return array<string, string|string[]>
     */
    private function createYamlSourceManipulator(string $yamlSource): array
    {
        $this->manipulator = new YamlSourceManipulator($yamlSource);
        return $this->manipulator->getData();
    }

    /**
     * Returns the updated YAML contents for the given data.
     *
     * @param array<string, string|string[]> $yamlData
     * @return string
     */
    private function getYamlContentsFromData(array $yamlData): string
    {
        Assert\Assertion::notNull($this->manipulator);
        $this->manipulator->setData($yamlData);

        return $this->manipulator->getContents();
    }
}
