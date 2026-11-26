<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 2.1
 * Repository pour les commentaires
 */
namespace App\Repository\Admin\Content\Comment;

use App\Dto\Api\Admin\AdminCollectionQuery;
use App\Dto\Api\Content\Comment\ApiCommentByPageDto;
use App\Entity\Admin\Content\Comment\Comment;
use App\Entity\Admin\Content\Page\Page;
use App\Enum\Admin\Comment\CommentStatus;
use App\Repository\Trait\AdminCollectionQueryTrait;
use App\Repository\Trait\OrderedQueryTrait;
use App\Repository\Trait\TranslatedFieldQueryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Comment>
 */
class CommentRepository extends ServiceEntityRepository
{
    use AdminCollectionQueryTrait;
    use OrderedQueryTrait;
    use TranslatedFieldQueryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    /**
     * Page de la collection d'administration. Le titre de la page et le modérateur sont lus en scalaires dans la
     * même requête (pageId, pageTitle, moderatorId, moderatorLogin), sans jointure de collection
     * @param AdminCollectionQuery $query
     * @param CommentStatus|null $status
     * @param int|null $pageId
     * @param string $locale langue de contenu
     * @param string $defaultLocale
     * @return Paginator
     */
    public function getAdminCollection(
        AdminCollectionQuery $query,
        ?CommentStatus $status,
        ?int $pageId,
        string $locale,
        string $defaultLocale,
    ): Paginator {
        $qb = $this->createQueryBuilder('comment')
            ->addSelect(
                'page.id AS pageId',
                'moderator.id AS moderatorId',
                'moderator.login AS moderatorLogin',
            )
            ->innerJoin('comment.page', 'page')
            ->leftJoin('comment.userModeration', 'moderator');

        $title = $this->joinTranslatedField($qb, 'page.pageTranslations', 'titre', $locale, $defaultLocale);
        $qb->addSelect($title . ' AS pageTitle');

        if ($status !== null) {
            $qb->andWhere('comment.status = :status')->setParameter('status', $status->value);
        }
        if ($pageId !== null) {
            $qb->andWhere('page.id = :page_id')->setParameter('page_id', $pageId);
        }

        return $this->paginateAdminCollection(
            $qb,
            $query,
            [
                'id' => 'comment.id',
                'comment' => 'comment.comment',
                'author' => 'comment.author',
                'email' => 'comment.email',
                'page' => $title,
                'status' => 'comment.status',
                'createdAt' => 'comment.createdAt',
            ],
            null,
            'comment.id',
            'comment.userModeration',
            orderFields: ['page' => 'pageTitle'],
        );
    }

    /**
     * Sauvegarde
     * @param Comment $entity
     * @param bool $flush
     * @return void
     */
    public function save(Comment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Retourne le nombre de commentaire en fonction du status
     * @return array
     */
    public function getNbGroupByStatus(): array
    {
        $query = $this->createQueryBuilder(Comment::DEFAULT_ALIAS)
            ->select('COUNT(' . Comment::DEFAULT_ALIAS . '.id) as nb', Comment::DEFAULT_ALIAS . '.status')
            ->groupBy(Comment::DEFAULT_ALIAS . '.status');
        return $query->getQuery()->getArrayResult();
    }

    /**
     * Retourne une liste de commentaires en fonction du Dto
     * Seuls les commentaires actifs d'une page active dont le status est dans $pageStatus sont retournés
     * @param ApiCommentByPageDto $dto
     * @param array $pageStatus
     * @return Paginator
     */
    public function getCommentsByPageForApi(ApiCommentByPageDto $dto, array $pageStatus): Paginator
    {
        $query = $this->createQueryBuilder('c')
            ->join('c.page', 'p')
            ->andWhere('c.disabled = false')
            ->andWhere('p.disabled = false')
            ->andWhere('p.status IN (:pageStatus)')
            ->setParameter('pageStatus', $pageStatus);

        if ($dto->getId() !== 0) {
            $query->andWhere('c.page = :id')->setParameter('id', $dto->getId());
        } else {
            $query
                ->join('p.pageTranslations', 'pt')
                ->andWhere('pt.url = :slug')
                ->setParameter('slug', $dto->getPageSlug())
                ->andWhere('pt.locale = :locale')
                ->setParameter('locale', $dto->getLocale());
        }

        $query->orderBy('c.' . $dto->getOrderBy(), $this->toSortDirection($dto->getOrder()));

        $paginator = new Paginator($query->getQuery(), true);
        $paginator
            ->getQuery()
            ->setFirstResult($dto->getLimit() * ($dto->getPage() - 1))
            ->setMaxResults($dto->getLimit());
        return $paginator;
    }

    /**
     * Retourne les commentaires actifs d'une page, paginés et triés par date de création
     * @param Page $page
     * @param int $pageNumber
     * @param int $limit
     * @param \SortDirection $direction
     * @return Paginator<Comment>
     */
    public function getActiveCommentsByPage(
        Page $page,
        int $pageNumber,
        int $limit,
        \SortDirection $direction,
    ): Paginator {
        $query = $this->createQueryBuilder('c')
            ->andWhere('c.page = :page')
            ->setParameter('page', $page)
            ->andWhere('c.disabled = false')
            ->orderBy('c.createdAt', $direction)
            ->addOrderBy('c.id', $direction)
            ->setFirstResult($limit * ($pageNumber - 1))
            ->setMaxResults($limit);

        return new Paginator($query->getQuery(), false);
    }
}
