<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Service pour les commentaires via API
 */

namespace App\Service\Api\Content;

use App\Dto\Api\Content\Comment\ApiAddCommentDto;
use App\Dto\Api\Content\Comment\ApiCommentByPageDto;
use App\Entity\Admin\Content\Comment\Comment;
use App\Entity\Admin\Content\Page\Page;
use App\Entity\Admin\System\User;
use App\Repository\Admin\Content\Comment\CommentRepository;
use App\Repository\Admin\Content\Page\PageRepository;
use App\Service\Api\AppApiService;
use App\Utils\Content\Comment\CommentConst;
use App\Utils\Content\Page\PageConst;
use App\Utils\System\Options\OptionSystemKey;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

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
    public function getCommentByPageIdOrSlug(ApiCommentByPageDto $dto, ?User $user = null): array
    {

        $translator = $this->getTranslator();

        /** @var CommentRepository $repository */
        $repository = $this->getRepository(Comment::class);
        $results = $repository->getCommentsByPageForApi($dto);

        $return = [];
        foreach ($results as $key => $comment) {
            /** @var Comment $comment */

            $com = $comment->getComment();
            if ($comment->getStatus() === CommentConst::WAIT_VALIDATION && !$this->isGranted($user, ['ROLE_CONTRIBUTEUR'])) {
                $com = $translator->trans('api_errors.comment.wait.validation', domain: 'api_errors');
            } elseif ($comment->getStatus() === CommentConst::MODERATE && !$this->isGranted($user, ['ROLE_CONTRIBUTEUR'])) {
                $com = $translator->trans('api_errors.comment.moderate', domain: 'api_errors');
            }

            $return[$key] = [
                'id' => $comment->getId(),
                'status' => $comment->getStatus(),
                'createdAt' => $comment->getCreatedAt(),
                'updateAt' => $comment->getUpdateAt(),
                'comment' => $com,
            ];
            if ($comment->getStatus() === CommentConst::MODERATE && $this->isGranted($user, ['ROLE_CONTRIBUTEUR'])) {
               $return[$key]['moderate'] = $comment->getModerationComment();
            }
        }
        return $return;
    }

    /**
     * Ajoute un nouveau commentaire
     * @param ApiAddCommentDto $dto
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function addNewComment(ApiAddCommentDto $dto) :bool {

        $translator = $this->getTranslator();

        /** @var PageRepository $pageRepository */
        $pageRepository = $this->getRepository(Page::class);
        if(empty($dto->getIdPage())) {
            $page = $pageRepository->getBySlug($dto->getPageSlug());
        }
        else {
            $page = $pageRepository->findOneBy(['id' => $dto->getIdPage(), 'status' => PageConst::STATUS_PUBLISH, 'disabled' => false]);
        }

        if($page === null) {
            throw new HttpException(Response::HTTP_FORBIDDEN, $translator->trans($translator->trans('api_errors.find.page.not.found', domain: 'api_errors')));
        }

        $isOpen = boolval($this->getOptionSystemService()->getValueByKey(OptionSystemKey::OS_OPEN_COMMENT));

        if(!$page->isOpenComment() || !$isOpen) {
            throw new HttpException(Response::HTTP_FORBIDDEN, $translator->trans($translator->trans('api_errors.comment.not.open', domain: 'api_errors')));
        }

        $comment = new Comment();
        $comment->setPage($page);
        $comment->setComment(strip_tags($dto->getComment()));
        $comment->setAuthor($dto->getAuthor());
        $comment->setEmail($dto->getEmail());
        $comment->setIp($dto->getIp());
        $comment->setUserAgent($dto->getUserAgent());

        return true;
    }
}
