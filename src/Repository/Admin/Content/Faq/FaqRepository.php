<?php

namespace App\Repository\Admin\Content\Faq;

use App\Entity\Admin\Content\Faq\Faq;
use App\Entity\Admin\Content\Faq\FaqCategory;
use App\Entity\Admin\Content\Faq\FaqCategoryTranslation;
use App\Entity\Admin\Content\Faq\FaqQuestion;
use App\Entity\Admin\Content\Faq\FaqQuestionTranslation;
use App\Entity\Admin\Content\Faq\FaqTranslation;
use App\Entity\Admin\System\User;
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
     * Retourne une liste de user Paginé
     * @param int $page
     * @param int $limit
     * @param array $queryParams
     * @return Paginator
     */
    public function getAllPaginate(int $page, int $limit, array $queryParams): Paginator
    {
        $orderField = 'id';
        $order = 'DESC';
        if (isset($queryParams['orderField']) && $queryParams['orderField'] !== '') {
            $orderField = $queryParams['orderField'];
        }

        if (isset($queryParams['order']) && $queryParams['order'] !== '') {
            $order = $queryParams['order'];
        }

        $query = $this->createQueryBuilder(Faq::DEFAULT_ALIAS)->orderBy(Faq::DEFAULT_ALIAS . '.' . $orderField, $order);

        if (isset($queryParams['search']) && $queryParams['search'] !== '') {
            $query
                ->join(Faq::DEFAULT_ALIAS . '.faqTranslations', FaqTranslation::DEFAULT_ALIAS)
                ->andWhere(FaqTranslation::DEFAULT_ALIAS . '.locale = :locale')
                ->andWhere(FaqTranslation::DEFAULT_ALIAS . '.title like :search')
                ->setParameter('search', '%' . $queryParams['search'] . '%')
                ->setParameter('locale', $queryParams['locale']);
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
