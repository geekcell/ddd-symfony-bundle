# Symfony Bundle for DDD

Various additions for [domain driven development](https://martinfowler.com/tags/domain%20driven%20design.html) inside Symfony.

## Installation

To use this bundle, require it in Composer

```bash
composer require geekcell/ddd-bundle
```

## Aggregate Root

Extend from `AggregateRoot` to record and commit domain events. Domain events must implement the (marker) interface `DomainEvent`. Events will be dispatched via the currently configured Symfony event dispatcher.

### Example Usage

```php
use GeekCell\DDDBundle\Domain\Event\DomainEvent;
use GeekCell\DDDBundle\Domain\Model\AggregateRoot;

class OrderPlacedEvent implements DomainEvent
{
    ...
}

class Order extends AggregateRoot
{
    public function save(): void
    {
        $this->record(new OrderPlacedEvent());
    }

    ...
}

$order = new Order();
$order->save();
$order->commit(); // <- Events will be dispatched
```

_Hint: If you want to dispatch an event directly, use `AggregateRoot::dispatch()` instead of `AggregateRoot::record()`._

If you cannot (or don't want to) extend from `AggregateRoot`, you can alternative use `DispatchableTrait` to add dispatching capabilities to any class. The former is however the recommended way.

## Supporting Tools

### Facades

Facades are heavily inspired by [Laravel's Facades](https://laravel.com/docs/facades) and are more or less singletons on steroids. They are basically a shortcut to services inside the DIC.

```php
use GeekCell\DDDBundle\Support\Facades\EventDispatcher;

EventDispatcher::dispatch($someEvent);

// same as: $container->get('event_dispatcher')->dispatch($someEvent);
```

You can create your own facades by extending from `Facade` and implementing the `Facade::getFacadeAccessor()` method to return the DIC service alias.

```php
use GeekCell\DDDBundle\Support\Facades\Facade;

class Logger extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'logger';
    }
}
```

Although facades are better testable than regular singletons, it is highly recommended to only use them sparringly and always prefer normal dependency injection when possible.
