<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Répository pour les tags
 */

namespace App\Repository\Admin\Content\Tag;

use App\Dto\Api\Admin\AdminCollectionQuery;
use App\Entity\Admin\Content\Tag\Tag;
use App\Entity\Admin\Content\Tag\TagTranslation;
use App\Repository\Trait\AdminCollectionQueryTrait;
use App\Repository\Trait\LikeQueryTrait;
use App\Repository\Trait\TranslatedFieldQueryTrait;
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
    use AdminCollectionQueryTrait;
    use LikeQueryTrait;
    use TranslatedFieldQueryTrait;

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
     * Page de la collection d'administration. Chaque ligne vaut [0 => Tag, 'label' => libellé de $locale, à défaut
     * de $defaultLocale, 'pageCount' => nombre de pages, 'authorId', 'authorLogin'] ; l'auteur du tag reste un
     * proxy non chargé, seuls son identifiant et son login sont lus
     * @param AdminCollectionQuery $query
     * @param string $locale
     * @param string $defaultLocale
     * @return Paginator
     */
    public function getAdminCollection(AdminCollectionQuery $query, string $locale, string $defaultLocale): Paginator
    {
        $qb = $this->createQueryBuilder('tag')
            ->addSelect('author.id AS authorId', 'author.login AS authorLogin', 'SIZE(tag.pages) AS pageCount')
            ->leftJoin('tag.user', 'author');

        $label = $this->joinTranslatedField($qb, 'tag.tagTranslations', 'label', $locale, $defaultLocale);
        $qb->addSelect($label . ' AS label');

        return $this->paginateAdminCollection(
            $qb,
            $query,
            [
                'id' => 'tag.id',
                'label' => $label,
                'color' => 'tag.color',
                'createdAt' => 'tag.createdAt',
                'updatedAt' => 'tag.updateAt',
            ],
            'tag.disabled',
            'tag.id',
            'tag.user',
            orderFields: ['label' => 'label'],
        );
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
