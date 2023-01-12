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
use GeekCell\Ddd\Contracts\Domain\Event as DomainEvent;
use GeekCell\DddBundle\Domain\AggregateRoot;

class OrderPlacedEvent implements DomainEvent
{
    public function __construct(
        private readonly Order $order,
    ) {
    }

    // Getters etc.
}

class Order extends AggregateRoot
{
    public function save(): void
    {
        $this->record(new OrderPlacedEvent($this));
    }

    // ...
}

$order = new Order( /* ... */ );
$order->save();
$order->commit(); // All recorded events will be dispatched and released
```

_Hint: If you want to dispatch an event directly, use `AggregateRoot::dispatch()` instead of `AggregateRoot::record()`._

If you cannot (or don't want to) extend from `AggregateRoot`, you can alternative use `DispatchableTrait` to add dispatching capabilities to any class. The former is however the recommended way.

## Command & Query Bus

You can use `CommandBus` and `QueryBus` as services to implement [CQRS](https://martinfowler.com/bliki/CQRS.html). Internally, both buses will use the [Symfony messenger](https://symfony.com/doc/current/messenger.html) as "backend".

## Example Usage

```php
// src/Application/Query/TopRatedBookQuery.php
use GeekCell\Ddd\Contracts\Application\Query;

class TopRatedBooksQuery implements Query
{
    public function __construct(
        private readonly string $category,
        private readonly int $sinceDays,
        private readonly int $limit = 10,
    ) {
    }

    // Getters etc.
}

// src/Application/Query/TopRatedBookQueryHandler.php
use GeekCell\Ddd\Contracts\Application\QueryHandler;

#[AsMessageHandler]
class TopRatedBookQueryHandler implements QueryHandler
{
    public function __construct(
        private readonly BookRepository $repository,
    ) {
    }

    public function __invoke(TopRatedBookQuery $query)
    {
        $books = $this->repository
            ->findTopRated($query->getCategory(), $query->getSinceDays())
            ->paginate($query->getLimit());

        return $books;
    }
}

// src/Infrastructure/Http/Controller/BookController.php
use GeekCell\Ddd\Contracts\Application\QueryBus;

class BookController extends AbstractController
{
    public function __construct(
        private readonly QueryBus $queryBus,
    ) {
    }

    #[Route('/books/top-rated')]
    public function getTopRated(Request $request)
    {
        $query = new TopRatedBooksQuery( /* extract from request */ );
        $topRatedBooks = $this->queryBus->dispatch($query);

        // ...
    }
}
```

## Supporting Tools

### Facades

Facades are heavily inspired by [Laravel's Facades](https://laravel.com/docs/facades) and are more or less singletons on steroids. They are basically a shortcut to services inside the DIC.

```php
use GeekCell\DddBundle\Support\Facades\EventDispatcher;

EventDispatcher::dispatch($someEvent);

// same as: $container->get('event_dispatcher')->dispatch($someEvent);
```

You can create your own facades by extending from `Facade` and implementing the `Facade::getFacadeAccessor()` method to return the DIC service alias.

```php
use GeekCell\DddBundle\Support\Facades\Facade;

class Logger extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'logger';
    }
}
```

Although facades are better testable than regular singletons, it is highly recommended to only use them sparringly and always prefer normal dependency injection when possible.
