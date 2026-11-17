<?php

declare(strict_types=1);

namespace App\Repository\Admin\System;

use App\Entity\Admin\System\ApiToken;
use App\Repository\Trait\OrderedQueryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ApiToken>
 */
class ApiTokenRepository extends ServiceEntityRepository
{
    use OrderedQueryTrait;

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
     * Retourne la liste paginée des tokens, sans les tokens internes
     * @param int $page
     * @param int $limit
     * @param array $queryParams
     * @return Paginator
     */
    public function getAllPaginate(int $page, int $limit, array $queryParams): Paginator
    {
        $query = $this->createQueryBuilder(ApiToken::DEFAULT_ALIAS)
            ->where(ApiToken::DEFAULT_ALIAS . '.internal = false');
        $this->applyOrdering($query, ApiToken::class, $queryParams);

        if (isset($queryParams['search']) && $queryParams['search'] !== '') {
            $query
                ->andWhere(ApiToken::DEFAULT_ALIAS . '.name like :search')
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
