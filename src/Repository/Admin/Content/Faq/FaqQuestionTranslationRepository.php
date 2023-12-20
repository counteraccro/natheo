<?php

namespace App\Repository\Admin\Content\Faq;

use App\Entity\Admin\Content\Faq\FaqQuestionTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FaqQuestionTranslation>
 *
 * @method FaqQuestionTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method FaqQuestionTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method FaqQuestionTranslation[]    findAll()
 * @method FaqQuestionTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FaqQuestionTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FaqQuestionTranslation::class);
    }

//    /**
//     * @return FaqQuestionTranslation[] Returns an array of FaqQuestionTranslation objects
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

//    public function findOneBySomeField($value): ?FaqQuestionTranslation
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
