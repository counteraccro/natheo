<?php

namespace App\Service\Admin\Content\Comment;

use App\Entity\Admin\Content\Comment\Comment;
use App\Service\Admin\AppAdminService;
use App\Service\Admin\GridService;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

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
     */
    public function getAllFormatToGrid(int $page, int $limit, string $search = null, int $userId = null): array
    {
        $translator = $this->getTranslator();
        $gridService =  $this->getGridService();

        $column = [
            $translator->trans('comment.grid.id', domain: 'comment'),
            $translator->trans('comment.grid.comment', domain: 'comment'),
            $translator->trans('comment.grid.author', domain: 'comment'),
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

            $data[] = [
                $translator->trans('comment.grid.id', domain: 'comment') => $element->getId(),
                $translator->trans('comment.grid.comment', domain: 'comment') => $element->getComment(),
                $translator->trans('comment.grid.author', domain: 'comment') => $element->getAuthor(),
                $translator->trans('comment.grid.status', domain: 'comment') => $element->getStatus(),
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
    public function getAllPaginate(int $page, int $limit, string $search = null, int $userId = null): Paginator
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
        $translator = $this->getTranslator();

        $actionDisabled = ['label' => '<i class="bi bi-eye-slash-fill"></i>',
            'type' => 'put',
            'url' => $router->generate('admin_faq_disabled', ['id' => $comment->getId()]),
            'ajax' => true,
            'confirm' => true,
            'msgConfirm' => $translator->trans('comment.confirm.disabled.msg', ['label' => $comment->getId()], 'comment')];
        if ($comment->getStatus() === 22) {
            $actionDisabled = [
                'label' => '<i class="bi bi-eye-fill"></i>',
                'type' => 'put',
                'url' => $router->generate('admin_faq_disabled', ['id' => $comment->getId()]),
                'ajax' => true
            ];
        }


        $actions = [];
        $actions[] = $actionDisabled;

        // Bouton edit
        $actions[] = ['label' => '<i class="bi bi-pencil-fill"></i>',
            'id' => $comment->getId(),
            'url' => $router->generate('admin_faq_update', ['id' => $comment->getId()]),
            'ajax' => false];

        return $actions;
    }
}
