<?php

namespace App\Repository\Admin\Content\Page;

use App\Entity\Admin\Content\Page\PageContentTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PageContentTranslation>
 *
 * @method PageContentTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method PageContentTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method PageContentTranslation[]    findAll()
 * @method PageContentTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PageContentTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PageContentTranslation::class);
    }

//    /**
//     * @return PageContentTranslation[] Returns an array of PageContentTranslation objects
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

//    public function findOneBySomeField($value): ?PageContentTranslation
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
