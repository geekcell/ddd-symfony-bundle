<?php

declare(strict_types=1);

namespace GeekCell\DddBundle\Infrastructure\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\GuidType;
use GeekCell\Ddd\Domain\ValueObject\Uuid;

abstract class AbstractUuidType extends GuidType
{
    /**
     * @inheritDoc
     * @return string
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (is_string($value)) {
            return $value;
        }

        if ($value === null) {
            return null;
        }

        if (!$value instanceof Uuid) {
            throw ConversionException::conversionFailedInvalidType(
                $value,
                $this->getName(),
                [Uuid::class],
            );
        }

        return strval($value);
    }

    /**
     * @inheritDoc
     * @return Uuid
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }

        $idType = $this->getIdType();
        if (!is_subclass_of($idType, Uuid::class)) {
            throw ConversionException::conversionFailedUnserialization(
                $this->getName(),
                sprintf(
                    "'%s' must be a subclass of '%s'",
                    $idType,
                    Uuid::class,
                ),
            );
        }

        return new $idType($value);
    }

    /**
     * @return class-string<Uuid> $entityType
     */
    abstract protected function getIdType(): string;
}
