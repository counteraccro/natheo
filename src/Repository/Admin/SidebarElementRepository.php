<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Repository SidebarElement, element du menu sidebar de l'admin
 */
namespace App\Repository\Admin;

use App\Entity\Admin\SidebarElement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SidebarElement>
 *
 * @method SidebarElement|null find($id, $lockMode = null, $lockVersion = null)
 * @method SidebarElement|null findOneBy(array $criteria, array $orderBy = null)
 * @method SidebarElement[]    findAll()
 * @method SidebarElement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SidebarElementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SidebarElement::class);
    }

    public function save(SidebarElement $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SidebarElement $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return SidebarElement[] Returns an array of SidebarElement objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?SidebarElement
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
