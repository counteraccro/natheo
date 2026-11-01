<?php

declare(strict_types=1);

namespace App\Repository\Admin\Content\Menu;

use App\Entity\Admin\Content\Menu\Menu;
use App\Repository\Trait\OrderedQueryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Menu>
 */
class MenuRepository extends ServiceEntityRepository
{
    use OrderedQueryTrait;

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
     * @param array $queryParams
     * @param int|null $userId
     * @return Paginator
     */
    public function getAllPaginate(int $page, int $limit, array $queryParams, ?int $userId = null): Paginator
    {
        $query = $this->createQueryBuilder(Menu::DEFAULT_ALIAS);
        $this->applyOrdering($query, Menu::class, $queryParams);

        if (isset($queryParams['search']) && $queryParams['search'] !== '') {
            $query->where(Menu::DEFAULT_ALIAS . '.name like :search');
            $query->setParameter('search', '%' . $queryParams['search'] . '%');
        }

        if ($userId !== null) {
            $query->andWhere(Menu::DEFAULT_ALIAS . '.user = :userId');
            $query->setParameter('userId', $userId);
        }

        $paginator = new Paginator($query->getQuery(), true);
        $paginator
            ->getQuery()
            ->setFirstResult($limit * ($page - 1))
            ->setMaxResults($limit);
        return $paginator;
    }

    /**
     * Retire le statut « par défaut » de tous les menus de la position $position, sauf $excludeId
     * @param int $excludeId
     * @param int $position
     * @return void
     */
    public function unsetDefaultByPosition(int $excludeId, int $position): void
    {
        $this->createQueryBuilder('m')
            ->update()
            ->set('m.defaultMenu', ':false')
            ->where('m.id != :excludeId')
            ->andWhere('m.position = :position')
            ->setParameter('false', false)
            ->setParameter('excludeId', $excludeId)
            ->setParameter('position', $position)
            ->getQuery()
            ->execute();
    }

    /**
     * Retourne le nombre de pages auxquelles le menu est rattaché
     * @param Menu $menu
     * @return int
     */
    public function countPages(Menu $menu): int
    {
        return (int) $this->createQueryBuilder('m')
            ->select('COUNT(p.id)')
            ->innerJoin('m.pages', 'p')
            ->where('m.id = :id')
            ->setParameter('id', $menu->getId())
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Retourne un menu en fonction de l'url de la page auquel il est rattaché
     * @param string $url
     * @param int $position
     * @return array
     */
    public function getByPageUrlAndPositionForApi(string $url, int $position): array
    {
        $query = $this->createQueryBuilder('m')
            ->select(['m.position', 'm.id', 'm.type'])
            ->join('m.pages', 'p')
            ->join('p.pageTranslations', 'pt')
            ->where('pt.url = :url')
            ->setParameter('url', $url)
            ->andWhere('m.position = :position')
            ->setParameter('position', $position)
            ->andWhere('m.disabled = :disabled')
            ->setParameter('disabled', false);
        return $query->getQuery()->getResult();
    }

    /**
     * Retourne un menu avec uniquement les menuElements sans parent
     * @param int $id
     * @return array
     */
    public function getByIdForApi(int $id): array
    {
        $query = $this->createQueryBuilder('m')
            ->select(['m.position', 'm.id', 'm.type'])
            ->where('m.id = :id')
            ->setParameter('id', $id)
            ->andWhere('m.disabled = :disabled')
            ->setParameter('disabled', false);
        return $query->getQuery()->getResult();
    }

    /**
     * Retourne les menus définis par défauts
     * @return array
     */
    public function getDefaultForApi(): array
    {
        $query = $this->createQueryBuilder('m')
            ->select(['m.position', 'm.id', 'm.type'])
            ->where('m.defaultMenu = :default_menu')
            ->setParameter('default_menu', true)
            ->andWhere('m.disabled = :disabled')
            ->setParameter('disabled', false);
        return $query->getQuery()->getResult();
    }

    /**
     * Recherche pour les menus
     * @param string $search
     * @param string $locale
     * @param int $page
     * @param int $limit
     * @return Paginator
     */
    public function search(string $search, string $locale, int $page, int $limit): Paginator
    {
        $query = $this->createQueryBuilder('m');

        $query
            ->join('m.user', 'u')
            ->leftJoin('m.menuElements', 'me')
            ->leftJoin('me.menuElementTranslations', 'met')
            ->andWhere('m.name like :search')
            ->orWhere('u.login like :search')
            ->orWhere('met.textLink like :search and met.locale = :locale')
            ->setParameter('search', '%' . $search . '%')
            ->setParameter('locale', $locale);

        $paginator = new Paginator($query->getQuery(), true);
        $paginator
            ->getQuery()
            ->setFirstResult($limit * ($page - 1))
            ->setMaxResults($limit);
        return $paginator;
    }
}
