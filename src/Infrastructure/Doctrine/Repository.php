<?php

declare(strict_types=1);

namespace GeekCell\DddBundle\Infrastructure\Doctrine;

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

abstract class Repository implements RepositoryInterface
{
    /**
     * @var QueryBuilder
     */
    private QueryBuilder $queryBuilder;

    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager  The entity manager
     * @param class-string $entityType               The entity class
     * @param string $alias                          Entity alias
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
        /** @var array<object> $results */
        $results = $this->queryBuilder->getQuery()->getResult() ?? [];

        /** @var Collection */
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
            ) {
                $queryBuilder
                    ->setFirstResult($itemsPerPage * ($currentPage - 1))->setMaxResults($itemsPerPage);
            }
        );

        return new DoctrinePaginator(
            new OrmPaginator($repository->queryBuilder->getQuery())
        );
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
     *
     * @return static
     */
    public function filter(callable $filter): static
    {
        $clone = clone $this;
        $filter($clone->queryBuilder);

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function __clone(): void
    {
        $this->queryBuilder = clone $this->queryBuilder;
    }

    /**
     * Determine the entity alias.
     *
     * @param class-string $entityType
     *
     * @return string
     */
    protected function determineAlias(string $entityType): string
    {
        $shortName = (new \ReflectionClass($entityType))->getShortName();
        return u($shortName)->camel()->snake()->toString();
    }
}
