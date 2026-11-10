<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Service pour les commentaires via API
 */

namespace App\Service\Api\Content;

use App\Dto\Api\Content\Comment\ApiAddCommentDto;
use App\Dto\Api\Content\Comment\ApiCommentByPageDto;
use App\Dto\Api\Content\Comment\ApiModerateCommentDto;
use App\Entity\Admin\Content\Comment\Comment;
use App\Entity\Admin\Content\Page\Page;
use App\Entity\Admin\System\User;
use App\Enum\Admin\Comment\CommentStatus;
use App\Enum\Admin\Content\Page\PageStatus;
use App\Enum\Admin\Global\Notification\Notification;
use App\Enum\Admin\System\Options\OptionSystem;
use App\Repository\Admin\Content\Comment\CommentRepository;
use App\Repository\Admin\Content\Page\PageRepository;
use App\Service\Api\AppApiService;
use App\Factory\Admin\Global\Notification\NotificationFactory;
use Doctrine\ORM\Tools\Pagination\Paginator;
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
        $results = $repository->getCommentsByPageForApi($dto, $this->getAllowedPageStatus($user));

        $return = [
            'comments' => [],
            'current_page' => $dto->getPage(),
            'rows' => $results->count(),
            'limit' => $dto->getLimit(),
        ];
        foreach ($results as $key => $comment) {
            /** @var Comment $comment */

            $com = $comment->getComment();
            $author = $comment->getAuthor();
            if (
                $comment->getStatus() === CommentStatus::WAIT_VALIDATION->value &&
                !$this->isGranted(['ROLE_CONTRIBUTEUR'], $user)
            ) {
                $com = $translator->trans('api_errors.comment.wait.validation', domain: 'api_errors');
                $author = $translator->trans('api_errors.comment.wait.validation_author', domain: 'api_errors');
            } elseif (
                $comment->getStatus() === CommentStatus::MODERATE->value &&
                !$this->isGranted(['ROLE_CONTRIBUTEUR'], $user)
            ) {
                $com = $translator->trans('api_errors.comment.moderate', domain: 'api_errors');
            }

            $return['comments'][$key] = [
                'id' => $comment->getId(),
                'author' => $author,
                'status' => $comment->getStatus(),
                'createdAt' => $comment->getCreatedAt()->getTimestamp(),
                'updateAt' => $comment->getUpdateAt()->getTimestamp(),
                'comment' => $com,
            ];

            if (
                $comment->getStatus() === CommentStatus::MODERATE->value &&
                $this->isGranted(['ROLE_CONTRIBUTEUR'], $user)
            ) {
                $return['comments'][$key]['moderate'] = $comment->getModerationComment();
            }
        }
        return $return;
    }

    /**
     * Ajoute un nouveau commentaire
     * @param ApiAddCommentDto $dto
     * @param string|null $clientIp IP de la requête, utilisée si le payload n'en fournit pas
     * @return Comment
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function addNewComment(ApiAddCommentDto $dto, ?string $clientIp = null): Comment
    {
        $translator = $this->getTranslator();

        /** @var PageRepository $pageRepository */
        $pageRepository = $this->getRepository(Page::class);
        if (empty($dto->getIdPage())) {
            $page = $pageRepository->getBySlug($dto->getPageSlug(), locale: $dto->getLocale());
        } else {
            $page = $pageRepository->findOneBy([
                'id' => $dto->getIdPage(),
                'status' => PageStatus::PUBLISH->value,
                'disabled' => false,
            ]);
        }

        if ($page === null) {
            throw new HttpException(
                Response::HTTP_NOT_FOUND,
                $translator->trans('api_errors.find.page.not.found', domain: 'api_errors'),
            );
        }

        if (!$this->isOpenForComments($page)) {
            throw new HttpException(
                Response::HTTP_FORBIDDEN,
                $translator->trans('api_errors.comment.not.open', domain: 'api_errors'),
            );
        }

        return $this->createComment(
            $page,
            $dto->getLocale(),
            $dto->getAuthor(),
            $dto->getEmail(),
            $dto->getComment(),
            $dto->getIp() !== '' ? $dto->getIp() : $clientIp ?? '',
            $dto->getUserAgent(),
        );
    }

    /**
     * Indique si la page accepte de nouveaux commentaires (option système et réglage de la page)
     * @param Page $page
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function isOpenForComments(Page $page): bool
    {
        return $page->isOpenComment() &&
            boolval($this->getOptionSystemService()->getValueByKey(OptionSystem::OS_OPEN_COMMENT->value));
    }

    /**
     * Crée un commentaire sur la page, en attente si la page ou l'option système l'exige, et notifie l'auteur de la page
     * @param Page $page
     * @param string $locale langue du titre de la page dans la notification
     * @param string $author
     * @param string|null $email
     * @param string $text Markdown brut
     * @param string $ip
     * @param string $userAgent
     * @return Comment
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function createComment(
        Page $page,
        string $locale,
        string $author,
        ?string $email,
        string $text,
        string $ip,
        string $userAgent,
    ): Comment {
        $translator = $this->getTranslator();

        $isMustValidate = boolval(
            $this->getOptionSystemService()->getValueByKey(OptionSystem::OS_NEW_COMMENT_WAIT_VALIDATION->value),
        );
        if (
            $page->getRuleComment() === CommentStatus::WAIT_VALIDATION->value ||
            $page->getRuleComment() === CommentStatus::MODERATE->value
        ) {
            $isMustValidate = true;
        }

        $status = CommentStatus::VALIDATE->value;
        $statusStr = $translator->trans('comment.status.validate', domain: 'comment');
        if ($isMustValidate) {
            $status = CommentStatus::WAIT_VALIDATION->value;
            $statusStr = $translator->trans('comment.status.wait.validation', domain: 'comment');
        }

        $comment = new Comment();
        $comment->setPage($page);
        // Markdown stocké brut : assaini à l'affichage (strip_tags tronquait les textes contenant « < »)
        $comment->setComment($text);
        $comment->setAuthor(strip_tags($author));
        $comment->setEmail($email);
        $comment->setIp($ip);
        $comment->setUserAgent($userAgent);
        $comment->setStatus($status);
        $comment->setDisabled(false);
        $this->save($comment);

        $notificationFactory = new NotificationFactory($page->getUser());
        $notificationFactory->addNotification(Notification::NEW_COMMENT->value, [
            'author' => htmlspecialchars($comment->getAuthor(), ENT_QUOTES),
            'status' => $statusStr,
            'page' => htmlspecialchars((string) $page->getPageTranslationByLocale($locale)?->getTitre(), ENT_QUOTES),
            'id' => $comment->getId(),
        ]);
        $this->save($notificationFactory->getUser());

        return $comment;
    }

    /**
     * Détermine si l'utilisateur peut modérer un commentaire (mêmes droits que l'administration)
     * @param User|null $user
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function canModerate(?User $user): bool
    {
        return $user !== null && $this->isGranted(['ROLE_CONTRIBUTEUR'], $user);
    }

    /**
     * Retourne les commentaires actifs d'une page, paginés et triés par date de création
     * @param Page $page
     * @param int $pageNumber
     * @param int $limit
     * @param \SortDirection $direction
     * @return Paginator<Comment>
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function findPageComments(Page $page, int $pageNumber, int $limit, \SortDirection $direction): Paginator
    {
        /** @var CommentRepository $repository */
        $repository = $this->getRepository(Comment::class);
        return $repository->getActiveCommentsByPage($page, $pageNumber, $limit, $direction);
    }

    /**
     * Retourne un commentaire actif d'une page publiée, null sinon
     * @param int $id
     * @return Comment|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function findVisibleComment(int $id): ?Comment
    {
        /** @var Comment|null $comment */
        $comment = $this->findOneById(Comment::class, $id);
        if ($comment === null || $comment->isDisabled() || !$this->isPageVisible($comment->getPage())) {
            return null;
        }
        return $comment;
    }

    /**
     * Permet de modérer un commentaire
     * @param ApiModerateCommentDto $dto
     * @param Comment $comment
     * @param User $user
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws HttpException statut inconnu
     */
    public function moderateComment(ApiModerateCommentDto $dto, Comment $comment, User $user): void
    {
        $status = CommentStatus::tryFrom(intval($dto->getStatus()));
        if ($status === null) {
            throw new HttpException(
                Response::HTTP_BAD_REQUEST,
                $this->getTranslator()->trans('api_errors.comment.status.no.valid', domain: 'api_errors'),
            );
        }

        $this->applyModeration($comment, $status, $dto->getModerationComment(), $user);
    }

    /**
     * Applique le statut et le message de modération au commentaire
     * @param Comment $comment
     * @param CommentStatus $status
     * @param string $moderationComment
     * @param User $user modérateur
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function applyModeration(
        Comment $comment,
        CommentStatus $status,
        string $moderationComment,
        User $user,
    ): void {
        $comment->setModerationComment(strip_tags($moderationComment));
        $comment->setStatus($status->value);
        $comment->setUserModeration($user);
        $this->save($comment);
    }
}
