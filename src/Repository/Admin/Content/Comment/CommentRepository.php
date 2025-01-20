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
}
