<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Lecture d'un commentaire par l'API d'administration
 */

namespace App\Api\V2\State\Provider\Admin\Content;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Api\V2\Exception\NotFoundException;
use App\Api\V2\Mapper\Admin\Content\AdminCommentMapper;
use App\Api\V2\Output\Admin\Content\AdminCommentPage;
use App\Api\V2\Resource\Admin\Content\AdminComment;
use App\Api\V2\Security\AdminPermissionResolver;
use App\Entity\Admin\Content\Comment\Comment;
use App\Service\Api\Admin\Content\ApiAdminCommentService;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class AdminCommentProvider implements ProviderInterface
{
    public function __construct(
        private ApiAdminCommentService $commentService,
        private AdminPermissionResolver $permissionResolver,
        private TranslatorInterface $translator,
    ) {}

    /**
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return AdminComment
     * @throws NotFoundException
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): AdminComment
    {
        return $this->map($this->find($uriVariables));
    }

    /**
     * Commentaire de l'adresse
     * @param array $uriVariables
     * @return Comment
     * @throws NotFoundException
     */
    public function find(array $uriVariables): Comment
    {
        return $this->commentService->find((int) ($uriVariables['id'] ?? 0)) ??
            throw new NotFoundException($this->translator->trans('comment.error.not_found', domain: 'comment'));
    }

    /**
     * Ressource complète d'un commentaire, partagée avec le processor
     * @param Comment $comment
     * @return AdminComment
     */
    public function map(Comment $comment): AdminComment
    {
        $page = $comment->getPage();
        return (new AdminCommentMapper())->map(
            $comment,
            new AdminCommentPage(
                (int) $page->getId(),
                $this->commentService->getPageTitle($page),
                $this->commentService->getPageUrl($page),
            ),
            $this->permissionResolver->forUnowned(),
        );
    }
}
