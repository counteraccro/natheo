<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Modification et modération d'un commentaire par l'API d'administration
 */

namespace App\Api\V2\State\Processor\Admin\Content;

use ApiPlatform\Metadata\Operation;
use App\Api\V2\Input\Admin\Content\UpdateCommentInput;
use App\Api\V2\Resource\Admin\Content\AdminComment;
use App\Api\V2\Security\CurrentUserResolver;
use App\Api\V2\State\Processor\Admin\AbstractAdminProcessor;
use App\Api\V2\State\Provider\Admin\Content\AdminCommentProvider;
use App\Enum\Admin\Comment\CommentStatus;
use App\Service\Api\Admin\Content\ApiAdminCommentService;

/**
 * @extends AbstractAdminProcessor<UpdateCommentInput, AdminComment>
 */
final class UpdateAdminCommentProcessor extends AbstractAdminProcessor
{
    public function __construct(
        private readonly ApiAdminCommentService $commentService,
        private readonly AdminCommentProvider $commentProvider,
        private readonly CurrentUserResolver $userResolver,
    ) {}

    /**
     * @param mixed $data
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return AdminComment
     */
    protected function write(mixed $data, Operation $operation, array $uriVariables, array $context): AdminComment
    {
        $comment = $this->commentService->update(
            $this->commentProvider->find($uriVariables),
            $data->comment,
            CommentStatus::tryFromSlug($data->status),
            $data->moderationComment,
            $this->userResolver->getUser(),
        );
        return $this->commentProvider->map($comment);
    }

    /**
     * Le statut seul est tracé sous son nom (validate, moderate, wait_validation), toute autre modification en update
     * @param Operation $operation
     * @param mixed $data
     * @return string
     */
    protected function getLogAction(Operation $operation, mixed $data): string
    {
        if ($data instanceof UpdateCommentInput && $data->isStatusOnly()) {
            return (string) $data->status;
        }
        return parent::getLogAction($operation, $data);
    }

    /**
     * Auteur du commentaire
     * @param mixed $resource
     * @return string|null
     */
    protected function getLogLabel(mixed $resource): ?string
    {
        return $resource instanceof AdminComment ? $resource->author : null;
    }
}
