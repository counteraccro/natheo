<?php
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Repository pour SQLManager
 */
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
     * Retourne une liste de sqlManager Paginé
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

        $query = $this->createQueryBuilder(SqlManager::DEFAULT_ALIAS)->orderBy(
            SqlManager::DEFAULT_ALIAS . '.' . $orderField,
            $order,
        );

        if (isset($queryParams['search']) && $queryParams['search'] !== '') {
            $query
                ->where(SqlManager::DEFAULT_ALIAS . '.name like :search')
                ->orWhere(SqlManager::DEFAULT_ALIAS . '.query like :search')
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
