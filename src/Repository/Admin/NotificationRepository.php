<?php

namespace App\Repository\Admin;

use App\Entity\Admin\Notification;
use App\Entity\Admin\System\User;
use App\Utils\Global\Database\RawQueryManager;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Notification>
 *
 * @method Notification|null find($id, $lockMode = null, $lockVersion = null)
 * @method Notification|null findOneBy(array $criteria, array $orderBy = null)
 * @method Notification[]    findAll()
 * @method Notification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    public function save(Notification $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Notification $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Retourne le nombre de notifications en fonction du User
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function getNbByUser(User $user)
    {
        return $this->createQueryBuilder('n')
            ->select('count(n.id)')
            ->andWhere('n.user = :user')
            ->setParameter('user', $user)
            ->andWhere('COALESCE(n.read, FALSE) = FALSE')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Retourne une liste de notification d'un utilisateur paginé
     * @param int $page
     * @param int $limit
     * @param User $user
     * @param bool $onlyNotRead
     * @return Paginator
     */
    public function getByUserPaginate(int $page, int $limit, User $user, bool $onlyNotRead): Paginator
    {
        $query = $this->createQueryBuilder('n')
            ->andWhere('n.user = :user')
            ->setParameter('user', $user)
            ->orderBy('n.createdAt', 'DESC');

        if ($onlyNotRead) {
            $query->andWhere('COALESCE(n.read, FALSE) = FALSE');
        }

        $paginator = new Paginator($query->getQuery(), true);
        $paginator
            ->getQuery()
            ->setFirstResult($limit * ($page - 1))
            ->setMaxResults($limit);
        return $paginator;
    }

    /**
     * Supprime les notifications lues en fonction du nombre de jours et de l'utilisateur
     * @param int $nbDay
     * @param int $userId
     * @return void
     * @throws Exception
     */
    public function removeAfterDay(int $nbDay, int $userId, RawQueryManager $rawQueryManager): void
    {
        $sql = $rawQueryManager->getQueryPurgeNotification();
        $statment = $this->getEntityManager()->getConnection()->prepare($sql);
        $statment->bindValue('nb_day', $nbDay);
        $statment->bindValue('user_id', $userId);
        $statment->executeStatement();
    }

    /**
     * Met en status lu l'ensemble des notifications non lu de l'utilisateur
     * @param User $user
     * @return void
     */
    public function readAll(User $user): void
    {
        $query = $this->createQueryBuilder('n')
            ->andWhere('n.user = :user')
            ->setParameter('user', $user)
            ->andWhere('COALESCE(n.read, FALSE) = FALSE');

        $result = $query->getQuery()->getResult();
        /** @var Notification $notification */
        foreach ($result as $notification) {
            $notification->setRead(true);
        }
        $this->getEntityManager()->flush();
    }
}
