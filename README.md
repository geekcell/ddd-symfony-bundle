# Symfony Bundle for DDD

This Symfony bundle augments [geekcell/php-ddd](https://github.com/geekcell/php-ddd) with framework-specific implementations to enable seamless [domain driven design](https://martinfowler.com/tags/domain%20driven%20design.html) in a familiar environment.

---

- [Installation](#installation)
- [Generator Commands](#generator-commands)
- [Building Blocks](#building-blocks)
    - [Model & Repository](#model--repository)
    - [AggregateRoot & Domain Events](#aggregateroot--domain-events)
    - [Command & Query](#command--query)
    - [Controller](#controller)
    - [Resource](#resource)

## Installation

To use this bundle, require it in Composer

```bash
composer require geekcell/ddd-bundle
```

## Generator Commands

This bundle adds several [MakerBundle](https://symfony.com/bundles/SymfonyMakerBundle/current/index.html) commands to generate commonly used components.

In order to use them in your Symfony project, you need to require it with composer first

```bash
composer require symfony/maker-bundle
```

### Available Commands

```bash
  make:ddd:command            Creates a new command class and handler
  make:ddd:controller         Creates a new controller class
  make:ddd:model              Creates a new domain model class
  make:ddd:query              Creates a new query class and handler
  make:ddd:resource           Creates a new API Platform resource
```

## Building Blocks

### Model & Repository

The **domain model** is a representation of the domain concepts and business logic within your project. The **repository** on the other hand is an abstraction layer that provides a way to access and manipulate domain objects without exposing the details of the underlying data persistence mechanism (such as a database or file system).

Since Doctrine is the de-facto persistence layer for Symfony, this bundle also provides an (opinionated) implementation for a Doctrine-based repository.

#### Generator Command(s)

This command can be used to generate:

- The domain model class.
- A repository class for the model.
- The model's identity class as value object (optional).
- A Doctrine database entity configuration, either as annotation or separate config file (optional).
- A custom Doctrine type for the model's identity class (optional).

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

### AggregateRoot & Domain Events

Optionally, by inheriting from `AggregateRoot`, you can make a model class an **aggregate root**, which is used to encapsulate a group of related objects, along with the behavior and rules that apply to them. The aggregate root is usually responsible for managing the lifecycle of the objects within the aggregate, and for coordinating any interactions between them.

The `AggregateRoot` base class comes with some useful functionality to record and dispatch **domain events**, which represent significant occurrences or state changes within the domain of a software system.

#### Generator Command(s)

N/A

#### Example Usage

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

### Command & Query

You can use `CommandBus` and `QueryBus` as services to implement [CQRS](https://martinfowler.com/bliki/CQRS.html). Internally, both buses will use the [Symfony messenger](https://symfony.com/doc/current/messenger.html) to dispatch commands and queries.

#### Generator Command(s)

These commands can be used to generate:

- A command and command handler class.
- A query and query handler class.

The query / command generated is just an empty class. The handler class is registered as a message handler for the configured [Symfony Messenger](https://symfony.com/doc/current/messenger.html).

```bash
Description:
  Creates a new query|command class and handler

Usage:
  make:ddd:query|command [<name>]

Arguments:
  name                     The name of the query|command class (e.g. Customer)
```

#### Example Usage

```php
// src/Application/Query/TopRatedBookQuery.php

use GeekCell\Ddd\Contracts\Application\Query;

readonly class TopRatedBooksQuery implements Query
{
    public function __construct(
        public string $category,
        public int $sinceDays,
        public int $limit = 10,
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
            ->findTopRated($query->category, $query->sinceDays)
            ->paginate($query->limit);

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

### Controller

A standard Symfony controller, but augmented with command and query bus(es).

#### Generator Command

This command can be used to generate a controller with optional `QueryBus` and `CommandBus` dependencies.

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

An [API Platform](https://api-platform.com/) resource, but instead of using the standard approach of using a combined entity/resource approach, it is preferred to separate model (domain layer) and API Platform specific resource (infrastructure layer)

#### Generator Command

Minimum required API Platform version is [2.7](https://api-platform.com/docs/core/upgrade-guide/#api-platform-2730) for the [new metadata system](https://api-platform.com/docs/core/upgrade-guide/#apiresource-metadata).

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
