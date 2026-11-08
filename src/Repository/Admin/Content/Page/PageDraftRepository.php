<?php

declare(strict_types=1);
/**
 * Repository des brouillons de pages publiées
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Repository\Admin\Content\Page;

use App\Entity\Admin\Content\Page\PageDraft;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PageDraft>
 *
 * @method PageDraft|null find($id, $lockMode = null, $lockVersion = null)
 * @method PageDraft|null findOneBy(array $criteria, array $orderBy = null)
 * @method PageDraft[]    findAll()
 * @method PageDraft[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PageDraftRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PageDraft::class);
    }

    public function save(PageDraft $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PageDraft $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Retourne les ids, parmi $pageIds, des pages qui ont un brouillon
     * @param int[] $pageIds
     * @return int[]
     */
    public function getPageIdsWithDraft(array $pageIds): array
    {
        if (empty($pageIds)) {
            return [];
        }

        $rows = $this->createQueryBuilder(PageDraft::DEFAULT_ALIAS)
            ->select('IDENTITY(' . PageDraft::DEFAULT_ALIAS . '.page) AS page_id')
            ->where(PageDraft::DEFAULT_ALIAS . '.page IN (:ids)')
            ->setParameter('ids', $pageIds)
            ->getQuery()
            ->getScalarResult();

        return array_map(fn(array $row) => (int) $row['page_id'], $rows);
    }
}
