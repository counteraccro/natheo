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
}
