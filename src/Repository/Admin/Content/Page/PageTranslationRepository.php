<?php

declare(strict_types=1);

namespace App\Repository\Admin\Content\Page;

use App\Entity\Admin\Content\Page\PageTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PageTranslation>
 *
 * @method PageTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method PageTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method PageTranslation[]    findAll()
 * @method PageTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PageTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PageTranslation::class);
    }

    /**
     * Vérifie qu'aucune autre page n'utilise déjà l'url dans la locale
     * @param string $url
     * @param string $locale
     * @param int|null $pageId page à exclure (page en cours d'édition)
     * @return bool
     */
    public function isUniqueUrl(string $url, string $locale, ?int $pageId = null): bool
    {
        $query = $this->createQueryBuilder('pt')
            ->select('COUNT(pt.id)')
            ->where('pt.url = :url')
            ->andWhere('pt.locale = :locale')
            ->setParameter('url', $url)
            ->setParameter('locale', $locale);

        if ($pageId !== null) {
            $query->andWhere('IDENTITY(pt.page) != :pageId')->setParameter('pageId', $pageId);
        }
        return (int) $query->getQuery()->getSingleScalarResult() === 0;
    }
}
