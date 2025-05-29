<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Service pour les commentaires via API
 */

namespace App\Service\Api\Content;

use App\Dto\Api\Content\Comment\ApiCommentByPageDto;
use App\Entity\Admin\Content\Comment\Comment;
use App\Entity\Admin\System\User;
use App\Repository\Admin\Content\Comment\CommentRepository;
use App\Service\Api\AppApiService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ApiCommentService extends AppApiService
{
    /**
     * Retourne une liste de commentaires en fonction de l'id de la page ou du slug
     * @param ApiCommentByPageDto $dto
     * @param User|null $user
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getCommentByPageIdOrSlug(ApiCommentByPageDto $dto, ?User $user = null) : array {

        /** @var CommentRepository $repository */
        $repository = $this->getRepository(Comment::class);
        $results = $repository->getCommentsByPageForApi($dto);

        $return = [];
        foreach($results as $comment) {
             /** @var Comment $comment */


            $return[] = [
                'id' => $comment->getId(),

            ];
        }

        return $return;
    }
}
