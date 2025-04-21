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
}
