<?php

namespace App\Service\Admin\Content\Comment;

use App\Entity\Admin\Content\Comment\Comment;
use App\Entity\Admin\System\User;
use App\Service\Admin\AppAdminService;
use App\Service\Admin\GridService;
use App\Utils\Content\Comment\CommentConst;
use App\Utils\Markdown;
use Doctrine\ORM\Tools\Pagination\Paginator;
use League\CommonMark\Exception\CommonMarkException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use function Symfony\Component\String\u;

class CommentService extends AppAdminService
{
    /**
     * Construit le tableau de donnée à envoyé au tableau GRID
     * @param int $page
     * @param int $limit
     * @param string|null $search
     * @param int|null $userId
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws CommonMarkException
     */
    public function getAllFormatToGrid(int $page, int $limit, ?string $search = null, ?int $userId = null): array
    {
        $translator = $this->getTranslator();
        $gridService = $this->getGridService();
        $requestStack = $this->getRequestStack();
        $router = $this->getRouter();

        $column = [
            $translator->trans('comment.grid.id', domain: 'comment'),
            $translator->trans('comment.grid.comment', domain: 'comment'),
            $translator->trans('comment.grid.author', domain: 'comment'),
            $translator->trans('comment.grid.page', domain: 'comment'),
            $translator->trans('comment.grid.status', domain: 'comment'),
            $translator->trans('comment.grid.created_at', domain: 'comment'),
            GridService::KEY_ACTION,
        ];

        $dataPaginate = $this->getAllPaginate($page, $limit, $search, $userId);

        $nb = $dataPaginate->count();
        $data = [];
        foreach ($dataPaginate as $element) {
            /* @var Comment $element */

            $action = $this->generateTabAction($element);
            $locale = $requestStack->getCurrentRequest()->getLocale();
            $titre = $element->getPage()->getPageTranslationByLocale($locale)->getTitre();
            $markdown = new Markdown();
            $comment = $markdown->convertMarkdownToHtml($element->getComment());

            $isDisabled = '';
            if ($element->isDisabled()) {
                $isDisabled = '<i class="bi bi-eye-slash"></i>';
            }

            $data[] = [
                $translator->trans('comment.grid.id', domain: 'comment') => $element->getId() . $isDisabled,
                $translator->trans('comment.grid.comment', domain: 'comment') => u($comment)->truncate(100, '…'),
                $translator->trans('comment.grid.author', domain: 'comment') => $element->getAuthor(),
                $translator->trans('comment.grid.page', domain: 'comment') => '<a href=' . $router->generate('admin_page_update', ['id' => $element->getPage()->getId()]) . '>' . $titre . '</a>',
                $translator->trans('comment.grid.status', domain: 'comment') => $this->getStatusFormatedByCode($element->getStatus()),
                $translator->trans('comment.grid.created_at', domain: 'comment') => $element->getCreatedAt()->format('d/m/y H:i'),
                GridService::KEY_ACTION => $action,
            ];
        }

        $tabReturn = [
            GridService::KEY_NB => $nb,
            GridService::KEY_DATA => $data,
            GridService::KEY_COLUMN => $column,
            GridService::KEY_RAW_SQL => $gridService->getFormatedSQLQuery($dataPaginate)
        ];
        return $gridService->addAllDataRequiredGrid($tabReturn);

    }

