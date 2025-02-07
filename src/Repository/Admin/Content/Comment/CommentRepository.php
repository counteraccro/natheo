<?php

namespace App\Repository\Admin\Content\Comment;

use App\Entity\Admin\Content\Comment\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Comment>
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    /**
     * Sauvegarde
     * @param Comment $entity
     * @param bool $flush
     * @return void
     */
    public function save(Comment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Retourne une liste de commentaires Paginé
     * @param int $page
     * @param int $limit
     * @param string|null $search
     * @param null $userId
     * @return Paginator
     */
    public function getAllPaginate(int $page, int $limit, string $search = null, $userId = null): Paginator
    {
        $query = $this->createQueryBuilder('c')
            ->orderBy('c.id', 'ASC');

        if($userId !== null){
            $query->andWhere('c.userModeration = :userId');
            $query->setParameter('userId', $userId);
        }

        if ($search !== null) {
            $query->where('c.comment like :search OR c.author like :search OR c.email like :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $paginator = new Paginator($query->getQuery(), true);
        $paginator->getQuery()
            ->setFirstResult($limit * ($page - 1))
            ->setMaxResults($limit);
        return $paginator;

    }

    /**
     * Retourne une liste de commentaire en fonction de ses filtres
     * @param int $status
     * @param int $idPage
     * @param int $limit
     * @param int $page
     * @return Paginator
     */
    public function getListCommentsByFilter(int $status, int $idPage, int $page,  int $limit): Paginator
    {
        $query = $this->createQueryBuilder('c');

        if($status !== 0){
            $query->andWhere('c.status = :status')
                ->setParameter('status', $status);
        }

        if($idPage !== 0){
            $query->andWhere('c.page = :page')
                ->setParameter("page", $idPage);
        }

        $paginator = new Paginator($query->getQuery(), true);
        $paginator->getQuery()
            ->setFirstResult($limit * ($page - 1))
            ->setMaxResults($limit);
        return $paginator;
    }

    /**
     * Retourne le nombre de commentaire en fonction du type
     * @param int $type
     * @return int
     */
    public function getNbByType(int $status): int
    {
        $query = $this->createQueryBuilder('c')
            ->select("COUNT(c.id) as nb")
            ->where('c.status = :status')
            ->setParameter('status', $status);

        $result = $query->getQuery()->getArrayResult();
        return $result[0]['nb'];
    }
}
