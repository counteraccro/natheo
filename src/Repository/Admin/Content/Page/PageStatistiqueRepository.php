<?php

namespace App\Repository\Admin\Content\Page;

use App\Entity\Admin\Content\Page\PageStatistique;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PageStatistique>
 *
 * @method PageStatistique|null find($id, $lockMode = null, $lockVersion = null)
 * @method PageStatistique|null findOneBy(array $criteria, array $orderBy = null)
 * @method PageStatistique[]    findAll()
 * @method PageStatistique[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PageStatistiqueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PageStatistique::class);
    }

    public function save(PageStatistique $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return PageStatistique[] Returns an array of PageStatistique objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?PageStatistique
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
