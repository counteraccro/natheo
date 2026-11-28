<?php

declare(strict_types=1);

namespace App\Repository\Admin\Content\Media;

use App\Entity\Admin\Content\Media\MediaTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MediaTranslation>
 *
 * @method MediaTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method MediaTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method MediaTranslation[]    findAll()
 * @method MediaTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MediaTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MediaTranslation::class);
    }

    public function save(MediaTranslation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MediaTranslation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
