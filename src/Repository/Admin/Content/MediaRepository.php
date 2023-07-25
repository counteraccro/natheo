<?php

namespace App\Repository\Admin\Content;

use App\Entity\Admin\Content\Media;
use App\Entity\Admin\Content\MediaFolder;
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
    public function findByMediaFolder(MediaFolder $mediaFolder = null): mixed
    {
        $query = $this->createQueryBuilder('m');

        $query->select('m.id', 'm.title', 'm.webPath', 'm.size', 'm.name');

        if ($mediaFolder !== null) {
            $query->andWhere('m.mediaFolder = :val')
                ->setParameter('val', $mediaFolder);
        } else {
            $query->andWhere('m.mediaFolder IS NULL');
        }

        return $query->orderBy('m.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
