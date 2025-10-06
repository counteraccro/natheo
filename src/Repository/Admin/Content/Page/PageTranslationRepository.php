<?php

namespace App\Repository\Admin\Content\Page;

use App\Entity\Admin\Content\Page\PageTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
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
     * Test si l'url est unique ou non
     * @throws NonUniqueResultException
     */
    public function isUniqueUrl(string $url, ?int $id = null): bool
    {
        $query = $this->createQueryBuilder('pt')->andWhere('pt.url = :val')->setParameter('val', $url);

        if ($id !== null) {
            $query->andWhere('pt.id != :id')->setParameter('id', $id);
        }
        $result = $query->getQuery()->getOneOrNullResult();

        if ($result === null) {
            return false;
        }
        return true;
    }
}
