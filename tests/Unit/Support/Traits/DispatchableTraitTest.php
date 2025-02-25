<?php

namespace Tests\Unit\Support\Traits;

use GeekCell\Ddd\Contracts\Domain\Event as DomainEvent;
use GeekCell\DddBundle\Support\Facades\EventDispatcher as EventDispatcherFacade;
use GeekCell\DddBundle\Support\Traits\DispatchableTrait;
use Mockery;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

class DispatchableTraitTest extends TestCase
{
    use DispatchableTrait;

    /**
     * @var string
     */
    public const SERVICE_ID = 'event_dispatcher';

    private Container $container;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = new Container();
        EventDispatcherFacade::setContainer($this->container);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        EventDispatcherFacade::clear();
    }

    public function testGetEventDispatcher(): void
    {
        // Given
        $dispatcher = new EventDispatcher();
        $this->container->set(self::SERVICE_ID, $dispatcher);

        // When
        $result = $this->getEventDispatcher();

        // Then
        $this->assertSame($dispatcher, $result);
    }

    public function testDispatch(): void
    {
        // Given
        $this->container->set(self::SERVICE_ID, new EventDispatcher());

        $event = new class () implements DomainEvent {};

        /** @var Mockery\MockInterface $dispatcherMock */
        $dispatcherMock = EventDispatcherFacade::swapMock();
        $dispatcherMock
            ->shouldReceive('dispatch')
            ->once()
            ->with($event)
        ;

        // When
        $this->dispatch($event);

        // Then
        $this->addToAssertionCount(1);
    }
}
