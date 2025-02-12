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
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
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
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?Uuid
    {
        if ($value === null) {
            return null;
        }

        $idType = $this->getIdType();

        return new $idType($value);
    }

    /**
     * @return class-string<Uuid>
     */
    abstract protected function getIdType(): string;
}
