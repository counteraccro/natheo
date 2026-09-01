<?php

declare(strict_types=1);

namespace App\Repository\Admin\Content\Page;

use App\Entity\Admin\Content\Page\Page;
use App\Entity\Admin\Content\Page\PageStatistique;
use App\Enum\Admin\Content\Page\PageStatus;
use App\Utils\Global\Database\RawQueryManager;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PageStatistique>
 *
 * @method PageStatistique|null find($id, $lockMode = null, $lockVersion = null)
 * @method PageStatistique|null findOneBy(array $criteria, array $orderBy = null)
 * @method PageStatistique[]    findAll()
 * @method PageStatistique[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PageStatistiqueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PageStatistique::class);
    }

    public function save(PageStatistique $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Retourne un total de statistique en fonction d'un clée
     * @param string $key
     * @return int
     */
    public function getTotalStatByKey(string $key, RawQueryManager $rawQueryManager): int
    {
        $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
        $rsm->addScalarResult('nb', 'nb', 'integer');

        $query = $rawQueryManager->getQueryTotalStatByKey();

        $query = $this->getEntityManager()->createNativeQuery($query, $rsm);
        $query->setParameter('key', $key);
        $query->setParameter('status', PageStatus::PUBLISH->value);

        return (int) $query->getSingleScalarResult();
    }
}
