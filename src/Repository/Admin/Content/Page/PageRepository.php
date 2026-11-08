<?php

declare(strict_types=1);

namespace App\Repository\Admin\Content\Page;

use App\Entity\Admin\Content\Page\Page;
use App\Entity\Admin\Content\Page\PageTranslation;
use App\Entity\Admin\Content\Tag\Tag;
use App\Entity\Admin\Content\Tag\TagTranslation;
use App\Enum\Admin\Content\Page\PageContentType;
use App\Enum\Admin\Content\Page\PageStatus;
use App\Enum\Admin\Content\Page\PageValidationStatus;
use App\Repository\Trait\OrderedQueryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Page>
 *
 * @method Page|null find($id, $lockMode = null, $lockVersion = null)
 * @method Page|null findOneBy(array $criteria, array $orderBy = null)
 * @method Page[]    findAll()
 * @method Page[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PageRepository extends ServiceEntityRepository
{
    use OrderedQueryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Page::class);
    }

    public function save(Page $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Page $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Retourne une liste de page Paginé
     * @param int $page
     * @param int $limit
     * @param array $queryParams
     * @param int|null $userId
     * @return Paginator
     */
    public function getAllPaginate(int $page, int $limit, array $queryParams, ?int $userId = null): Paginator
    {
        $query = $this->createQueryBuilder(Page::DEFAULT_ALIAS);
        $this->applyOrdering($query, Page::class, $queryParams);

        $query
            ->join(Page::DEFAULT_ALIAS . '.pageTranslations', PageTranslation::DEFAULT_ALIAS)
            ->where(PageTranslation::DEFAULT_ALIAS . '.locale = :locale')
            ->setParameter('locale', $queryParams['locale']);

        if (isset($queryParams['search']) && $queryParams['search'] !== '') {
            // leftJoin pour conserver les pages sans tag dont le titre correspond
            $query
                ->leftJoin(Page::DEFAULT_ALIAS . '.tags', Tag::DEFAULT_ALIAS)
                ->leftJoin(
                    Tag::DEFAULT_ALIAS . '.tagTranslations',
                    TagTranslation::DEFAULT_ALIAS,
                    'WITH',
                    TagTranslation::DEFAULT_ALIAS . '.locale = :locale',
                )
                ->andWhere(
                    $query
                        ->expr()
                        ->orX(
                            TagTranslation::DEFAULT_ALIAS . '.label like :search',
                            PageTranslation::DEFAULT_ALIAS . '.titre like :search',
                        ),
                )
                ->setParameter('search', '%' . $queryParams['search'] . '%');
        }

        if ($userId !== null) {
            $query->andWhere(Page::DEFAULT_ALIAS . '.user = :userId');
            $query->setParameter('userId', $userId);
        }

        if (isset($queryParams['validationStatus'])) {
            $query->andWhere(Page::DEFAULT_ALIAS . '.validationStatus = :validationStatus');
            $query->setParameter('validationStatus', (int) $queryParams['validationStatus']);
        }

        $paginator = new Paginator($query->getQuery(), true);
        $paginator
            ->getQuery()
            ->setFirstResult($limit * ($page - 1))
            ->setMaxResults($limit);
        return $paginator;
    }

    /**
     * Retourne une liste de page en fonction de la catégorie
     * Si la catégorie vaux zero, renvoi toutes les pages
     * @param int $page
     * @param int $limit
     * @param int $categoryId
     * @return Paginator
     */
    public function getPagesByCategoryPaginate(int $page, int $limit, int $categoryId = 0): Paginator
    {
        $query = $this->createQueryBuilder('p')
            ->where('p.disabled = :disabled')
            ->setParameter('disabled', false)
            ->andWhere('p.status = :status')
            ->setParameter('status', PageStatus::PUBLISH->value)
            ->orderBy('p.updateAt', \SortDirection::Descending);

        if ($categoryId !== 0) {
            $query->andWhere('p.category = :categoryId')->setParameter('categoryId', $categoryId);
        }

        $paginator = new Paginator($query->getQuery(), true);
        $paginator
            ->getQuery()
            ->setFirstResult($limit * ($page - 1))
            ->setMaxResults($limit);
        return $paginator;
    }

    /**
     * Retourne une liste de page en fonction du tag
     * @param int $page
     * @param int $limit
     * @param string $tag
     * @return Paginator
     */
    public function getPagesByTagPaginate(int $page, int $limit, string $tag): Paginator
    {
        $query = $this->createQueryBuilder('p')
            ->join('p.tags', 't')
            ->join('t.tagTranslations', 'tt')
            ->where('LOWER(tt.label) like :tag')
            ->setParameter('tag', '%' . addcslashes(strtolower($tag), '%_\\') . '%')
            ->andWhere('p.disabled = :disabled')
            ->setParameter('disabled', false)
            ->andWhere('p.status = :status')
            ->setParameter('status', PageStatus::PUBLISH->value)
            ->orderBy('p.updateAt', \SortDirection::Descending);

        $paginator = new Paginator($query->getQuery(), true);
        $paginator
            ->getQuery()
            ->setFirstResult($limit * ($page - 1))
            ->setMaxResults($limit);
        return $paginator;
    }

    /**
     * Retourne une page en fonction de son slug
     * Si le slug est vide, renvoi la landingPage
     * L'URL n'est unique que par locale : la page dont le slug est celui de $locale est prioritaire,
     * à défaut une page dont le slug existe dans une autre locale (page non traduite)
     * @param string $slug
     * @param array $status
     * @param string|null $locale
     * @return Page|null
     */
    public function getBySlug(string $slug, array $status = [PageStatus::PUBLISH->value], ?string $locale = null): ?Page
    {
        $query = $this->createQueryBuilder('p');

        if ($slug !== '') {
            $query->join('p.pageTranslations', 'pt')->where('pt.url = :slug')->setParameter('slug', $slug);
            if ($locale !== null) {
                $query
                    ->addSelect('CASE WHEN pt.locale = :locale THEN 0 ELSE 1 END AS HIDDEN localePriority')
                    ->setParameter('locale', $locale)
                    ->orderBy('localePriority', \SortDirection::Ascending);
            }
            $query->addOrderBy('p.id', \SortDirection::Ascending);
        } else {
            $query->where('p.landingPage = :landingPage')->setParameter('landingPage', true);
        }

        $query
            ->andWhere('p.disabled = :disabled')
            ->setParameter('disabled', false)
            ->andWhere('p.status IN (:status)')
            ->setParameter('status', $status)
            ->setMaxResults(1);
        return $query->getQuery()->getOneOrNullResult();
    }

    /**
     * Recherche dans les pages
     * @param string $search
     * @param string $locale
     * @param int $page
     * @param int $limit
     * @return Paginator
     */
    public function search(string $search, string $locale, int $page, int $limit): Paginator
    {
        $query = $this->createQueryBuilder('p');

        $query
            ->join('p.pageTranslations', 'pt')
            ->leftJoin('p.pageContents', 'pc')
            ->leftJoin('pc.pageContentTranslations', 'pct')
            ->join('p.user', 'u')
            ->andWhere('pt.locale = :locale')
            ->andWhere('pt.titre like :search')
            ->orWhere('pc.type = :type AND pct.text like :search AND pct.locale = :locale')
            ->orWhere('u.login like :search')
            ->setParameter('search', '%' . $search . '%')
            ->setParameter('type', PageContentType::TEXT->value)
            ->setParameter('locale', $locale);

        $paginator = new Paginator($query->getQuery(), true);
        $paginator
            ->getQuery()
            ->setFirstResult($limit * ($page - 1))
            ->setMaxResults($limit);
        return $paginator;
    }

    /**
     * Retourne une liste d'id et de titre de page en fonction de la locale
     * @param string $locale
     * @return array
     */
    public function getTitleAllPageByLocale(string $locale): array
    {
        $query = $this->createQueryBuilder('p');
        $query
            ->select('p.id', 'pt.titre')
            ->join('p.pageTranslations', 'pt')
            ->where('pt.locale = :locale')
            ->setParameter('locale', $locale);

        return $query->getQuery()->getArrayResult();
    }

    /**
     * Passe la page $pageId en landing page et toutes les autres à false, en une seule requête
     * @param int $pageId
     * @return void
     */
    public function switchLandingPage(int $pageId): void
    {
        $this->createQueryBuilder('p')
            ->update()
            ->set('p.landingPage', 'CASE WHEN p.id = :id THEN true ELSE false END')
            ->setParameter('id', $pageId)
            ->getQuery()
            ->execute();
    }

    /**
     * Retourne l'id, la locale, le titre et l'url de toutes les traductions de pages
     * @return array<int, array{id: int, locale: string, titre: string, url: string}>
     */
    public function getAllTitleAndUrl(): array
    {
        return $this->createQueryBuilder('p')
            ->select('p.id', 'pt.locale', 'pt.titre', 'pt.url')
            ->join('p.pageTranslations', 'pt')
            ->orderBy('p.id', \SortDirection::Descending)
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * Retourne les pages en attente de validation, les plus anciennes soumissions en premier
     * @param int $limit
     * @return Page[]
     */
    public function findPendingValidation(int $limit): array
    {
        return $this->createQueryBuilder('p')
            ->addSelect('vu')
            ->leftJoin('p.validationUser', 'vu')
            ->where('p.validationStatus = :validationStatus')
            ->setParameter('validationStatus', PageValidationStatus::PENDING->value)
            ->orderBy('p.validationAt', \SortDirection::Ascending)
            ->addOrderBy('p.id', \SortDirection::Ascending)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
