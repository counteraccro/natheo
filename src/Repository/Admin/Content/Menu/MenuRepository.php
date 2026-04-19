<?php

namespace App\Repository\Admin\Content\Menu;

use App\Entity\Admin\Content\Menu\Menu;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Parameter;
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
     * @param array $queryParams
     * @param int|null $userId
     * @return Paginator
     */
    public function getAllPaginate(int $page, int $limit, array $queryParams, ?int $userId = null): Paginator
    {
        $orderField = 'id';
        $order = 'DESC';
        if (isset($queryParams['orderField']) && $queryParams['orderField'] !== '') {
            $orderField = $queryParams['orderField'];
        }

        if (isset($queryParams['order']) && $queryParams['order'] !== '') {
            $order = $queryParams['order'];
        }

        $query = $this->createQueryBuilder(Menu::DEFAULT_ALIAS)->orderBy(
            Menu::DEFAULT_ALIAS . '.' . $orderField,
            $order,
        );

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
     * Retourne tous les menus sauf le champ $field avec $value en fonction de $position
     * @param string $field
     * @param mixed $value
     * @param int $position
     * @return mixed
     */
    public function getAllWithoutExcludeByPosition(string $field, mixed $value, int $position): mixed
    {
        $query = $this->createQueryBuilder('m');
        $query
            ->where($query->expr()->neq('m.' . $field, ':value'))
            ->setParameters(new ArrayCollection([new Parameter('value', $value)]))
            ->andWhere('m.position = :position')
            ->setParameter('position', $position);
        return $query->getQuery()->getResult();
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
