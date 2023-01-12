<?php

declare(strict_types=1);

namespace GeekCell\DddBundle\Infrastructure\Doctrine;

use Assert\Assert;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;
use GeekCell\Ddd\Contracts\Domain\Paginator as PaginatorInterface;
use Traversable;

class Paginator implements PaginatorInterface
{
    /**
     * @var int
     */
    private readonly int $firstResult;

    /**
     * @var int
     */
    private readonly int $maxResults;

    public function __construct(
        private readonly DoctrinePaginator $doctrinePaginator,
    )
    {
        $query = $this->doctrinePaginator->getQuery();
        $firstResult = $query->getFirstResult();
        $maxResults = $query->getMaxResults();

        Assert::that($firstResult)->notNull('First result is not set');
        Assert::that($maxResults)->notNull('Max results is not set');

        /** @var int $firstResult */
        $this->firstResult = $firstResult;

        /** @var int $maxResults */
        $this->maxResults = $maxResults;
    }

    public function getCurrentPage(): int
    {
        return (int) ceil($this->firstResult / $this->maxResults);
    }

    public function getTotalPages(): int
    {
        return (int) ceil($this->getTotalItems() / $this->maxResults);
    }

    public function getItemsPerPage(): int
    {
        return $this->maxResults;
    }

    public function getTotalItems(): int
    {
        return count($this->doctrinePaginator);
    }

    public function count(): int
    {
        return count(iterator_to_array($this->doctrinePaginator));
    }

    public function getIterator(): Traversable
    {
        return $this->doctrinePaginator->getIterator();
    }
}
