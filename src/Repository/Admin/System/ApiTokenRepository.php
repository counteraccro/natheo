<?php

namespace App\Repository\Admin\System;

use App\Entity\Admin\System\ApiToken;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ApiToken>
 */
class ApiTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApiToken::class);
    }

    /**
     * @param ApiToken $entity
     * @param bool $flush
     * @return void
     */
    public function save(ApiToken $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param ApiToken $entity
     * @param bool $flush
     * @return void
     */
    public function remove(ApiToken $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Retourne une liste de Mail Paginé
     * @param int $page
     * @param int $limit
     * @param array $queryParams
     * @return Paginator
     */
    public function getAllPaginate(int $page, int $limit, array $queryParams): Paginator
    {
        $orderField = 'id';
        $order = 'DESC';
        if (isset($queryParams['orderField']) && $queryParams['orderField'] !== '') {
            $orderField = $queryParams['orderField'];
        }

        if (isset($queryParams['order']) && $queryParams['order'] !== '') {
            $order = $queryParams['order'];
        }

        $query = $this->createQueryBuilder(ApiToken::DEFAULT_ALIAS)->orderBy(
            ApiToken::DEFAULT_ALIAS . '.' . $orderField,
            $order,
        );

        if (isset($queryParams['search']) && $queryParams['search'] !== '') {
            $query
                ->where(ApiToken::DEFAULT_ALIAS . '.name like :search')
                ->setParameter('search', '%' . $queryParams['search'] . '%');
        }

        $paginator = new Paginator($query->getQuery(), true);
        $paginator
            ->getQuery()
            ->setFirstResult($limit * ($page - 1))
            ->setMaxResults($limit);
        return $paginator;
    }
}
