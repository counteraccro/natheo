<?php

namespace App\Repository\Admin\Content;

use App\Entity\Admin\Content\MediaFolder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MediaFolder>
 *
 * @method MediaFolder|null find($id, $lockMode = null, $lockVersion = null)
 * @method MediaFolder|null findOneBy(array $criteria, array $orderBy = null)
 * @method MediaFolder[]    findAll()
 * @method MediaFolder[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MediaFolderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MediaFolder::class);
    }

    public function save(MediaFolder $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MediaFolder $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Retourne une liste de médiaFolder contenu dans un médiaFolder
     * @param MediaFolder|null $mediaFolder
     * @param bool $trash : par défaut false
     * @param bool $disabled : par défaut false
     * @return float|int|mixed|string
     */
    public function getByMediaFolder
    (
        MediaFolder $mediaFolder = null,
        bool        $trash = false,
        bool        $disabled = false
    ): mixed
    {
        $query = $this->createQueryBuilder('mf')
            ->andWhere('mf.disabled = :disabled')
            ->setParameter('disabled', $disabled)
            ->andWhere('mf.trash = :trash')
            ->setParameter('trash', $trash);

        if ($mediaFolder != null) {
            $query->andWhere('mf.parent = :val');
            $query->setParameter('val', $mediaFolder);
        } else {
            $query->andWhere('mf.parent IS NULL');
        }
        return $query->getQuery()->getResult();
    }


    /**
     * Retourne une liste de médiaFolder contenant dans path la chaine $name
     * @param string $name
     * @return mixed
     */
    public function getAllByLikePath(string $name): mixed
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.path LIKE :name')
            ->setParameter('name', '%' . $name . '%')
            ->orderBy('m.id', 'ASC')
            ->getQuery()->getResult();
    }

    /**
     * Retourne une liste de dossier (id, nom) qui est non-enfant du nom dossier envoyé en paramètre
     * et qui n'est pas le dossier id en paramètre. Liste pour déplacer le dossier
     * @param MediaFolder $mediaFolder
     * @return mixed
     */
    public function getAllFolderNoChild(MediaFolder $mediaFolder): mixed
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.path NOT LIKE :name')
            ->setParameter('name', '%' . $mediaFolder->getName() . '%')
            ->andWhere('m.id != :id')
            ->setParameter('id',  $mediaFolder->getId())
            ->orderBy('m.id', 'ASC')
            ->getQuery()->getResult();
    }

//    /**
//     * @return MediaFolder[] Returns an array of MediaFolder objects
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

//    public function findOneBySomeField($value): ?MediaFolder
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
