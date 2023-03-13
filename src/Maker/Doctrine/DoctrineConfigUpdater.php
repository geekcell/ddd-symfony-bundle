<?php

declare(strict_types=1);

namespace GeekCell\DddBundle\Maker\Doctrine;

use Assert;
use GeekCell\DddBundle\Maker\AbstractBaseConfigUpdater;

class DoctrineConfigUpdater extends AbstractBaseConfigUpdater
{
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
        $data = $this->read($yamlSource);
        $data['doctrine']['dbal']['types'][$identifier] = $mappingClass;

        return $this->write($data);
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
        Assert\Assertion::inArray($mappingType, ['xml', 'attribute'], 'Invalid mapping type: %s');

        $data = $this->read($yamlSource);
        $data['doctrine']['orm']['mappings']['App']['type'] = $mappingType;
        $data['doctrine']['orm']['mappings']['App']['dir'] = $directory;
        $data['doctrine']['orm']['mappings']['App']['prefix'] = 'App\Domain\Model';
        $data['doctrine']['orm']['mappings']['App']['alias'] = 'App';
        $data['doctrine']['orm']['mappings']['App']['is_bundle'] = false;

        return $this->write($data);
    }
}
