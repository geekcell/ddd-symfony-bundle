# Symfony Bundle for DDD

This Symfony bundle augments [geekcell/php-ddd](https://github.com/geekcell/php-ddd) with framework-specific implementations to enable seamless [domain driven design](https://martinfowler.com/tags/domain%20driven%20design.html) in a familiar environment.

## Installation

To use this bundle, require it in Composer

```bash
composer require geekcell/ddd-bundle
```

## Quickstart

- [Aggregate Root](#aggregate-root)
- [Repositories](#repositories)
- [Command & Query Bus](#command--query-bus)
- [Supporting Tools](#supporting-tools)

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

## Repositories

_coming soon..._

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

## Generator Commands

This bundle adds several [maker bundle](https://symfony.com/bundles/SymfonyMakerBundle/current/index.html) commands to generate commonly used components.

### Model / Repository

This command can be used to generate:

- The domain model class.
- A repository class for the model.
- The model's identity class as value object (optional).
- A Doctrine database entity configuration, either as annotation or separate config file (optional).
- A custom Doctrine type for the model's identity class (optional).

#### Command Output

```bash
Description:
  Creates a new domain model class

Usage:
  make:ddd:model [options] [--] [<name>]

Arguments:
  name                               The name of the model class (e.g. Customer)

Options:
      --aggregate-root               Marks the model as aggregate root
      --entity=ENTITY                Use this model as Doctrine entity
      --with-identity=WITH-IDENTITY  Whether an identity value object should be created
      --with-suffix                  Adds the suffix "Model" to the model class name
```

### Query / Command

These commands can be used to generate:

- A query and query handler class.
- A command and command handler class.

The query / command generated is just an empty class. The handler class is registered as a message handler for the configured [Symfony Messenger](https://symfony.com/doc/current/messenger.html).

#### Command Output

```bash
Description:
  Creates a new query|command class and handler

Usage:
  make:ddd:query|command [<name>]

Arguments:
  name                     The name of the query|command class (e.g. Customer)
```

### Controller

This command can be used to generate a controller with optional `QueryBus` and `CommandBus` dependencies.

#### Command Output

```bash
Description:
  Creates a new controller class

Usage:
  make:ddd:controller [options] [--] [<name>]

Arguments:
  name                       The name of the model class (e.g. Customer)

Options:
      --include-query-bus    Add a query bus dependency
      --include-command-bus  Add a command bus dependency
```

### Resource

This command can be used to generate an [Api Platform](https://api-platform.com/) resource. Minimum required version is [2.7](https://api-platform.com/docs/core/upgrade-guide/#api-platform-2730) for the PHP attributes support.

#### Command Output

```bash
Description:
  Creates a new API Platform resource

Usage:
  make:ddd:resource [options] [--] [<name>]

Arguments:
  name            The name of the model class to create the resource for (e.g. Customer). Model must exist already.

Options:
      --config    Config flavor to create (attribute|xml).
```
