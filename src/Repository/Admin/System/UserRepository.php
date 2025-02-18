<?php

namespace App\Repository\Admin\System;

use App\Entity\Admin\System\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements UserLoaderInterface, PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function save(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Requête SQL pour authentification
     * @param string $identifier
     * @return User|null
     * @throws NonUniqueResultException
     */
    public function loadUserByIdentifier(string $identifier): ?User
    {
        $query = $this->createQueryBuilder('u')
        ->where('u.email = :email')
        ->setParameter('email', $identifier)
        ->andWhere('u.disabled = false')
        ->andWhere('u.anonymous = false');

        return $query->getQuery()->getOneOrNullResult();
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
        $query = $this->createQueryBuilder('u')
            ->orderBy('u.id', 'ASC');

        if($search !== null)
        {
            $query->where('u.email like :search');
            $query->orWhere('u.login like :search');
            $query->orWhere('u.firstname like :search');
            $query->orWhere('u.lastname like :search');
            $query->setParameter('search', '%' . $search . '%');
        }

        $paginator = new Paginator($query->getQuery(), true);
        $paginator->getQuery()
            ->setFirstResult($limit * ($page - 1))
            ->setMaxResults($limit);
        return $paginator;

    }

    /**
     * Retourne une liste d'utilisateur en fonction de son role
     * @param string $role
     * @return float|int|mixed|string
     * @deprecated RETOURNE UNIQUEMENT LE FOUNDER POUR LE MOMENT, A refaire car roles est un champ JSON
     */
    public function findByRole(string $role): mixed
    {
        // TODO à réécrire attention différent entre Mysql et PostGreSql

        return $this->createQueryBuilder('u')
            /**
             * Foncione que sous postgres
             * ->andWhere('CONTAINS(TO_JSONB(u.roles), :role) = TRUE')
             * ->setParameter('role', '["'.$role.'"]')
             */
            ->where('u.founder = 1')
        ->orderBy('u.id', 'ASC')
        ->getQuery()
        ->getResult();

    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);
        $this->save($user, true);
    }

    /**
     * Recherche dans les users
     * @param string $search
     * @param string $locale
     * @param int $page
     * @param int $limit
     * @return Paginator
     */
    public function search(string $search, string $locale, int $page, int $limit): Paginator
    {
        $query = $this->createQueryBuilder('u');

        $query->orWhere('u.login like :search')
            ->orWhere('u.email like :search')
            ->orWhere('u.firstname like :search')
            ->orWhere('u.lastname like :search')
            ->setParameter('search', '%' . $search . '%');

        $paginator = new Paginator($query->getQuery(), true);
        $paginator->getQuery()
            ->setFirstResult($limit * ($page - 1))
            ->setMaxResults($limit);
        return $paginator;
    }
}
