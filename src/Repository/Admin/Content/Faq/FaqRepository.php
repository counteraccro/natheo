<?php

namespace App\Repository\Admin\Content\Faq;

use App\Entity\Admin\Content\Faq\Faq;
use App\Entity\Admin\Content\Faq\FaqTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\AST\Join;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Faq>
 *
 * @method Faq|null find($id, $lockMode = null, $lockVersion = null)
 * @method Faq|null findOneBy(array $criteria, array $orderBy = null)
 * @method Faq[]    findAll()
 * @method Faq[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FaqRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Faq::class);
    }

    /**
     * Sauvegarde
     * @param Faq $entity
     * @param bool $flush
     * @return void
     */
    public function save(Faq $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Supprime
     * @param Faq $entity
     * @param bool $flush
     * @return void
     */
    public function remove(Faq $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Retourne une liste de user Paginé
     * @param int $page
     * @param int $limit
     * @param string|null $search
     * @return Paginator
     */
    public function getAllPaginate(int $page, int $limit, string $search = null): Paginator
    {
        $query = $this->createQueryBuilder('f')
            ->orderBy('f.id', 'ASC');

        if ($search !== null) {
            $query->join('f.faqTranslations', 'ft')
                ->where('ft.title like :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $paginator = new Paginator($query->getQuery(), true);
        $paginator->getQuery()
            ->setFirstResult($limit * ($page - 1))
            ->setMaxResults($limit);
        return $paginator;

    }

    /**
     * Retourne une liste de FAQ sous la forme id => label en fonction de la locale précisé
     * @param string $locale
     * @param bool $disabled
     * @return array
     */
    public function getListeFaq(string $locale = 'fr', bool $disabled = false): array
    {
        $result =  $this->createQueryBuilder('f')
            ->select('f.id', 'ft.title')
            ->leftJoin('f.faqTranslations', 'ft')
            ->where('f.disabled = :disabled')
            ->andWhere('ft.locale = :locale')
            ->setParameter('locale', $locale)
            ->setParameter('disabled', $disabled)
            ->getQuery()->getArrayResult();

        $return = array();
        foreach($result as $data)
        {
            $return[$data['id']] = $data['title'];
        }
        return $return;
    }
}
