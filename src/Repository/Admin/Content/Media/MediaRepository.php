<?php

declare(strict_types=1);

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
     * Médias hors corbeille d'un dossier (racine si null), avec leurs traductions
     * @param MediaFolder|null $mediaFolder
     * @param string $orderField name ou createdAt
     * @param string $direction ASC ou DESC
     * @return Media[]
     */
    public function findInFolder(
        ?MediaFolder $mediaFolder,
        string $orderField = 'createdAt',
        string $direction = 'ASC',
    ): array {
        $qb = $this->createQueryBuilder('media')
            ->addSelect('mediaTranslation')
            ->leftJoin('media.mediaTranslations', 'mediaTranslation')
            ->where('media.trashedAt IS NULL')
            ->orderBy(
                'media.' . ($orderField === 'name' ? 'name' : 'createdAt'),
                $direction === 'DESC' ? 'DESC' : 'ASC',
            );
        $mediaFolder === null
            ? $qb->andWhere('media.mediaFolder IS NULL')
            : $qb->andWhere('media.mediaFolder = :folder')->setParameter('folder', $mediaFolder);
        return $qb->getQuery()->getResult();
    }
}
