<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Repository des modules et opérations désactivés du back-office
 */

namespace App\Repository\Admin\System;

use App\Entity\Admin\System\ModuleActivation;
use App\Enum\Admin\Module\ActivationType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ModuleActivation>
 */
class ModuleActivationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ModuleActivation::class);
    }

    /**
     * Noms des éléments désactivés, groupés par type
     * @return array<string, string[]> clé : valeur de ActivationType
     */
    public function findDisabledNames(): array
    {
        $rows = $this->createQueryBuilder('ma')
            ->select('ma.type AS type', 'ma.name AS name')
            ->getQuery()
            ->getArrayResult();

        $names = [ActivationType::MODULE->value => [], ActivationType::OPERATION->value => []];
        foreach ($rows as $row) {
            $type = $row['type'] instanceof ActivationType ? $row['type']->value : (string) $row['type'];
            $names[$type][] = $row['name'];
        }
        return $names;
    }

    /**
     * @param ModuleActivation $entity
     * @param bool $flush
     * @return void
     */
    public function save(ModuleActivation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param ModuleActivation $entity
     * @param bool $flush
     * @return void
     */
    public function remove(ModuleActivation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
