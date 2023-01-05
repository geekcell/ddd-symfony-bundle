<?php

declare(strict_types=1);

namespace GeekCell\DDDBundle\Support\Facades;

/**
 * Class EventDispatcher.
 * This is a facade for the event dispatcher.
 *
 * @package GeekCell\DDDBundle\Support\Facades
 * @codeCoverageIgnore
 */
class EventDispatcher extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'event_dispatcher';
    }
}
