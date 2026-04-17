<?php

namespace App\Service\Admin\Content\Comment;

use App\Entity\Admin\Content\Comment\Comment;
use App\Entity\Admin\System\User;
use App\Enum\Admin\Comment\Status;
use App\Service\Admin\AppAdminService;
use App\Service\Admin\GridService;
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
     * @param array $queryParams
     * @param int|null $userId
     * @return array
     * @throws CommonMarkException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getAllFormatToGrid(int $page, int $limit, array $queryParams, ?int $userId = null): array
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

        $dataPaginate = $this->getAllPaginate($page, $limit, $queryParams, $userId);

        $nb = $dataPaginate->count();
        $data = [];
        foreach ($dataPaginate as $element) {
            /* @var Comment $element */

            $action = $this->generateTabAction($element);
            $locale = $requestStack->getCurrentRequest()->getLocale();
            $titre = $element->getPage()->getPageTranslationByLocale($locale)->getTitre();
            $markdown = new Markdown();
            $comment = $markdown->convertMarkdownToHtml($element->getComment());

            $data[] = [
                $translator->trans('comment.grid.id', domain: 'comment') => $element->getId(),
                $translator->trans('comment.grid.comment', domain: 'comment') => u($comment)->truncate(100, '…'),
                $translator->trans('comment.grid.author', domain: 'comment') => $element->getAuthor(),
                $translator->trans('comment.grid.page', domain: 'comment') =>
                    '<a href=' .
                    $router->generate('admin_page_update', ['id' => $element->getPage()->getId()]) .
                    '>' .
                    $titre .
                    '</a>',
                $translator->trans('comment.grid.status', domain: 'comment') => $this->getStatusFormatedByCode(
                    $element->getStatus(),
                ),
                $translator->trans('comment.grid.created_at', domain: 'comment') => $element
                    ->getCreatedAt()
                    ->format('d/m/y H:i'),
                GridService::KEY_ACTION => $action,
                'isDisabled' => $element->isDisabled(),
            ];
        }

        $tabReturn = [
            GridService::KEY_NB => $nb,
            GridService::KEY_DATA => $data,
            GridService::KEY_COLUMN => $column,
            GridService::KEY_RAW_SQL => $gridService->getFormatedSQLQuery($dataPaginate),
            GridService::KEY_LIST_ORDER_FIELD => [
                'id' => $translator->trans('comment.grid.id', domain: 'comment'),
                'author' => $translator->trans('comment.grid.author', domain: 'comment'),
                'page_id' => $translator->trans('comment.grid.page', domain: 'comment'),
                'status' => $translator->trans('comment.grid.status', domain: 'comment'),
                'created_at' => $translator->trans('comment.grid.created_at', domain: 'comment'),
            ],
        ];
        return $gridService->addAllDataRequiredGrid($tabReturn);
    }

    /**
     * Retourne une liste de commentaires paginé
     * @param int $page
     * @param int $limit
     * @param array $queryParams
     * @param int|null $userId
     * @return Paginator
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getAllPaginate(int $page, int $limit, array $queryParams, ?int $userId = null): Paginator
    {
        $repo = $this->getRepository(Comment::class);
        return $repo->getAllPaginate($page, $limit, $queryParams, $userId);
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
        $actions[] = [
            'label' => [
                'M10.779 17.779 4.36 19.918 6.5 13.5m4.279 4.279 8.364-8.643a3.027 3.027 0 0 0-2.14-5.165 3.03 3.03 0 0 0-2.14.886L6.5 13.5m4.279 4.279L6.499 13.5m2.14 2.14 6.213-6.504M12.75 7.04 17 11.28',
            ],
            'color' => 'primary',
            'type' => 'post',
            'id' => $comment->getId(),
            'url' => $router->generate('admin_comment_see', ['id' => $comment->getId()]),
            'ajax' => false,
        ];

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
            Status::WAIT_VALIDATION->value => '<span class="badge ' .
                Status::WAIT_VALIDATION->getClassCss() .
                '">' .
                $string .
                '</span>',
            Status::VALIDATE->value => '<span class="badge ' .
                Status::VALIDATE->getClassCss() .
                '">' .
                $string .
                '</span>',
            Status::MODERATE->value => '<span class="badge ' .
                Status::MODERATE->getClassCss() .
                '">' .
                $string .
                '</span>',
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
            Status::WAIT_VALIDATION->value => $translator->trans('comment.status.wait.validation', domain: 'comment'),
            Status::VALIDATE->value => $translator->trans('comment.status.validate', domain: 'comment'),
            Status::MODERATE->value => $translator->trans('comment.status.moderate', domain: 'comment'),
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
            Status::WAIT_VALIDATION->value => $translator->trans('comment.status.wait.validation', domain: 'comment'),
            Status::VALIDATE->value => $translator->trans('comment.status.validate', domain: 'comment'),
            Status::MODERATE->value => $translator->trans('comment.status.moderate', domain: 'comment'),
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

        $statStatus = $this->getNbCommentByStatus();

        $data = [];
        foreach ($result as $comment) {
            /** @var Comment $comment */

            $pageTitle = $comment->getPage()->getPageTranslationByLocale($locale)->getTitre();
            $moderator = null;
            if ($comment->getUserModeration() !== null) {
                $moderator = $comment->getUserModeration()->getEmail();
            }

            $data[] = [
                'id' => $comment->getId(),
                'comment' => $comment->getComment(),
                'author' => $comment->getAuthor(),
                'email' => $comment->getEmail(),
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
            'statStatus' => $statStatus,
            'data' => $data,
        ];
    }

    /**
     * Retourne le nombre de commentaire en fonction du status
     * Si status est vide, renvoi un tableau de statistiques avec comme clé le code du status et value le nombre de commentaires associés
     * @param int|null $status
     * @return int|array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getNbCommentByStatus(?int $status = null): int|array
    {
        $repository = $this->getRepository(Comment::class);
        $result = $repository->getNbGroupByStatus();

        if ($status === null) {
            $return = [];
            foreach (Status::cases() as $status) {
                foreach ($result as $row) {
                    if ($row['status'] === $status->value) {
                        $return[$status->value] = $row['nb'];
                    }
                }
                if (!isset($return[$status->value])) {
                    $return[$status->value] = 0;
                }
            }
            return $return;
        }

        $return = 0;
        foreach ($result as $row) {
            if ($row['status'] === $status) {
                $return = $row['nb'];
            }
        }
        return $return;
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

        foreach ($comments as $comment) {
            /** @var Comment $comment */
            $comment->setStatus($data['status']);
            if ($comment->getStatus() !== Status::MODERATE->value) {
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
