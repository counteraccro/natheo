<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Repository des sessions API des utilisateurs
 */

namespace App\Repository\Admin\System;

use App\Entity\Admin\System\User;
use App\Entity\Admin\System\UserSession;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserSession>
 */
class UserSessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserSession::class);
    }

    /**
     * @param UserSession $entity
     * @param bool $flush
     * @return void
     */
    public function save(UserSession $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Sessions de l'utilisateur dont le refresh token n'a pas expiré, la plus récemment utilisée en premier
     * @param User $user
     * @return UserSession[]
     */
    public function findActiveByUser(User $user): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.user = :user')
            ->setParameter('user', $user)
            ->andWhere('s.refreshTokenExpiresAt > :now')
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('s.lastUsedAt', \SortDirection::Descending)
            ->addOrderBy('s.id', \SortDirection::Descending)
            ->getQuery()
            ->getResult();
    }

    /**
     * Supprime les sessions de l'utilisateur, ou seulement celles dont le refresh token a expiré
     * @param User $user
     * @param bool $expiredOnly
     * @return int nombre de sessions supprimées
     */
    public function deleteByUser(User $user, bool $expiredOnly = false): int
    {
        $query = $this->createQueryBuilder('s')->delete()->where('s.user = :user')->setParameter('user', $user);
        if ($expiredOnly) {
            $query->andWhere('s.refreshTokenExpiresAt <= :now')->setParameter('now', new \DateTimeImmutable());
        }
        return $query->getQuery()->execute();
    }
}
