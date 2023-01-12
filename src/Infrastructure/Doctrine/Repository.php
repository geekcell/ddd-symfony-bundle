<?php

declare(strict_types=1);

namespace GeekCell\DddBundle\Infrastructure\Doctrine;

use Assert\Assert;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use GeekCell\Ddd\Contracts\Domain\Repository as RepositoryInterface;
use GeekCell\Ddd\Domain\Collection;
use GeekCell\DddBundle\Infrastructure\Doctrine\Paginator as DoctrinePaginator;
use Traversable;

abstract class Repository implements RepositoryInterface
{
    /**
     * @var QueryBuilder
     */
    private QueryBuilder $queryBuilder;

    /**
     * @var null|int
     */
    private ?int $itemsPerPage = null;

    /**
     * @var null|int
     */
    private ?int $currentPage = null;

    /**
     * @var bool
     */
    private bool $isPaginated = false;

    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager  The entity manager
     * @param string $entityClass                    The entity class
     * @param string $collectionType                 The collection type
     * @param string $alias                          Entity alias
     */
    public function __construct(
        protected EntityManagerInterface $entityManager,
        string $entityClass,
        protected string $collectionType,
        string $alias,
    ) {
        Assert::that($entityClass)->classExists();
        Assert::that($collectionType)->classExists();
        Assert::that($alias)->notEmpty();

        $this->queryBuilder = $this->entityManager
            ->createQueryBuilder()
            ->select($alias)
            ->from($entityClass, $alias);
    }

    /**
     * @inheritDoc
     */
    public function collect(): static
    {
        $clone = clone $this;
        $clone->itemsPerPage = null;
        $clone->currentPage = null;
        $clone->isPaginated = false;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function paginate(
        int $itemsPerPage,
        int $currentPage = 1
    ): static
    {
        $clone = clone $this;
        $clone->itemsPerPage = $itemsPerPage;
        $clone->currentPage = $currentPage;
        $clone->isPaginated = true;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return count($this->toCollection());
    }

    /**
     * @inheritDoc
     */
    public function getIterator(): Traversable
    {
        if ($this->isPaginated) {
            $repository = $this->filter(function (QueryBuilder $queryBuilder) {
                $queryBuilder->setFirstResult(
                    $this->itemsPerPage * ($this->currentPage - 1));
                $queryBuilder->setMaxResults($this->itemsPerPage);
            });

            $paginator = new Paginator($repository->queryBuilder->getQuery());

            return new DoctrinePaginator($paginator);
        }

        return $this->toCollection();
    }

    /**
     * Apply a filter to the repository by adding to the query builder.
     *
     * @param callable $filter  A callable that accepts a QueryBuilder
     * @return static
     */
    protected function filter(callable $filter): static
    {
        $clone = clone $this;
        $filter($clone->queryBuilder);

        return $clone;
    }

    /**
     * Wraps the results of the query builder in a collection.
     *
     * @return Collection
     */
    protected function toCollection(): Collection
    {
        $collectionClass = $this->collectionType;
        $results = $this->queryBuilder->getQuery()->getResult() ?? [];

        /** @var Collection */
        return new $collectionClass($results);
    }

    /**
     * @inheritDoc
     */
    public function __clone(): void
    {
        $this->queryBuilder = clone $this->queryBuilder;
    }
}
