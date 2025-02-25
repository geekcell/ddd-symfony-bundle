<?php

declare(strict_types=1);

namespace GeekCell\DddBundle\Tests\Unit\Infrastructure\Doctrine;

use Doctrine\ORM\Query;
use PHPUnit\Framework\Attributes\DataProvider;
use ArrayIterator;
use Doctrine\ORM\Tools\Pagination\Paginator as OrmPaginator;
use GeekCell\DddBundle\Infrastructure\Doctrine\Paginator as DoctrinePaginator;
use Mockery;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Traversable;

class PaginatorTest extends TestCase
{
    #[DataProvider('provideCurrentPageData')]
    public function testGetCurrentPage(int $first, int $max, int $currentPage): void
    {
        // Given
        $ormPaginator = $this->mockOrmPaginator($first, $max);

        $paginator = new DoctrinePaginator($ormPaginator);

        // When
        $result = $paginator->getCurrentPage();

        // Then
        $this->assertEquals($currentPage, $result);
    }

    /**
     * @param ?Traversable<mixed> $iterator
     */
    private function mockOrmPaginator(
        int $firstResult,
        ?int $maxResults,
        ?int $count = null,
        ?Traversable $iterator = null
    ): OrmPaginator&MockObject {
        $ormPaginatorMock = $this->createMock(OrmPaginator::class);
        if ($count !== null) {
            $ormPaginatorMock
                ->expects($this->once())
                ->method('count')
                ->willReturn($count);
        }

        if ($iterator !== null) {
            $ormPaginatorMock
                ->expects($this->once())
                ->method('getIterator')
                ->willReturn($iterator);
        }

        if (!method_exists(OrmPaginator::class, 'getFirstResult')) {
            // doctrine/orm:^3.x
            $query = $this->createMock(Query::class);
            $ormPaginatorMock
                ->expects($this->once())
                ->method('getQuery')
                ->willReturn($query);

            $query
                ->expects($this->once())
                ->method('getFirstResult')
                ->willReturn($firstResult);

            $query
                ->expects($this->once())
                ->method('getMaxResults')
                ->willReturn($maxResults);

            return $ormPaginatorMock;
        }

        // doctrine/orm:^2.x
        $ormPaginatorMock
            ->expects($this->once())
            ->method('getQuery')
            ->willReturnSelf();

        $ormPaginatorMock
            ->expects($this->once())
            ->method('getFirstResult')
            ->willReturn($firstResult);

        $ormPaginatorMock
            ->expects($this->once())
            ->method('getMaxResults')
            ->willReturn($maxResults);

        return $ormPaginatorMock;
    }

    #[DataProvider('provideTotalPagesData')]
    public function testGetTotalPages(int $first, int $max, int $count, int $total): void
    {
        // Given
        $ormPaginator = $this->mockOrmPaginator($first, $max, $count);

        $paginator = new DoctrinePaginator($ormPaginator);

        // When
        $result = $paginator->getTotalPages();

        // Then
        $this->assertEquals($total, $result);
    }

    public function testGetItemsPerPage(): void
    {
        // Given
        $max = 100;

        $ormPaginator = $this->mockOrmPaginator(1, $max);

        $paginator = new DoctrinePaginator($ormPaginator);

        // When
        $result = $paginator->getItemsPerPage();

        // Then
        $this->assertEquals($max, $result);
    }

    public function testGetTotalItems(): void
    {
        // Given
        $count = 100;
        $ormPaginator = $this->mockOrmPaginator(1, 1, $count);

        $paginator = new DoctrinePaginator($ormPaginator);

        // When
        $result = $paginator->getTotalItems();

        // Then
        $this->assertEquals($count, $result);
    }

    public function testGetIterator(): void
    {
        // Given
        $iterator = new ArrayIterator();

        $ormPaginator = $this->mockOrmPaginator(1, 1, iterator: $iterator);

        $paginator = new DoctrinePaginator($ormPaginator);

        // When
        $result = $paginator->getIterator();

        // Then
        $this->assertEquals($iterator, $result);
    }

    /**
     * @return array<int, array<int, int>>
     */
    public static function provideCurrentPageData(): array
    {
        return [
            [0, 1, 1],
            [2, 10, 1],
            [10, 10, 1],
            [11, 10, 2],
            [20, 10, 2],
            [21, 10, 3],
        ];
    }

    /**
     * @return array<int, array<int, int>>
     */
    public static function provideTotalPagesData(): array
    {
        return [
            [0, 1, 10, 10],
            [2, 10, 100, 10],
            [10, 10, 30, 3],
            [10, 10, 29, 3],
            [10, 10, 31, 4],
            [11, 10, 2, 1],
            [20, 10, 2, 1],
        ];
    }
}
