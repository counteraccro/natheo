<?php

declare(strict_types=1);

namespace App\Repository\Admin\Content\Media;

use App\Entity\Admin\Content\Media\MediaFolder;
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
     * Sous-dossiers hors corbeille d'un dossier (racine si null), triés par nom
     * @param MediaFolder|null $parent
     * @return MediaFolder[]
     */
    public function findChildren(?MediaFolder $parent): array
    {
        $qb = $this->createQueryBuilder('mediaFolder')
            ->where('mediaFolder.trashedAt IS NULL')
            ->orderBy('mediaFolder.name', 'ASC');
        $parent === null
            ? $qb->andWhere('mediaFolder.parent IS NULL')
            : $qb->andWhere('mediaFolder.parent = :parent')->setParameter('parent', $parent);
        return $qb->getQuery()->getResult();
    }
}
