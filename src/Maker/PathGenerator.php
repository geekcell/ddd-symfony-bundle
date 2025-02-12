<?php

namespace GeekCell\DddBundle\Maker;

use function Symfony\Component\String\u;

class PathGenerator
{
    private readonly string $basePath;

    public const DEFAULT_BASE_PATH = 'src/';

    public function __construct(string $basePath)
    {
        if ($basePath && !u($basePath)->endsWith('/')) {
            $basePath .= '/';
        }

        $this->basePath = u($basePath)->trimPrefix(self::DEFAULT_BASE_PATH);
    }

    public function namespacePrefix(string $namespacePrefix): string
    {
        if ($this->basePath !== '' && $this->basePath !== '0') {
            return $this->toNamespace($this->basePath) . $namespacePrefix;
        }

        return $namespacePrefix;
    }

    public function path(string $prefix, string $suffix): string
    {
        $prefix = u($prefix)->trimSuffix('/');
        $suffix = u($suffix)->trimPrefix('/');

        if ($this->basePath !== '' && $this->basePath !== '0') {
            return $prefix . '/' . $this->basePath . $suffix;
        }

        return $prefix . '/' . $suffix;
    }

    private function toNamespace(string $basePath): string
    {
        return u($basePath)->replace('/', '\\');
    }
}
