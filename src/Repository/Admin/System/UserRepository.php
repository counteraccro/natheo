<?php

declare(strict_types=1);

namespace App\Repository\Admin\System;

use App\Entity\Admin\System\User;
use App\Repository\Trait\OrderedQueryTrait;
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
    use OrderedQueryTrait;

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
    public function getAllPaginate(int $page, int $limit, array $queryParams): Paginator
    {
        $query = $this->createQueryBuilder(User::DEFAULT_ALIAS);
        $this->applyOrdering($query, User::class, $queryParams);

        if (isset($queryParams['search']) && $queryParams['search'] !== '') {
            $query->where(User::DEFAULT_ALIAS . '.email like :search');
            $query->orWhere(User::DEFAULT_ALIAS . '.login like :search');
            $query->orWhere(User::DEFAULT_ALIAS . '.firstname like :search');
            $query->orWhere(User::DEFAULT_ALIAS . '.lastname like :search');
            $query->setParameter('search', '%' . $queryParams['search'] . '%');
        }

        $paginator = new Paginator($query->getQuery(), true);
        $paginator
            ->getQuery()
            ->setFirstResult($limit * ($page - 1))
            ->setMaxResults($limit);
        return $paginator;
    }

    /**
     * Retourne la liste des utilisateurs actifs (non désactivés, non anonymisés) ayant le rôle
     * Le filtre sur le rôle est fait en PHP car le champ JSON roles ne se requête pas de la même façon
     * sous MySQL et PostgreSQL
     * @param string $role
     * @return User[]
     */
    public function findByRole(string $role): array
    {
        $users = $this->createQueryBuilder('u')
            ->where('u.disabled = false')
            ->andWhere('u.anonymous = false')
            ->orderBy('u.id', 'ASC')
            ->getQuery()
            ->getResult();

        return array_values(array_filter($users, fn(User $user) => in_array($role, $user->getRoles(), true)));
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!($user instanceof User)) {
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

        $query
            ->orWhere('u.login like :search')
            ->orWhere('u.email like :search')
            ->orWhere('u.firstname like :search')
            ->orWhere('u.lastname like :search')
            ->setParameter('search', '%' . $search . '%');

        $paginator = new Paginator($query->getQuery(), true);
        $paginator
            ->getQuery()
            ->setFirstResult($limit * ($page - 1))
            ->setMaxResults($limit);
        return $paginator;
    }
}
