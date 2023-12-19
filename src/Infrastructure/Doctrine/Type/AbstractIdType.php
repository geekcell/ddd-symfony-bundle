<?php

declare(strict_types=1);

namespace GeekCell\DddBundle\Infrastructure\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\IntegerType;
use GeekCell\Ddd\Domain\ValueObject\Id;

abstract class AbstractIdType extends IntegerType
{
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?int
    {
        if (is_int($value)) {
            return $value;
        }

        if ($value === null) {
            return null;
        }

        if (!$value instanceof Id) {
            throw ConversionException::conversionFailedInvalidType(
                $value,
                $this->getName(),
                [Id::class],
            );
        }

        return intval($value->getValue());
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?Id
    {
        if ($value === null) {
            return null;
        }

        $idType = $this->getIdType();
        if (!is_subclass_of($idType, Id::class)) {
            throw ConversionException::conversionFailedUnserialization(
                $this->getName(),
                sprintf(
                    "'%s' must be a subclass of '%s'",
                    $idType,
                    Id::class,
                ),
            );
        }

        return new $idType($value);
    }

    /**
     * @return class-string<Id>
     */
    abstract protected function getIdType(): string;
}
