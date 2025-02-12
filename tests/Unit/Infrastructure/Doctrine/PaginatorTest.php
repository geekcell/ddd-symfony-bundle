<?php

declare(strict_types=1);

namespace GeekCell\DddBundle\Tests\Unit\Infrastructure\Doctrine;

use PHPUnit\Framework\Attributes\DataProvider;
use ArrayIterator;
use Doctrine\ORM\Tools\Pagination\Paginator as OrmPaginator;
use GeekCell\DddBundle\Infrastructure\Doctrine\Paginator as DoctrinePaginator;
use Mockery;
use PHPUnit\Framework\TestCase;

class PaginatorTest extends TestCase
{
    /** @var OrmPaginator|Mockery\MockInterface */
    private mixed $ormPaginatorMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ormPaginatorMock = Mockery::mock(OrmPaginator::class);
    }

    #[DataProvider('provideCurrentPageData')]
    public function testGetCurrentPage(int $first, int $max, int $currentPage): void
    {
        // Given
        $this->ormPaginatorMock->shouldReceive('getQuery')
            ->once()
            ->andReturnSelf();

        $this->ormPaginatorMock->shouldReceive('getFirstResult')
            ->once()
            ->andReturn($first);

        $this->ormPaginatorMock->shouldReceive('getMaxResults')
            ->once()
            ->andReturn($max);

        $paginator = new DoctrinePaginator($this->ormPaginatorMock);

        // When
        $result = $paginator->getCurrentPage();

        // Then
        $this->assertEquals($currentPage, $result);
    }

    #[DataProvider('provideTotalPagesData')]
    public function testGetTotalPages(int $first, int $max, int $count, int $total): void
    {
        // Given
        $this->ormPaginatorMock->shouldReceive('getQuery')
            ->once()
            ->andReturnSelf();

        $this->ormPaginatorMock->shouldReceive('getFirstResult')
            ->once()
            ->andReturn($first);

        $this->ormPaginatorMock->shouldReceive('getMaxResults')
            ->once()
            ->andReturn($max);

        $this->ormPaginatorMock->shouldReceive('count')
            ->once()
            ->andReturn($count);

        $paginator = new DoctrinePaginator($this->ormPaginatorMock);

        // When
        $result = $paginator->getTotalPages();

        // Then
        $this->assertEquals($total, $result);
    }

    public function testGetItemsPerPage(): void
    {
        // Given
        $max = 100;

        $this->ormPaginatorMock->shouldReceive('getQuery')
            ->once()
            ->andReturnSelf();

        $this->ormPaginatorMock->shouldReceive('getFirstResult')
            ->once()
            ->andReturn(1);

        $this->ormPaginatorMock->shouldReceive('getMaxResults')
            ->once()
            ->andReturn($max);

        $paginator = new DoctrinePaginator($this->ormPaginatorMock);

        // When
        $result = $paginator->getItemsPerPage();

        // Then
        $this->assertEquals($max, $result);
    }

    public function testGetTotalItems(): void
    {
        // Given
        $count = 100;

        $this->ormPaginatorMock->shouldReceive('getQuery')
            ->once()
            ->andReturnSelf();

        $this->ormPaginatorMock->shouldReceive('getFirstResult')
            ->once()
            ->andReturn(1);

        $this->ormPaginatorMock->shouldReceive('getMaxResults')
            ->once()
            ->andReturn(1);

        $this->ormPaginatorMock->shouldReceive('count')
            ->once()
            ->andReturn($count);

        $paginator = new DoctrinePaginator($this->ormPaginatorMock);

        // When
        $result = $paginator->getTotalItems();

        // Then
        $this->assertEquals($count, $result);
    }

    public function testGetIterator(): void
    {
        // Given
        $iterator = new ArrayIterator();

        $this->ormPaginatorMock->shouldReceive('getQuery')
            ->once()
            ->andReturnSelf();

        $this->ormPaginatorMock->shouldReceive('getFirstResult')
            ->once()
            ->andReturn(1);

        $this->ormPaginatorMock->shouldReceive('getMaxResults')
            ->once()
            ->andReturn(1);

        $this->ormPaginatorMock->shouldReceive('getIterator')
            ->once()
            ->andReturn($iterator);

        $paginator = new DoctrinePaginator($this->ormPaginatorMock);

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
