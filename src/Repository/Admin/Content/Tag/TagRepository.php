<?php
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Répository pour les tags
 */

namespace App\Repository\Admin\Content\Tag;

use App\Entity\Admin\Content\Tag\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tag>
 *
 * @method Tag|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tag|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tag[]    findAll()
 * @method Tag[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tag::class);
    }

    public function save(Tag $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Tag $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Retourne une liste de tag Paginé
     * @param int $page
     * @param int $limit
     * @param string|null $search
     * @return Paginator
     */
    public function getAllPaginate(int $page, int $limit, string $search = null): Paginator
    {
        $query = $this->createQueryBuilder('t')
            ->orderBy('t.id', 'ASC');

        if ($search !== null) {
            $query->join('t.tagTranslations', 'tt')
                ->where('tt.label like :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $paginator = new Paginator($query->getQuery(), true);
        $paginator->getQuery()
            ->setFirstResult($limit * ($page - 1))
            ->setMaxResults($limit);
        return $paginator;

    }

    /**
     * Retourne une liste de tag en fonction de $search et de la locale
     * si withDisabled est à true, remonte aussi les tags disabled
     * @param string $locale
     * @param string $search
     * @param bool $withDisabled
     * @return array
     */
    public function searchByName(string $locale, string $search, bool $withDisabled = false): array
    {
        $query = $this->createQueryBuilder('t')
            ->select('t.id', 'tt.label', 't.disabled', 't.color')
            ->join('t.tagTranslations', 'tt', 'WITH', "tt.locale = '" . $locale . "'")
            ->andWhere('LOWER(tt.label) LIKE LOWER(:label)')
            ->setParameter('label', '%' . $search . '%');

        if (!$withDisabled) {
            $query->andWhere('t.disabled = false');
        }

        return $query->getQuery()
            ->getArrayResult();
    }

//    /**
//     * @return Tag[] Returns an array of Tag objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Tag
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
