<?php

namespace App\Repository\Admin\Content\Tag;

use App\Entity\Admin\Content\Tag\TagTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TagTranslation>
 *
 * @method TagTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method TagTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method TagTranslation[]    findAll()
 * @method TagTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TagTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TagTranslation::class);
    }

    public function save(TagTranslation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(TagTranslation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
