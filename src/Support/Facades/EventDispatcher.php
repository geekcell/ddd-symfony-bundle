<?php

declare(strict_types=1);

namespace GeekCell\DddBundle\Support\Facades;

use GeekCell\Facade\Facade;

class EventDispatcher extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor(): string
    {
        return 'event_dispatcher';
    }
}
