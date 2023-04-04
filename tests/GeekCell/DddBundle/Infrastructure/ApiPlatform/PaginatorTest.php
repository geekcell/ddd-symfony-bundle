<?php

namespace Infrastructure\ApiPlatform;

use ApiPlatform\State\Pagination\PaginatorInterface;
use GeekCell\DddBundle\Infrastructure\ApiPlatform\Paginator;
use Mockery;
use PHPUnit\Framework\TestCase;
use GeekCell\DddBundle\Infrastructure\Doctrine\Paginator as DoctrinePaginator;

if (interface_exists(PaginatorInterface::class)) {
    class PaginatorTest extends TestCase
    {
        /** @var DoctrinePaginator|Mockery\MockInterface */
        private mixed $doctrinePaginatorMock;

        public function setUp(): void
        {
            parent::setUp();

            $this->doctrinePaginatorMock = Mockery::mock(DoctrinePaginator::class);
        }

        public function testGetCurrentPage()
        {
            $this->doctrinePaginatorMock->shouldReceive('getCurrentPage')
                ->once()
                ->andReturn(12);

            $paginator = new Paginator($this->doctrinePaginatorMock);

            $this->assertEquals(12, $paginator->getCurrentPage());
        }

        public function testGetTotalPages()
        {
            $this->doctrinePaginatorMock->shouldReceive('getTotalPages')
                ->once()
                ->andReturn(77);

            $paginator = new Paginator($this->doctrinePaginatorMock);

            $this->assertEquals(77, $paginator->getLastPage());
        }

        public function testGetItemsPerPage()
        {
            $this->doctrinePaginatorMock->shouldReceive('getItemsPerPage')
                ->once()
                ->andReturn(10);

            $paginator = new Paginator($this->doctrinePaginatorMock);

            $this->assertEquals(10, $paginator->getItemsPerPage());
        }

        public function testGetTotalItems()
        {
            $this->doctrinePaginatorMock->shouldReceive('getTotalItems')
                ->once()
                ->andReturn(770);

            $paginator = new Paginator($this->doctrinePaginatorMock);

            $this->assertEquals(770, $paginator->getTotalItems());
        }

        public function testGetCount()
        {
            $this->doctrinePaginatorMock->shouldReceive('count')
                ->once()
                ->andReturn(4);

            $paginator = new Paginator($this->doctrinePaginatorMock);

            $this->assertEquals(4, $paginator->count());
        }

        public function testGetIterator()
        {
            $this->doctrinePaginatorMock->shouldReceive('getIterator')
                ->once()
                ->andReturn(new \ArrayIterator([]));

            $paginator = new Paginator($this->doctrinePaginatorMock);

            $this->assertInstanceOf(\Traversable::class, $paginator->getIterator());
        }
    }
}
