<?php

namespace App\Repository\Admin\System;

use App\Entity\Admin\System\OptionUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OptionUser>
 *
 * @method OptionUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method OptionUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method OptionUser[]    findAll()
 * @method OptionUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OptionUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OptionUser::class);
    }

    public function save(OptionUser $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(OptionUser $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
