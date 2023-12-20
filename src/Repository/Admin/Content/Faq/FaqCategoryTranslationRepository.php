<?php

namespace App\Repository\Admin\Content\Faq;

use App\Entity\Admin\Content\Faq\FaqCategoryTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FaqCategoryTranslation>
 *
 * @method FaqCategoryTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method FaqCategoryTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method FaqCategoryTranslation[]    findAll()
 * @method FaqCategoryTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FaqCategoryTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FaqCategoryTranslation::class);
    }

//    /**
//     * @return FaqCategoryTranslation[] Returns an array of FaqCategoryTranslation objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('f.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?FaqCategoryTranslation
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
