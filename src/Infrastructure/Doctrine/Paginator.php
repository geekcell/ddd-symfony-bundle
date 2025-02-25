<?php

declare(strict_types=1);

namespace GeekCell\DddBundle\Infrastructure\Doctrine;

use Assert\Assert;
use Doctrine\ORM\Tools\Pagination\Paginator as OrmPaginator;
use GeekCell\Ddd\Contracts\Domain\Paginator as PaginatorInterface;
use Traversable;

/**
 * @template T of object
 * @implements PaginatorInterface<T>
 */
class Paginator implements PaginatorInterface
{
    private readonly int $firstResult;

    private readonly int $maxResults;

    /**
     * @param OrmPaginator<T> $ormPaginator
     */
    public function __construct(
        private readonly OrmPaginator $ormPaginator,
    ) {
        $query = $this->ormPaginator->getQuery();
        $firstResult = $query->getFirstResult();
        $maxResults = $query->getMaxResults();

        Assert::that($maxResults)->notNull('Max results is not set');

        $this->firstResult = $firstResult;

        /** @var int $maxResults */
        $this->maxResults = $maxResults;
    }

    /**
     * @inheritDoc
     */
    public function getCurrentPage(): int
    {
        if (0 === $this->firstResult) {
            return 1;
        }

        return (int) ceil($this->firstResult / $this->maxResults);
    }

    /**
     * @inheritDoc
     */
    public function getTotalPages(): int
    {
        return (int) ceil($this->getTotalItems() / $this->maxResults);
    }

    /**
     * @inheritDoc
     */
    public function getItemsPerPage(): int
    {
        return $this->maxResults;
    }

    /**
     * @inheritDoc
     */
    public function getTotalItems(): int
    {
        return $this->count();
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return count($this->ormPaginator);
    }

    /**
     * @inheritDoc
     */
    public function getIterator(): Traversable
    {
        return $this->ormPaginator->getIterator();
    }
}
