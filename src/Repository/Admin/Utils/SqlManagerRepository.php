<?php

namespace App\Repository\Admin\Utils;

use App\Entity\Admin\Tools\SqlManager;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SqlManager>
 *
 * @method SqlManager|null find($id, $lockMode = null, $lockVersion = null)
 * @method SqlManager|null findOneBy(array $criteria, array $orderBy = null)
 * @method SqlManager[]    findAll()
 * @method SqlManager[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SqlManagerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SqlManager::class);
    }

    /**
     * Sauvegarde
     * @param SqlManager $entity
     * @param bool $flush
     * @return void
     */
    public function save(SqlManager $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Supprime
     * @param SqlManager $entity
     * @param bool $flush
     * @return void
     */
    public function remove(SqlManager $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Retourne une liste de user Paginé
     * @param int $page
     * @param int $limit
     * @param string|null $search
     * @return Paginator
     */
    public function getAllPaginate(int $page, int $limit, string $search = null): Paginator
    {
        $query = $this->createQueryBuilder('sm')
            ->orderBy('sm.id', 'ASC');

        if ($search !== null) {
            $query->where('sm.name like :search')
                ->orWhere('sm.query like : search')
                ->setParameter('search', '%' . $search . '%');
        }

        $paginator = new Paginator($query->getQuery(), true);
        $paginator->getQuery()
            ->setFirstResult($limit * ($page - 1))
            ->setMaxResults($limit);
        return $paginator;

    }
}
