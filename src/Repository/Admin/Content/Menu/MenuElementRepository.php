<?php

namespace App\Repository\Admin\Content\Menu;

use App\Entity\Admin\Content\Menu\MenuElement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MenuElement>
 */
class MenuElementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MenuElement::class);
    }

    /**
     * @param MenuElement $entity
     * @param bool $flush
     * @return void
     */
    public function save(MenuElement $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param MenuElement $entity
     * @param bool $flush
     * @return void
     */
    public function remove(MenuElement $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Retourne une liste de menuElements d'un menu et de son parent.
     * Si parent = null, retourne la liste de premier niveau de menuElement
     * Si showDisabled est à true, affiche aussi les elements masqués
     * @param int $idMenu
     * @param int|null $parent
     * @param bool $showDisabled
     * @return mixed
     */
    public function getMenuElementByMenuAndParent(int $idMenu, ?int $parent = null, bool $showDisabled = true): mixed
    {
        $queryB = $this->createQueryBuilder('me')->where('me.menu =  :idMenu')->setParameter('idMenu', $idMenu);
        if ($parent === null) {
            $queryB->andWhere('me.parent IS NULL');
        } else {
            $queryB->andWhere('me.parent =  :parent')->setParameter('parent', $parent);
        }

        if (!$showDisabled) {
            $queryB->andWhere('me.disabled = :disabled')->setParameter('disabled', false);
        }

        $queryB->addOrderBy('me.columnPosition', 'ASC')->addOrderBy('me.rowPosition', 'ASC');

        return $queryB->getQuery()->getResult();
    }
}
