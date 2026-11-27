<?php

declare(strict_types=1);

namespace App\Repository\Admin\Content\Faq;

use App\Entity\Admin\Content\Faq\Faq;
use App\Entity\Admin\Content\Faq\FaqQuestion;
use App\Repository\Trait\TranslatedFieldQueryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FaqQuestion>
 *
 * @method FaqQuestion|null find($id, $lockMode = null, $lockVersion = null)
 * @method FaqQuestion|null findOneBy(array $criteria, array $orderBy = null)
 * @method FaqQuestion[]    findAll()
 * @method FaqQuestion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FaqQuestionRepository extends ServiceEntityRepository
{
    use TranslatedFieldQueryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FaqQuestion::class);
    }

    /**
     * Save
     * @param FaqQuestion $entity
     * @param bool $flush
     * @return void
     */
    public function save(FaqQuestion $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Questions triées d'une FAQ, sans leur réponse : identifiant, catégorie, position, statut et titre de $locale,
     * à défaut de $defaultLocale
     * @param Faq $faq
     * @param string $locale
     * @param string $defaultLocale
     * @return array<int, array{id: int, categoryId: int, position: int, disabled: bool, title: ?string}>
     */
    public function getAdminTreeRows(Faq $faq, string $locale, string $defaultLocale): array
    {
        $qb = $this->createQueryBuilder('question')
            ->select(
                'question.id',
                'IDENTITY(question.faqCategory) AS categoryId',
                'question.renderOrder AS position',
                'question.disabled',
            )
            ->innerJoin('question.faqCategory', 'category')
            ->where('category.faq = :faq')
            ->setParameter('faq', $faq)
            ->orderBy('question.renderOrder', 'ASC')
            ->addOrderBy('question.id', 'ASC');

        $title = $this->joinTranslatedField($qb, 'question.faqQuestionTranslations', 'title', $locale, $defaultLocale);
        return $qb
            ->addSelect($title . ' AS title')
            ->getQuery()
            ->getArrayResult();
    }
}
