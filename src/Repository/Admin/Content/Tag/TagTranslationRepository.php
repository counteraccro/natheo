<?php

declare(strict_types=1);

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

    /**
     * Libellé d'une langue égal à $label sans tenir compte de la casse, hors tag $excludedTagId
     * @param string $locale
     * @param string $label
     * @param int|null $excludedTagId
     * @return TagTranslation|null
     */
    public function findOneByLabel(string $locale, string $label, ?int $excludedTagId = null): ?TagTranslation
    {
        $qb = $this->createQueryBuilder('translation')
            ->where('translation.locale = :locale')
            ->andWhere('LOWER(translation.label) = :label')
            ->setParameter('locale', $locale)
            ->setParameter('label', mb_strtolower(trim($label)))
            ->setMaxResults(1);
        if ($excludedTagId !== null) {
            $qb->andWhere('IDENTITY(translation.tag) <> :tag')->setParameter('tag', $excludedTagId);
        }
        return $qb->getQuery()->getOneOrNullResult();
    }
}
