<?php

namespace GeekCell\DddBundle\Tests\Unit\Maker;

use PHPUnit\Framework\Attributes\DataProvider;
use GeekCell\DddBundle\Maker\PathGenerator;
use PHPUnit\Framework\TestCase;

class PathGeneratorTest extends TestCase
{
    #[DataProvider('provideNamespacePrefixData')]
    public function testNamespacePrefix(string $basePath, string $namespacePrefix, string $expected): void
    {
        $pathGenerator = new PathGenerator($basePath);

        $this->assertEquals($expected, $pathGenerator->namespacePrefix($namespacePrefix));
    }

    /**
     * @return array<int, string[]>
     */
    public static function provideNamespacePrefixData(): array
    {
        return [
            ['', 'Domain\\Model\\', 'Domain\\Model\\'],
            ['src', 'Domain\\Model\\', 'Domain\\Model\\'],
            ['src/', 'Domain\\Model\\', 'Domain\\Model\\'],
            ['src/Foo', 'Domain\\Model\\', 'Foo\\Domain\\Model\\'],
            ['src/Foo/Bar', 'Domain\\Model\\', 'Foo\\Bar\\Domain\\Model\\'],
        ];
    }

    #[DataProvider('providePathData')]
    public function testPath(string $basePath, string $prefix, string $suffix, string $expected): void
    {
        $pathGenerator = new PathGenerator($basePath);

        $this->assertEquals($expected, $pathGenerator->path($prefix, $suffix));
    }

    /**
     * @return array<int, string[]>
     */
    public static function providePathData(): array
    {
        return [
            ['', '%kernel.project_dir%/src', 'Domain/Model', '%kernel.project_dir%/src/Domain/Model'],
            ['', '%kernel.project_dir%/src/', 'Domain/Model', '%kernel.project_dir%/src/Domain/Model'],
            ['', '%kernel.project_dir%/src/', '/Domain/Model', '%kernel.project_dir%/src/Domain/Model'],
            ['src', '%kernel.project_dir%/src', 'Domain/Model', '%kernel.project_dir%/src/Domain/Model'],
            ['src/', '%kernel.project_dir%/src', 'Domain/Model', '%kernel.project_dir%/src/Domain/Model'],
            ['src/Foo', '%kernel.project_dir%/src', 'Domain/Model', '%kernel.project_dir%/src/Foo/Domain/Model'],
            ['src/Foo/Bar', '%kernel.project_dir%/src', 'Domain/Model', '%kernel.project_dir%/src/Foo/Bar/Domain/Model'],
            ['', '/src', 'Infrastructure/Doctrine/ORM/Mapping', '/src/Infrastructure/Doctrine/ORM/Mapping'],
            ['src/', '/src', 'Infrastructure/Doctrine/ORM/Mapping', '/src/Infrastructure/Doctrine/ORM/Mapping'],
            ['src/Foo', '/src', 'Infrastructure/Doctrine/ORM/Mapping', '/src/Foo/Infrastructure/Doctrine/ORM/Mapping'],
        ];
    }
}
