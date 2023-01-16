<?php

namespace Tests\Unit\Support\Traits;

use GeekCell\DddBundle\Support\Facades\EventDispatcher;
use GeekCell\DddBundle\Support\Traits\DispatchableTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher as SymfonyEventDispatcher;

class DispatchableTraitTest extends TestCase
{
    use DispatchableTrait;

    public function tearDown(): void
    {
        parent::tearDown();
        EventDispatcher::clear();
    }

    public function testGetDefaultEventDispatcher(): void
    {
        // Given
        $dispatcher = new SymfonyEventDispatcher();
        EventDispatcher::swap($dispatcher);

        // When
        $default = $this->getEventDispatcher();

        // Then
        $this->assertSame($dispatcher, $default);
    }
}
