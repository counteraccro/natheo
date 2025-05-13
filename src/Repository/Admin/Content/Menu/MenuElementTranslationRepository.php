<?php

namespace App\Repository\Admin\Content\Menu;

use App\Entity\Admin\Content\Menu\MenuElementTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MenuElementTranslation>
 */
class MenuElementTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MenuElementTranslation::class);
    }
}
