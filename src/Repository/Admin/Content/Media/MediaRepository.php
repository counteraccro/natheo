<?php

namespace App\Repository\Admin\Content\Media;

use App\Entity\Admin\Content\Media\Media;
use App\Entity\Admin\Content\Media\MediaFolder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Media>
 *
 * @method Media|null find($id, $lockMode = null, $lockVersion = null)
 * @method Media|null findOneBy(array $criteria, array $orderBy = null)
 * @method Media[]    findAll()
 * @method Media[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MediaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Media::class);
    }

    public function save(Media $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Media $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Retourne une liste de medias en fonction d'un mediaFolder
     * @param MediaFolder|null $mediaFolder
     * @return float|int|mixed|string
     */
    public function findByMediaFolder(?MediaFolder $mediaFolder = null): mixed
    {
        $query = $this->createQueryBuilder('m');
        $query->where('m.trash = false');

        if ($mediaFolder !== null) {
            $query->andWhere('m.mediaFolder = :val')->setParameter('val', $mediaFolder);
        } else {
            $query->andWhere('m.mediaFolder IS NULL');
        }

        return $query->orderBy('m.createdAt', 'ASC')->getQuery()->getResult();
    }

    /**
     * Retourne une liste de Medias contenant dans path la chaine $name
     * @param string $name
     * @return mixed
     */
    public function getAllByLikePath(string $name): mixed
    {
        $name = addcslashes($name, '\\%_');

        return $this->createQueryBuilder('m')
            ->andWhere('m.path LIKE :name')
            ->setParameter('name', '%' . $name . '%')
            ->orderBy('m.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne le nombre de médias tagué pour la corbeille
     * @return int
     */
    public function getNbInTrash(): int
    {
        $result = $this->createQueryBuilder('m')
            ->select('count(m.id) as nb')
            ->where('m.trash = true')
            ->getQuery()
            ->getScalarResult();

        if (isset($result[0]['nb'])) {
            return $result[0]['nb'];
        }
        return 0;
    }
}
