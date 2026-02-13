<?php

namespace App\Repository\Admin\System;

use App\Entity\Admin\System\Mail;
use App\Entity\Admin\System\MailTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Mail>
 *
 * @method Mail|null find($id, $lockMode = null, $lockVersion = null)
 * @method Mail|null findOneBy(array $criteria, array $orderBy = null)
 * @method Mail[]    findAll()
 * @method Mail[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MailRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Mail::class);
    }

    public function save(Mail $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Mail $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Retourne une liste de Mail Paginé
     * @param int $page
     * @param int $limit
     * @param array $queryParams
     * @return Paginator
     */
    public function getAllPaginate(int $page, int $limit, array $queryParams): Paginator
    {
        $orderField = 'id';
        $order = 'DESC';
        if (isset($queryParams['orderField']) && $queryParams['orderField'] !== '') {
            $orderField = $queryParams['orderField'];
        }

        if (isset($queryParams['order']) && $queryParams['order'] !== '') {
            $order = $queryParams['order'];
        }

        $query = $this->createQueryBuilder(Mail::DEFAULT_ALIAS)->orderBy(
            Mail::DEFAULT_ALIAS . '.' . $orderField,
            $order,
        );

        if (isset($queryParams['search']) && $queryParams['search'] !== '') {
            $query->join(Mail::DEFAULT_ALIAS . '.mailTranslations', MailTranslation::DEFAULT_ALIAS);
            $query->andWhere(MailTranslation::DEFAULT_ALIAS . '.locale = :locale');
            $query->andWhere(
                $query
                    ->expr()
                    ->orX(
                        $query->expr()->like(MailTranslation::DEFAULT_ALIAS . '.title', ':search1'),
                        $query->expr()->like(MailTranslation::DEFAULT_ALIAS . '.content', ':search2'),
                    ),
            );
            $query->setParameter('locale', $queryParams['locale']);
            $query->setParameter('search1', '%' . $queryParams['search'] . '%');
            $query->setParameter('search2', '%' . $queryParams['search'] . '%');
        }

        $doctrineQuery = $query->getQuery();

        $paginator = new Paginator($query->getQuery(), true);
        $paginator
            ->getQuery()
            ->setFirstResult($limit * ($page - 1))
            ->setMaxResults($limit);
        return $paginator;
    }

    /**
     * Retourne un email en fonction de sa clé
     * @param string $key
     * @return Mail
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function findByKey(string $key): Mail
    {
        return $this->createQueryBuilder(Mail::DEFAULT_ALIAS)
            ->andWhere(Mail::DEFAULT_ALIAS . '.key = :key')
            ->setParameter('key', $key)
            ->getQuery()
            ->getSingleResult();
    }
}
