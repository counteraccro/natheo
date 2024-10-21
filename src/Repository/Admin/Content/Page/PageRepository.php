<?php

namespace App\Repository\Admin\Content\Page;

use App\Entity\Admin\Content\Page\Page;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Page>
 *
 * @method Page|null find($id, $lockMode = null, $lockVersion = null)
 * @method Page|null findOneBy(array $criteria, array $orderBy = null)
 * @method Page[]    findAll()
 * @method Page[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Page::class);
    }

    public function save(Page $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Page $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Retourne une liste de page Paginé
     * @param int $page
     * @param int $limit
     * @param string|null $search
     * @return Paginator
     */
    public function getAllPaginate(int $page, int $limit, string $search = null): Paginator
    {
        $query = $this->createQueryBuilder('p')
            ->orderBy('p.id', 'ASC');
        if ($search !== null) {
            $query->join('p.pageTranslations', 'ppt')
                ->join('p.tags', 't')
                ->join('t.tagTranslations', 'tt')
                ->where('tt.label like :search')
                ->orWhere('ppt.titre like :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $paginator = new Paginator($query->getQuery(), true);
        $paginator->getQuery()
            ->setFirstResult($limit * ($page - 1))
            ->setMaxResults($limit);
        return $paginator;

    }

    /**
     * Retourne toutes les pages sauf le champ $field avec $value
     * @param string $field
     * @param mixed $value
     * @return float|int|mixed|string
     */
    public function getAllWithoutExclude(string $field, mixed $value): mixed
    {
        $query = $this->createQueryBuilder('p');
        $query->where(
            $query->expr()->neq('p.' . $field, ':value')
        )
            ->setParameters([
                'value' => $value,
            ]);
        return $query->getQuery()->getResult();
    }

    /**
     * Retourne une page en fonction de son slug
     * @param string $slug
     * @return array
     */
    public function getBySlug(string $slug) : array
    {
        $query = $this->createQueryBuilder('p');
        $query->join('p.pageTranslations', 'pt')
            ->where('pt.url = :slug')
            ->setParameter('slug', $slug)
            ->setMaxResults(1);
        return $query->getQuery()->getResult();

    }

//    /**
//     * @return Page[] Returns an array of Page objects
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

//    public function findOneBySomeField($value): ?Page
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