    /**
     * Retourne une liste de commentaires paginé
     * @param int $page
     * @param int $limit
     * @param string|null $search
     * @param int|null $userId
     * @return Paginator
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getAllPaginate(int $page, int $limit, ?string $search = null, ?int $userId = null): Paginator
    {
        $repo = $this->getRepository(Comment::class);
        return $repo->getAllPaginate($page, $limit, $search, $userId);
    }

    /**
     * Génère le tableau d'action pour le Grid des commentaires
     * @param Comment $comment
     * @return array[]|string[]
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function generateTabAction(Comment $comment): array
    {
        $router = $this->getRouter();

        $actions = [];

        // Bouton edit
        $actions[] = ['label' => '<i class="bi bi-chat-dots-fill"></i>',
            'id' => $comment->getId(),
            'url' => $router->generate('admin_comment_see', ['id' => $comment->getId()]),
            'ajax' => false];

        return $actions;
    }

    /**
     * Retourne un status formaté HTML en fonction de son code
     * @param int $status
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getStatusFormatedByCode(int $status): string
    {
        $string = $this->getStatusStringByCode($status);

        return match ($status) {
            CommentConst::WAIT_VALIDATION => '<span class="badge text-bg-primary">' . $string . '</span>',
            CommentConst::VALIDATE => '<span class="badge text-bg-success">' . $string . '</span>',
            CommentConst::MODERATE => '<span class="badge text-bg-danger">' . $string . '</span>',
        };
    }

    /**
     * Retourne le texte du status en fonction d'un code
     * @param int $status
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getStatusStringByCode(int $status): string
    {
        $translator = $this->getTranslator();
        return match ($status) {
            CommentConst::WAIT_VALIDATION => $translator->trans('comment.status.wait.validation', domain: 'comment'),
            CommentConst::VALIDATE => $translator->trans('comment.status.validate', domain: 'comment'),
            CommentConst::MODERATE => $translator->trans('comment.status.moderate', domain: 'comment'),
        };
    }

    /**
     * Retourne l'ensemble des status d'un commentaire
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getAllStatus(): array
    {
        $translator = $this->getTranslator();
        return [
            CommentConst::WAIT_VALIDATION => $translator->trans('comment.status.wait.validation', domain: 'comment'),
            CommentConst::VALIDATE => $translator->trans('comment.status.validate', domain: 'comment'),
            CommentConst::MODERATE => $translator->trans('comment.status.moderate', domain: 'comment'),
        ];
    }

    /**
     * Retourne une liste de commentaires filtrés
     * @param int $status
     * @param int $idPage
     * @param int $page
     * @param int $limit
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getCommentFilter(int $status, int $idPage, int $page, int $limit): array
    {
        $repository = $this->getRepository(Comment::class);
        $result = $repository->getListCommentsByFilter($status, $idPage, $page, $limit);
        $locale = $this->getLocales()['current'];

        $data = [];
        foreach($result as $comment) {

            /** @var Comment $comment */

            $pageTitle = $comment->getPage()->getPageTranslationByLocale($locale)->getTitre();
            $moderator = null;
            if($comment->getUserModeration() !== null) {
                $moderator = $comment->getUserModeration()->getEmail();
            }

            $data[] = [
                'id' => $comment->getId(),
                'comment' => $comment->getComment(),
                'author' => $comment->getAuthor() . ' (' . $comment->getEmail() . ')',
                'page' => $pageTitle,
                'ip' => $comment->getIp(),
                'userAgent' => $comment->getUserAgent(),
                'moderator' => $moderator,
                'commentModeration' => $comment->getModerationComment(),
                'status' => $this->getStatusFormatedByCode($comment->getStatus()),
                'date' => $comment->getCreatedAt()->format('d/m/y H:i'),
                'update' => $comment->getUpdateAt()->format('d/m/y H:i'),
            ];
        }

        return [
            'nb' => $result->count(),
            'data' => $data,
        ];
    }

    /**
     * Retourne le nombre de commentaire en fonction tu type
     * @param int $status
     * @return int
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getNbCommentByStatus(int $status): int
    {
        $repository = $this->getRepository(Comment::class);
        return $repository->getNbByType($status);

    }

    /**
     * Metà jour une liste de commentaire défini dans $data
     * @param array $data
     * @param User $user
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function updateMultipleComment(array $data, User $user): void
    {
        $repository = $this->getRepository(Comment::class);
        $comments = $repository->findBy(['id' => $data['selected']]);

        foreach($comments as $comment) {
            /** @var Comment $comment */
            $comment->setStatus($data['status']);
            if($comment->getStatus() !== CommentConst::MODERATE) {
                $comment->setModerationComment(null);
                $comment->setUserModeration(null);
            } else {
                $comment->setModerationComment($data['moderateComment']);
                $comment->setUserModeration($user);
            }
            $this->save($comment);
        }
    }
}
