<?php

declare(strict_types=1);

namespace GeekCell\DddBundle\Tests\Unit\Infrastructure\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use GeekCell\Ddd\Domain\ValueObject\Uuid;
use GeekCell\DddBundle\Infrastructure\Doctrine\Type\AbstractUuidType;
use Mockery;
use PHPUnit\Framework\TestCase;

class FooUuid extends Uuid
{
}

class FooUuidType extends AbstractUuidType
{
    public function getName()
    {
        return 'foo_uuid';
    }

    protected function getIdType(): string
    {
        return FooUuid::class;
    }
}

class AbstractUuidTypeTest extends TestCase
{
    public function testConvertToDatabaseValue(): void
    {
        // Given
        $uuidString = '00000000-0000-0000-0000-000000000000';
        $platform = Mockery::mock(AbstractPlatform::class);
        $type = new FooUuidType();

        // When
        $result = $type->convertToDatabaseValue(
            new FooUuid($uuidString),
            $platform,
        );

        // Then
        $this->assertSame($uuidString, $result);
    }

    public function testConvertToPhpValue(): void
    {
        // Given
        $uuidString = '00000000-0000-0000-0000-000000000000';
        $platform = Mockery::mock(AbstractPlatform::class);
        $type = new FooUuidType();

        // When
        $result = $type->convertToPHPValue($uuidString, $platform);

        // Then
        $this->assertInstanceOf(FooUuid::class, $result);
        $this->assertSame($uuidString, $result->getValue());
    }
}
