<?php

namespace Infrastructure\ApiPlatform;

use ApiPlatform\State\Pagination\PaginatorInterface;
use GeekCell\DddBundle\Infrastructure\ApiPlatform\Paginator;
use \GeekCell\DddBundle\Infrastructure\Doctrine\Repository as DoctrineRepository;
use \GeekCell\DddBundle\Infrastructure\Doctrine\Paginator as DoctrinePaginator;

if (interface_exists(PaginatorInterface::class)) {
    class Repository extends DoctrineRepository
    {
        public function paginateApiPlatformAware(int $itemsPerPage, int $currentPage = 1, bool $useApiPlatformPaginatorContract = false): Paginator
        {
            /** @var DoctrinePaginator $paginator */
            $paginator = parent::paginate($itemsPerPage, $currentPage, $useApiPlatformPaginatorContract);

            return new Paginator($paginator);
        }
    }
}
