<?php

namespace App\Repository\Admin\Content\Page;

use App\Entity\Admin\Content\Page\PageContent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PageContent>
 *
 * @method PageContent|null find($id, $lockMode = null, $lockVersion = null)
 * @method PageContent|null findOneBy(array $criteria, array $orderBy = null)
 * @method PageContent[]    findAll()
 * @method PageContent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PageContentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PageContent::class);
    }
}
