<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Processor qui modère un commentaire, réservé à un utilisateur autorisé à modérer
 */

namespace App\Api\V2\State\Processor\Content;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Api\V2\Exception\ForbiddenException;
use App\Api\V2\Exception\NotFoundException;
use App\Api\V2\Input\Content\ModerateCommentInput;
use App\Api\V2\Mapper\Content\CommentMapper;
use App\Api\V2\Resource\Content\Comment;
use App\Api\V2\Security\CurrentUserResolver;
use App\Enum\Admin\Comment\CommentStatus;
use App\Service\Api\Content\ApiCommentService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @implements ProcessorInterface<ModerateCommentInput, Comment>
 */
final readonly class ModerateCommentProcessor implements ProcessorInterface
{
    public function __construct(
        private ApiCommentService $apiCommentService,
        private CurrentUserResolver $currentUserResolver,
        private TranslatorInterface $translator,
    ) {}

    /**
     * @param ModerateCommentInput $data
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return Comment
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Comment
    {
        $user = $this->currentUserResolver->getUser();
        if ($user === null || !$this->apiCommentService->canModerate($user)) {
            throw new ForbiddenException(
                $this->translator->trans('api_errors.comment.moderate_forbidden', domain: 'api_errors'),
            );
        }

        $comment = $this->apiCommentService->findVisibleComment((int) $uriVariables['id']);
        if ($comment === null) {
            throw new NotFoundException(
                $this->translator->trans('api_errors.find.comment.not.found', domain: 'api_errors'),
            );
        }

        $this->apiCommentService->applyModeration(
            $comment,
            CommentStatus::tryFromSlug($data->status) ?? CommentStatus::from((int) $comment->getStatus()),
            $data->moderationComment ?? (string) $comment->getModerationComment(),
            $user,
        );
        return (new CommentMapper(true))->map($comment);
    }
}
