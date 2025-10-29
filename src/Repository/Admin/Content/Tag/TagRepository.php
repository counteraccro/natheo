<?php
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Répository pour les tags
 */

namespace App\Repository\Admin\Content\Tag;

use App\Entity\Admin\Content\Tag\Tag;
use App\Entity\Admin\Content\Tag\TagTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tag>
 *
 * @method Tag|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tag|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tag[]    findAll()
 * @method Tag[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tag::class);
    }

    public function save(Tag $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Tag $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Retourne une liste de tag Paginé
     * @param int $page
     * @param int $limit
     * @param array $queryParams
     * @return Paginator
     */
    public function getAllPaginate(int $page, int $limit, array $queryParams): Paginator
    {
        $orderField = 'id';
        $order = 'DESC';
        if ($queryParams['orderField'] !== null) {
            $orderField = $queryParams['orderField'];
        }

        if ($queryParams['order'] !== null) {
            $order = $queryParams['order'];
        }

        $query = $this->createQueryBuilder(Tag::DEFAULT_ALIAS)->orderBy(Tag::DEFAULT_ALIAS . '.' . $orderField, $order);

        if ($queryParams['search'] !== null) {
            $query
                ->join(Tag::DEFAULT_ALIAS . '.tagTranslations', TagTranslation::DEFAULT_ALIAS)
                ->where(TagTranslation::DEFAULT_ALIAS . '.label like :search')
                ->setParameter('search', '%' . $queryParams['search'] . '%');
        }

        $paginator = new Paginator($query->getQuery(), true);
        $paginator
            ->getQuery()
            ->setFirstResult($limit * ($page - 1))
            ->setMaxResults($limit);
        return $paginator;
    }

    /**
     * Retourne une liste de tag en fonction de $search et de la locale
     * si withDisabled est à true, remonte aussi les tags disabled
     * @param string $locale
     * @param string $search
     * @param bool $withDisabled
     * @return array
     */
    public function searchByName(string $locale, string $search, bool $withDisabled = false): array
    {
        $query = $this->createQueryBuilder(Tag::DEFAULT_ALIAS)
            ->select(
                Tag::DEFAULT_ALIAS . '.id',
                'tt.label',
                Tag::DEFAULT_ALIAS . '.disabled',
                Tag::DEFAULT_ALIAS . '.color',
            )
            ->join(
                Tag::DEFAULT_ALIAS . '.tagTranslations',
                TagTranslation::DEFAULT_ALIAS,
                'WITH',
                "tt.locale = '" . $locale . "'",
            )
            ->andWhere('LOWER(' . TagTranslation::DEFAULT_ALIAS . '.label) LIKE LOWER(:label)')
            ->setParameter('label', '%' . $search . '%');

        if (!$withDisabled) {
            $query->andWhere(Tag::DEFAULT_ALIAS . '.disabled = false');
        }

        return $query->getQuery()->getArrayResult();
    }

    /**
     * Recherche dans les tags
     * @param string $search
     * @param string $locale
     * @param int $page
     * @param int $limit
     * @return Paginator
     */
    public function search(string $search, string $locale, int $page, int $limit): Paginator
    {
        $query = $this->createQueryBuilder(Tag::DEFAULT_ALIAS);

        $query
            ->join(Tag::DEFAULT_ALIAS . '.tagTranslations', TagTranslation::DEFAULT_ALIAS)
            ->orWhere(
                TagTranslation::DEFAULT_ALIAS .
                    '.label like :search AND ' .
                    TagTranslation::DEFAULT_ALIAS .
                    '.locale = :locale',
            )
            ->setParameter('search', '%' . $search . '%')
            ->setParameter('locale', $locale);

        $paginator = new Paginator($query->getQuery(), true);
        $paginator
            ->getQuery()
            ->setFirstResult($limit * ($page - 1))
            ->setMaxResults($limit);
        return $paginator;
    }
}
