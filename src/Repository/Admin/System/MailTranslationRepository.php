<?php

namespace App\Repository\Admin\System;

use App\Entity\Admin\System\MailTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MailTranslation>
 *
 * @method MailTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method MailTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method MailTranslation[]    findAll()
 * @method MailTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MailTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MailTranslation::class);
    }

    public function save(MailTranslation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MailTranslation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
