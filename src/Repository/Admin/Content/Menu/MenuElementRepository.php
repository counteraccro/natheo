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
     * @param int $idMenu
     * @param int|null $parent
     * @return mixed
     */
    public function getMenuElementByMenuAndParent(int $idMenu, int $parent = null): mixed
    {
        $queryB = $this->createQueryBuilder('me')
            ->where('me.menu =  :idMenu')
            ->setParameter('idMenu', $idMenu);
        if ($parent === null) {
            $queryB->andWhere('me.parent IS NULL');
        } else {
            $queryB->andWhere('me.parent =  :parent')
                ->setParameter('parent', $parent);
        }

        $queryB->addOrderBy('me.columnPosition', 'ASC')
            ->addOrderBy('me.rowPosition', 'ASC');

        return $queryB->getQuery()->getResult();
    }

//    /**
//     * @return MenuElement[] Returns an array of MenuElement objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('m.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?MenuElement
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
