<?php

namespace App\Repository\Admin\System;

use App\Entity\Admin\System\OptionSystem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OptionSystem>
 *
 * @method OptionSystem|null find($id, $lockMode = null, $lockVersion = null)
 * @method OptionSystem|null findOneBy(array $criteria, array $orderBy = null)
 * @method OptionSystem[]    findAll()
 * @method OptionSystem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OptionSystemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OptionSystem::class);
    }

    public function save(OptionSystem $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(OptionSystem $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}

