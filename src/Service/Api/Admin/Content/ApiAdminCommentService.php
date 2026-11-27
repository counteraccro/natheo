<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Lecture et modération des commentaires par l'API d'administration
 */

namespace App\Service\Api\Admin\Content;

use App\Dto\Api\Admin\AdminCollectionQuery;
use App\Entity\Admin\Content\Comment\Comment;
use App\Entity\Admin\Content\Page\Page;
use App\Entity\Admin\System\User;
use App\Enum\Admin\Comment\CommentStatus;
use App\Enum\Admin\Content\Page\PageCategory;
use App\Repository\Admin\Content\Comment\CommentRepository;
use App\Service\Api\AppApiService;
use App\Service\Global\MarkdownService;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

use function Symfony\Component\String\u;

class ApiAdminCommentService extends AppApiService
{
    /**
     * Longueur de l'extrait affiché dans la liste
     * @var int
     */
    private const int EXCERPT_LENGTH = 100;

    /**
     * Page de la collection d'administration dans la langue de contenu (voir CommentRepository::getAdminCollection())
     * @param AdminCollectionQuery $query
     * @param CommentStatus|null $status
     * @param int|null $pageId
     * @return Paginator
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getCollection(AdminCollectionQuery $query, ?CommentStatus $status, ?int $pageId): Paginator
    {
        return $this->getCommentRepository()->getAdminCollection(
            $query,
            $status,
            $pageId,
            $this->getContentLocale(),
            $this->getDefaultLocale(),
        );
    }

    /**
     * @param int $id
     * @return Comment|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function find(int $id): ?Comment
    {
        return $this->getCommentRepository()->find($id);
    }

    /**
     * Nombre de commentaires de chaque statut, dans l'ordre de CommentStatus
     * @return array<string, int> slug du statut => nombre
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function countByStatus(): array
    {
        $counts = array_column($this->getCommentRepository()->getNbGroupByStatus(), 'nb', 'status');
        $result = [];
        foreach (CommentStatus::cases() as $status) {
            $result[$status->getSlug()] = (int) ($counts[$status->value] ?? 0);
        }
        return $result;
    }

    /**
     * Texte sans Markdown, coupé pour la liste
     * @param string $comment
     * @return string
     */
    public function getExcerpt(string $comment): string
    {
        $text = (new MarkdownService())->convertMarkdownToText($comment);
        return u($text)->truncate(self::EXCERPT_LENGTH, '…')->toString();
    }

    /**
     * Titre de la page dans la langue de contenu, à défaut dans la langue par défaut du site
     * @param Page $page
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getPageTitle(Page $page): string
    {
        return (string) $page
            ->getPageTranslationByLocale($this->getContentLocale(), $this->getDefaultLocale())
            ?->getTitre();
    }

    /**
     * Adresse de la page sur le site, dans la langue de contenu quand elle y est traduite
     * @param Page $page
     * @return string|null null pour une page sans traduction
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getPageUrl(Page $page): ?string
    {
        $translation = $page->getPageTranslationByLocale($this->getContentLocale(), $this->getDefaultLocale());
        if ($translation === null) {
            return null;
        }
        return $this->getRouter()->generate('front_index_2', [
            'locale' => $translation->getLocale(),
            'category' => PageCategory::tryFrom((int) $page->getCategory())?->getSlug(),
            'slug' => $translation->getUrl(),
        ]);
    }

    /**
     * Modifie le texte et la modération ; le modérateur ne change que si le statut ou la raison est envoyé
     * @param Comment $comment
     * @param string|null $text
     * @param CommentStatus|null $status
     * @param string|null $moderationComment
     * @param User|null $user utilisateur de la session
     * @return Comment
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function update(
        Comment $comment,
        ?string $text,
        ?CommentStatus $status,
        ?string $moderationComment,
        ?User $user,
    ): Comment {
        if ($text !== null) {
            $comment->setComment($text);
        }
        if ($status !== null || $moderationComment !== null) {
            $comment->moderate(
                $status ?? CommentStatus::from((int) $comment->getStatus()),
                $moderationComment ?? $comment->getModerationComment(),
                $user,
            );
        }
        $this->save($comment);
        return $comment;
    }

    /**
     * Langue par défaut du site
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getDefaultLocale(): string
    {
        return $this->getOptionSystemService()->getDefaultLanguage();
    }

    /**
     * @return CommentRepository
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getCommentRepository(): CommentRepository
    {
        /** @var CommentRepository $repository */
        $repository = $this->getRepository(Comment::class);
        return $repository;
    }
}
