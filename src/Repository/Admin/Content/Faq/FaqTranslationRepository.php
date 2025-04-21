<?php

namespace App\Repository\Admin\Content\Faq;

use App\Entity\Admin\Content\Faq\FaqTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FaqTranslation>
 *
 * @method FaqTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method FaqTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method FaqTranslation[]    findAll()
 * @method FaqTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FaqTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FaqTranslation::class);
    }
}
