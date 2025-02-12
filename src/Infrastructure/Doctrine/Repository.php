<?php

declare(strict_types=1);

namespace GeekCell\DddBundle\Infrastructure\Doctrine;

use ReflectionClass;
use Assert\Assert;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator as OrmPaginator;
use GeekCell\Ddd\Contracts\Domain\Paginator;
use GeekCell\Ddd\Contracts\Domain\Repository as RepositoryInterface;
use GeekCell\Ddd\Domain\Collection;
use GeekCell\DddBundle\Infrastructure\Doctrine\Paginator as DoctrinePaginator;
use Traversable;

use function Symfony\Component\String\u;

/**
 * @template T of object
 * @implements RepositoryInterface<T>
 */
abstract class Repository implements RepositoryInterface
{
    private QueryBuilder $queryBuilder;

    /**
     * @param class-string $entityType
     */
    public function __construct(
        protected EntityManagerInterface $entityManager,
        string $entityType,
        ?string $alias = null,
    ) {
        Assert::that($entityType)->classExists();

        if (null === $alias) {
            $alias = $this->determineAlias($entityType);
        }

        $this->queryBuilder = $this->entityManager
            ->createQueryBuilder()
            ->select($alias)
            ->from($entityType, $alias);
    }

    /**
     * @inheritDoc
     */
    public function collect(): Collection
    {
        /** @var array<T> $results */
        $results = $this->queryBuilder->getQuery()->getResult() ?? [];
        return new Collection($results);
    }

    /**
     * @inheritDoc
     */
    public function paginate(
        int $itemsPerPage,
        int $currentPage = 1
    ): Paginator {
        $repository = $this->filter(
            function (QueryBuilder $queryBuilder) use (
                $itemsPerPage,
                $currentPage
            ): void {
                $queryBuilder
                    ->setFirstResult($itemsPerPage * ($currentPage - 1))->setMaxResults($itemsPerPage);
            }
        );

        /** @var OrmPaginator<T> $paginator */
        $paginator = new OrmPaginator($repository->queryBuilder->getQuery());
        return new DoctrinePaginator($paginator);
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return count($this->collect());
    }

    /**
     * @inheritDoc
     */
    public function getIterator(): Traversable
    {
        return $this->collect()->getIterator();
    }

    /**
     * Apply a filter to the repository by adding to the query builder.
     *
     * @param callable $filter  A callable that accepts a QueryBuilder
     */
    public function filter(callable $filter): static
    {
        $clone = clone $this;
        $filter($clone->queryBuilder);

        return $clone;
    }

    public function __clone(): void
    {
        $this->queryBuilder = clone $this->queryBuilder;
    }

    /**
     * Determine the entity alias.
     *
     * @param class-string $entityType
     */
    protected function determineAlias(string $entityType): string
    {
        $shortName = (new ReflectionClass($entityType))->getShortName();
        return u($shortName)->camel()->snake()->toString();
    }
}
