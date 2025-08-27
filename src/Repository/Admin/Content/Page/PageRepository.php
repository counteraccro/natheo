<?php

namespace App\Repository\Admin\Content\Page;

use App\Entity\Admin\Content\Page\Page;
use App\Utils\Content\Page\PageConst;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Parameter;
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
     * @param string|null $search
     * @param int|null $userId
     * @return Paginator
     */
    public function getAllPaginate(int $page, int $limit, ?string $search = null, ?int $userId = null): Paginator
    {
        $query = $this->createQueryBuilder('p')
            ->orderBy('p.id', 'ASC');
        if ($search !== null) {
            $query->join('p.pageTranslations', 'ppt')
                ->join('p.tags', 't')
                ->join('t.tagTranslations', 'tt')
                ->where('tt.label like :search')
                ->orWhere('ppt.titre like :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($userId !== null) {
            $query->andWhere('p.user = :userId');
            $query->setParameter('userId', $userId);
        }

        $paginator = new Paginator($query->getQuery(), true);
        $paginator->getQuery()
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
            ->setParameter('status', PageConst::STATUS_PUBLISH)
            ->orderBy('p.updateAt', 'DESC');

        if ($categoryId !== 0) {
            $query->andWhere('p.category = :categoryId')
                ->setParameter('categoryId', $categoryId);
        }

        $paginator = new Paginator($query->getQuery(), true);
        $paginator->getQuery()
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
            ->setParameter('tag', '%' . strtolower($tag) . '%')
            ->andWhere('p.disabled = :disabled')
            ->setParameter('disabled', false)
            ->andWhere('p.status = :status')
            ->setParameter('status', PageConst::STATUS_PUBLISH)
            ->orderBy('p.updateAt', 'DESC');


        $paginator = new Paginator($query->getQuery(), true);
        $paginator->getQuery()
            ->setFirstResult($limit * ($page - 1))
            ->setMaxResults($limit);
        return $paginator;
    }

    /**
     * Retourne toutes les pages sauf le champ $field avec $value
     * @param string $field
     * @param mixed $value
     * @return float|int|mixed|string
     */
    public function getAllWithoutExclude(string $field, mixed $value): mixed
    {
        $query = $this->createQueryBuilder('p');
        $query->where(
            $query->expr()->neq('p.' . $field, ':value')
        )
            ->setParameters(new ArrayCollection([
                new Parameter('value', $value),
            ]));
        return $query->getQuery()->getResult();
    }

    /**
     * Retourne une page en fonction de son slug
     * Si le slug est vide, renvoi la landingPage
     * @param string $slug
     * @param array $status
     * @return Page|null
     */
    public function getBySlug(string $slug, array $status = [PageConst::STATUS_PUBLISH]): ?Page
    {
        $query = $this->createQueryBuilder('p');

        if ($slug !== "") {
            $query->join('p.pageTranslations', 'pt')
                ->where('pt.url = :slug')
                ->setParameter('slug', $slug);
        } else {
            $query->where('p.landingPage = :landingPage')
                ->setParameter('landingPage', true);
        }

        $query->andWhere('p.disabled = :disabled')
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

        $query->join('p.pageTranslations', 'pt')
            ->leftJoin('p.pageContents', 'pc')
            ->leftJoin('pc.pageContentTranslations', 'pct')
            ->join('p.user', 'u')
            ->andWhere('pt.locale = :locale')
            ->andWhere('pt.titre like :search')
            ->orWhere('pc.type = :type AND pct.text like :search AND pct.locale = :locale')
            ->orWhere('u.login like :search')
            ->setParameter('search', '%' . $search . '%')
            ->setParameter('type', PageConst::CONTENT_TYPE_TEXT)
            ->setParameter('locale', $locale);

        $paginator = new Paginator($query->getQuery(), true);
        $paginator->getQuery()
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
}
