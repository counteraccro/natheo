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
     * Retourne une liste de menuElements d'un menu uniquement de premier niveau (parent null)
     * @param int $idMenu
     * @return float|int|mixed|string
     */
    public function getMenuElementFirstLevelByMenu(int $idMenu): mixed
    {
        return $this->createQueryBuilder('me')
            ->where('me.menu =  :idMenu')
            ->setParameter('idMenu', $idMenu)
            ->andWhere('me.parent is NULL')
            ->orderBy('me.columnPosition', 'ASC')
            ->orderBy('me.rowPosition', 'ASC')
            ->getQuery()
            ->getResult();
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
