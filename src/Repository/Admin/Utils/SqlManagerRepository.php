<?php

namespace App\Repository\Admin\Utils;

use App\Entity\Admin\Utils\SqlManager;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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

    //    /**
    //     * @return SqlManager[] Returns an array of SqlManager objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?SqlManager
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
