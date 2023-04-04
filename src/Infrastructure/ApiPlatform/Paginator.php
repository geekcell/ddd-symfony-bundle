<?php

declare(strict_types=1);

namespace GeekCell\DddBundle\Infrastructure\ApiPlatform;

use ApiPlatform\State\Pagination\PaginatorInterface;
use \GeekCell\DddBundle\Infrastructure\Doctrine\Paginator as DoctrinePaginator;
use IteratorAggregate;
use Traversable;

if (interface_exists(PaginatorInterface::class)) {
    class Paginator implements IteratorAggregate, PaginatorInterface
    {
        public function __construct(private readonly DoctrinePaginator $doctrinePaginator)
        {
        }

        public function getLastPage(): float
        {
            return $this->doctrinePaginator->getTotalPages();
        }

        public function count(): int
        {
            return $this->doctrinePaginator->count();
        }

        public function getCurrentPage(): float
        {
            return $this->doctrinePaginator->getCurrentPage();
        }

        public function getItemsPerPage(): float
        {
            return $this->doctrinePaginator->getItemsPerPage();
        }

        public function getTotalItems(): float
        {
            return $this->doctrinePaginator->getTotalItems();
        }

        public function getIterator(): Traversable
        {
            return $this->doctrinePaginator->getIterator();
        }
    }
}
