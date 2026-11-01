<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Répository pour les tags
 */

namespace App\Repository\Admin\Content\Tag;

use App\Entity\Admin\Content\Tag\Tag;
use App\Entity\Admin\Content\Tag\TagTranslation;
use App\Entity\Admin\System\User;
use App\Repository\Trait\OrderedQueryTrait;
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
    use OrderedQueryTrait;

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
     * La jointure sur les traductions est limitée à la locale courante (tri et recherche par libellé)
     * @param int $page
     * @param int $limit
     * @param array $queryParams
     * @param int|null $userId filtre sur le propriétaire du tag
     * @return Paginator
     */
    public function getAllPaginate(int $page, int $limit, array $queryParams, ?int $userId = null): Paginator
    {
        $orderField = $queryParams['orderField'] ?? '';
        $search = (string) ($queryParams['search'] ?? '');

        $query = $this->createQueryBuilder(Tag::DEFAULT_ALIAS)
            ->addSelect(User::DEFAULT_ALIAS)
            ->leftJoin(Tag::DEFAULT_ALIAS . '.user', User::DEFAULT_ALIAS);
        if ($orderField === 'label' || $search !== '') {
            // LEFT JOIN : un tag sans libellé dans la locale reste visible lors du tri
            $query
                ->leftJoin(
                    Tag::DEFAULT_ALIAS . '.tagTranslations',
                    TagTranslation::DEFAULT_ALIAS,
                    'WITH',
                    TagTranslation::DEFAULT_ALIAS . '.locale = :locale',
                )
                ->setParameter('locale', (string) ($queryParams['locale'] ?? ''));
        }

        if ($orderField === 'label') {
            $this->applyOrdering($query, TagTranslation::class, $queryParams);
        } else {
            $this->applyOrdering($query, Tag::class, $queryParams);
        }

        if ($search !== '') {
            $query
                ->andWhere(TagTranslation::DEFAULT_ALIAS . '.label like :search')
                ->setParameter('search', '%' . $this->escapeLike($search) . '%');
        }

        if ($userId !== null) {
            $query->andWhere(Tag::DEFAULT_ALIAS . '.user = :userId')->setParameter('userId', $userId);
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
                TagTranslation::DEFAULT_ALIAS . '.label',
                Tag::DEFAULT_ALIAS . '.disabled',
                Tag::DEFAULT_ALIAS . '.color',
            )
            ->join(
                Tag::DEFAULT_ALIAS . '.tagTranslations',
                TagTranslation::DEFAULT_ALIAS,
                'WITH',
                TagTranslation::DEFAULT_ALIAS . '.locale = :locale',
            )
            ->andWhere('LOWER(' . TagTranslation::DEFAULT_ALIAS . '.label) LIKE LOWER(:label)')
            ->setParameter('locale', $locale)
            ->setParameter('label', '%' . $this->escapeLike($search) . '%');

        if (!$withDisabled) {
            $query->andWhere(Tag::DEFAULT_ALIAS . '.disabled = false');
        }

        return $query->getQuery()->getArrayResult();
    }

    /**
     * Échappe les jokers % et _ d'une valeur recherchée avec LIKE
     * @param string $value
     * @return string
     */
    private function escapeLike(string $value): string
    {
        return addcslashes($value, '\\%_');
    }

    /**
     * Retourne le nombre de pages auxquelles le tag est rattaché
     * @param Tag $tag
     * @return int
     */
    public function countPages(Tag $tag): int
    {
        return (int) $this->createQueryBuilder('t')
            ->select('COUNT(p.id)')
            ->innerJoin('t.pages', 'p')
            ->where('t.id = :id')
            ->setParameter('id', $tag->getId())
            ->getQuery()
            ->getSingleScalarResult();
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
