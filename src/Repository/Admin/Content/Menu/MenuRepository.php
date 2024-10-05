<?php

namespace App\Repository\Admin\Content\Menu;

use App\Entity\Admin\Content\Menu\Menu;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Menu>
 */
class MenuRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Menu::class);
    }

    /**
     * @param Menu $entity
     * @param bool $flush
     * @return void
     */
    public function save(Menu $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param Menu $entity
     * @param bool $flush
     * @return void
     */
    public function remove(Menu $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Retourne une liste de menu Paginé
     * @param int $page
     * @param int $limit
     * @param string|null $search
     * @return Paginator
     */
    public function getAllPaginate(int $page, int $limit, string $search = null): Paginator
    {
        $query = $this->createQueryBuilder('m')
            ->orderBy('m.id', 'ASC');

        if ($search !== null) {
            $query->where('m.name like :search');
            $query->setParameter('search', '%' . $search . '%');
        }

        $paginator = new Paginator($query->getQuery(), true);
        $paginator->getQuery()
            ->setFirstResult($limit * ($page - 1))
            ->setMaxResults($limit);
        return $paginator;
    }

    /**
     * Retourne tous les menus sauf le champ $field avec $value en fonction de $position
     * @param string $field
     * @param mixed $value
     * @param int $position
     * @return mixed
     */
    public function getAllWithoutExcludeByPosition(string $field, mixed $value, int $position): mixed
    {
        $query = $this->createQueryBuilder('m');
        $query->where(
            $query->expr()->neq('m.' . $field, ':value')
        )
            ->setParameters(['value' => $value])
            ->andWhere('m.position = :position')
            ->setParameter('position', $position);
        return $query->getQuery()->getResult();
    }
}
