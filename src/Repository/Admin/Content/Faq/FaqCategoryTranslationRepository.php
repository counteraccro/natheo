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
}
