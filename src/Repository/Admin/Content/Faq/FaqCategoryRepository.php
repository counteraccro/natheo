<?php

declare(strict_types=1);

namespace App\Repository\Admin\Content\Faq;

use App\Entity\Admin\Content\Faq\Faq;
use App\Entity\Admin\Content\Faq\FaqCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FaqCategory>
 *
 * @method FaqCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method FaqCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method FaqCategory[]    findAll()
 * @method FaqCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FaqCategoryRepository extends ServiceEntityRepository
{
    /**
     * Save
     * @param FaqCategory $entity
     * @param bool $flush
     * @return void
     */
    public function save(FaqCategory $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FaqCategory::class);
    }

    /**
     * Catégories triées d'une FAQ, avec leurs traductions
     * @param Faq $faq
     * @return FaqCategory[]
     */
    public function getAdminTreeCategories(Faq $faq): array
    {
        return $this->createQueryBuilder('category')
            ->addSelect('translation')
            ->leftJoin('category.faqCategoryTranslations', 'translation')
            ->where('category.faq = :faq')
            ->setParameter('faq', $faq)
            ->orderBy('category.renderOrder', 'ASC')
            ->addOrderBy('category.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
