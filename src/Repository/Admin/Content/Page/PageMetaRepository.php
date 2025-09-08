<?php

namespace App\Repository\Admin\Content\Page;

use App\Entity\Admin\Content\Page\PageMeta;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PageMeta>
 */
class PageMetaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PageMeta::class);
    }

    /**
     * Retourne l'ensemble des metas en fonction de la page et de la locale.
     * $page peut être un id ou un slug
     * @param mixed $page
     * @param string $locale
     * @return array
     */
    public function getMetasByPageAndLocale(string $locale, mixed $page = null): array
    {
        $query = $this->createQueryBuilder('pm')
            ->select('pm.name', 'pmt.value')
            ->join('pm.page', 'p')
            ->join('pm.pageMetaTranslations', 'pmt')
            ->andWhere('pmt.locale = :locale')
            ->setParameter('locale', $locale);

        if (is_int($page)) {
            $query->andWhere('p.id = :id')
                ->setParameter('id', $page);

        } else if (is_string($page)) {
            $query->join('p.pageTranslations', 'pt')
                ->andWhere('pt.url = :url')
                ->setParameter('url', $page);
        } else {
            $query->andWhere('p.landingPage = 1');
        }

        return $query->getQuery()->getResult();
    }

    //    /**
    //     * @return PageMeta[] Returns an array of PageMeta objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?PageMeta
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
