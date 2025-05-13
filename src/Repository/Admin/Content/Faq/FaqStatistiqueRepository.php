<?php

namespace App\Repository\Admin\Content\Faq;

use App\Entity\Admin\Content\Faq\FaqStatistique;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FaqStatistique>
 *
 * @method FaqStatistique|null find($id, $lockMode = null, $lockVersion = null)
 * @method FaqStatistique|null findOneBy(array $criteria, array $orderBy = null)
 * @method FaqStatistique[]    findAll()
 * @method FaqStatistique[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FaqStatistiqueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FaqStatistique::class);
    }

    /**
     * Save
     * @param FaqStatistique $entity
     * @param bool $flush
     * @return void
     */
    public function save(FaqStatistique $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
