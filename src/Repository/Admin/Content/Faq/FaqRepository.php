<?php

declare(strict_types=1);

namespace App\Repository\Admin\Content\Faq;

use App\Dto\Api\Admin\AdminCollectionQuery;
use App\Entity\Admin\Content\Faq\Faq;
use App\Entity\Admin\Content\Faq\FaqCategory;
use App\Entity\Admin\Content\Faq\FaqCategoryTranslation;
use App\Entity\Admin\Content\Faq\FaqQuestion;
use App\Entity\Admin\Content\Faq\FaqQuestionTranslation;
use App\Entity\Admin\Content\Faq\FaqTranslation;
use App\Entity\Admin\Content\Page\PageContent;
use App\Entity\Admin\System\User;
use App\Enum\Admin\Content\Page\PageContentType;
use App\Repository\Trait\AdminCollectionQueryTrait;
use App\Repository\Trait\LikeQueryTrait;
use App\Repository\Trait\OrderedQueryTrait;
use App\Repository\Trait\TranslatedFieldQueryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\AST\Join;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Faq>
 *
 * @method Faq|null find($id, $lockMode = null, $lockVersion = null)
 * @method Faq|null findOneBy(array $criteria, array $orderBy = null)
 * @method Faq[]    findAll()
 * @method Faq[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FaqRepository extends ServiceEntityRepository
{
    use AdminCollectionQueryTrait;
    use LikeQueryTrait;
    use OrderedQueryTrait;
    use TranslatedFieldQueryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Faq::class);
    }

    /**
     * Sauvegarde
     * @param Faq $entity
     * @param bool $flush
     * @return void
     */
    public function save(Faq $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Supprime
     * @param Faq $entity
     * @param bool $flush
     * @return void
     */
    public function remove(Faq $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Page de la collection d'administration. Chaque ligne vaut [0 => Faq, 'title' => titre de $locale, à défaut de
     * $defaultLocale, 'categoryCount', 'questionCount', 'pageCount', 'authorId', 'authorLogin'] ; compteurs lus en
     * sous-requêtes, l'auteur reste un proxy non chargé
     * @param AdminCollectionQuery $query
     * @param string $locale
     * @param string $defaultLocale
     * @return Paginator
     */
    public function getAdminCollection(AdminCollectionQuery $query, string $locale, string $defaultLocale): Paginator
    {
        $qb = $this->createQueryBuilder('faq')
            ->addSelect(
                'author.id AS authorId',
                'author.login AS authorLogin',
                'SIZE(faq.faqCategories) AS categoryCount',
                '(SELECT COUNT(question.id) FROM ' .
                    FaqQuestion::class .
                    ' question JOIN question.faqCategory question_category' .
                    ' WHERE question_category.faq = faq) AS questionCount',
                '(SELECT COUNT(DISTINCT IDENTITY(page_content.page)) FROM ' .
                    PageContent::class .
                    ' page_content WHERE page_content.type = :faq_type AND page_content.typeId = faq.id) AS pageCount',
            )
            ->leftJoin('faq.user', 'author')
            ->setParameter('faq_type', PageContentType::FAQ->value);

        $title = $this->joinTranslatedField($qb, 'faq.faqTranslations', 'title', $locale, $defaultLocale);
        $qb->addSelect($title . ' AS title');

        return $this->paginateAdminCollection(
            $qb,
            $query,
            [
                'id' => 'faq.id',
                'title' => $title,
                'createdAt' => 'faq.createdAt',
                'updatedAt' => 'faq.updateAt',
            ],
            'faq.disabled',
            'faq.id',
            'faq.user',
            orderFields: ['title' => 'title'],
        );
    }

    /**
     * Retourne une liste de user Paginé
     * @param int $page
     * @param int $limit
     * @param array $queryParams
     * @return Paginator
     */
    public function getAllPaginate(int $page, int $limit, array $queryParams): Paginator
    {
        $orderField = $queryParams['orderField'] ?? '';
        $search = (string) ($queryParams['search'] ?? '');

        // Jointure limitée à la locale : une ligne par FAQ, tri et recherche sur le titre affiché
        $query = $this->createQueryBuilder(Faq::DEFAULT_ALIAS)
            ->leftJoin(
                Faq::DEFAULT_ALIAS . '.faqTranslations',
                FaqTranslation::DEFAULT_ALIAS,
                'WITH',
                FaqTranslation::DEFAULT_ALIAS . '.locale = :locale',
            )
            ->setParameter('locale', (string) ($queryParams['locale'] ?? ''));

        if ($orderField === 'title') {
            $this->applyOrdering($query, FaqTranslation::class, $queryParams);
        } else {
            $this->applyOrdering($query, Faq::class, $queryParams);
        }

        if ($search !== '') {
            $query
                ->andWhere(FaqTranslation::DEFAULT_ALIAS . '.title like :search')
                ->setParameter('search', '%' . $this->escapeLike($search) . '%');
        }

        if (isset($queryParams['userId']) && $queryParams['userId'] !== '') {
            $query->andWhere(Faq::DEFAULT_ALIAS . '.user = :userId');
            $query->setParameter('userId', $queryParams['userId']);
        }
        $paginator = new Paginator($query->getQuery(), true);
        $paginator
            ->getQuery()
            ->setFirstResult($limit * ($page - 1))
            ->setMaxResults($limit);
        return $paginator;
    }

    /**
     * Retourne une liste de FAQ sous la forme id => label en fonction de la locale précisé
     * @param string $locale
     * @param bool $disabled
     * @return array
     */
    public function getListeFaq(string $locale = 'fr', bool $disabled = false): array
    {
        $result = $this->createQueryBuilder(Faq::DEFAULT_ALIAS)
            ->select(Faq::DEFAULT_ALIAS . '.id', FaqTranslation::DEFAULT_ALIAS . '.title')
            ->leftJoin(Faq::DEFAULT_ALIAS . '.faqTranslations', FaqTranslation::DEFAULT_ALIAS)
            ->where(Faq::DEFAULT_ALIAS . '.disabled = :disabled')
            ->andWhere(FaqTranslation::DEFAULT_ALIAS . '.locale = :locale')
            ->setParameter('locale', $locale)
            ->setParameter('disabled', $disabled)
            ->getQuery()
            ->getArrayResult();

        $return = [];
        foreach ($result as $data) {
            $return[$data['id']] = $data['title'];
        }
        return $return;
    }

    /**
     * Recherche dans les faqs
     * @param string $search
     * @param string $locale
     * @param int $page
     * @param int $limit
     * @return Paginator
     */
    public function search(string $search, string $locale, int $page, int $limit): Paginator
    {
        $query = $this->createQueryBuilder(Faq::DEFAULT_ALIAS);

        $query
            ->join(Faq::DEFAULT_ALIAS . '.faqTranslations', FaqTranslation::DEFAULT_ALIAS)
            ->leftJoin(Faq::DEFAULT_ALIAS . '.faqCategories', FaqCategory::DEFAULT_ALIAS)
            ->leftJoin(FaqCategory::DEFAULT_ALIAS . '.faqCategoryTranslations', FaqCategoryTranslation::DEFAULT_ALIAS)
            ->leftJoin(FaqCategory::DEFAULT_ALIAS . '.faqQuestions', FaqQuestion::DEFAULT_ALIAS)
            ->leftJoin(FaqQuestion::DEFAULT_ALIAS . '.faqQuestionTranslations', FaqQuestionTranslation::DEFAULT_ALIAS)
            ->join(Faq::DEFAULT_ALIAS . '.user', User::DEFAULT_ALIAS)
            ->orWhere(
                FaqTranslation::DEFAULT_ALIAS .
                    '.title like :search AND ' .
                    FaqTranslation::DEFAULT_ALIAS .
                    '.locale = :locale',
            )
            ->orWhere(
                FaqCategoryTranslation::DEFAULT_ALIAS .
                    '.title like :search AND ' .
                    FaqCategoryTranslation::DEFAULT_ALIAS .
                    '.locale = :locale',
            )
            ->orWhere(
                FaqQuestionTranslation::DEFAULT_ALIAS .
                    '.title like :search AND ' .
                    FaqQuestionTranslation::DEFAULT_ALIAS .
                    '.locale = :locale',
            )
            ->orWhere(
                FaqQuestionTranslation::DEFAULT_ALIAS .
                    '.answer like :search AND ' .
                    FaqQuestionTranslation::DEFAULT_ALIAS .
                    '.locale = :locale',
            )
            ->orWhere(User::DEFAULT_ALIAS . '.login like :search')
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
