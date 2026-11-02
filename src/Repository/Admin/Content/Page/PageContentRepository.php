<?php

declare(strict_types=1);

namespace App\Repository\Admin\Content\Page;

use App\Entity\Admin\Content\Page\PageContent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PageContent>
 *
 * @method PageContent|null find($id, $lockMode = null, $lockVersion = null)
 * @method PageContent|null findOneBy(array $criteria, array $orderBy = null)
 * @method PageContent[]    findAll()
 * @method PageContent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PageContentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PageContent::class);
    }

    /**
     * Retourne le nombre de pages distinctes utilisant un contenu (ex. une FAQ) via un PageContent
     * @param int $type valeur de PageContentType
     * @param int $typeId identifiant du contenu
     * @return int
     */
    public function countPagesByTypeAndTypeId(int $type, int $typeId): int
    {
        return (int) $this->createQueryBuilder('pc')
            ->select('COUNT(DISTINCT IDENTITY(pc.page))')
            ->where('pc.type = :type')
            ->andWhere('pc.typeId = :typeId')
            ->setParameter('type', $type)
            ->setParameter('typeId', $typeId)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
