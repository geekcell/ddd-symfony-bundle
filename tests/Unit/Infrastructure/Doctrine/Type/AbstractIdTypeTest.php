<?php

declare(strict_types=1);

namespace GeekCell\DddBundle\Tests\Unit\Infrastructure\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use GeekCell\Ddd\Domain\ValueObject\Id;
use GeekCell\DddBundle\Infrastructure\Doctrine\Type\AbstractIdType;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * Test fixture.
 */
class FooId extends Id
{
}

/**
 * Test subject.
 */
class FooIdType extends AbstractIdType
{
    public function getName()
    {
        return 'foo_id';
    }

    protected function getIdType(): string
    {
        return FooId::class;
    }
}

class AbstractIdTypeTest extends TestCase
{
    public function testConvertToDatabaseValue(): void
    {
        // Given
        $intId = 42;
        $type = new FooIdType();
        $platform = Mockery::mock(AbstractPlatform::class);

        // When
        $result = $type->convertToDatabaseValue(new FooId($intId), $platform);

        // Then
        $this->assertSame($intId, $result);
    }

    public function testConvertToDatabaseValueScalar(): void
    {
        // Given
        $intId = 42;
        $type = new FooIdType();
        $platform = Mockery::mock(AbstractPlatform::class);

        // When
        $result = $type->convertToDatabaseValue($intId, $platform);

        // Then
        $this->assertSame($intId, $result);
    }

    public function testConvertToDatabaseValueInvalidType(): void
    {
        // Given
        $type = new FooIdType();
        $platform = Mockery::mock(AbstractPlatform::class);

        // Then
        $this->expectException(ConversionException::class);

        // When
        $type->convertToDatabaseValue('foo', $platform);
    }

    public function testConvertToDatabaseValueNull(): void
    {
        // Given
        $type = new FooIdType();
        $platform = Mockery::mock(AbstractPlatform::class);

        // When
        $result = $type->convertToDatabaseValue(null, $platform);

        // Then
        $this->assertNull($result);
    }

    public function testConvertToPhpValue(): void
    {
        // Given
        $intId = 42;
        $type = new FooIdType();
        $platform = Mockery::mock(AbstractPlatform::class);

        // When
        $result = $type->convertToPHPValue($intId, $platform);

        // Then
        $this->assertInstanceOf(FooId::class, $result);
        $this->assertSame($intId, $result->getValue());
    }

    public function testConvertToPhpValueNull(): void
    {
        // Given
        $type = new FooIdType();
        $platform = Mockery::mock(AbstractPlatform::class);

        // When
        $result = $type->convertToPHPValue(null, $platform);

        // Then
        $this->assertNull($result);
    }
}
