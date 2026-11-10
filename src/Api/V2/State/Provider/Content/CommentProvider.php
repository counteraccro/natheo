<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Provider d'un commentaire actif d'une page publiée par son id
 */

namespace App\Api\V2\State\Provider\Content;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Api\V2\Exception\NotFoundException;
use App\Api\V2\Mapper\Content\CommentMapper;
use App\Api\V2\Resource\Content\Comment;
use App\Api\V2\Security\UserTokenResolver;
use App\Service\Api\Content\ApiCommentService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @implements ProviderInterface<Comment>
 */
final readonly class CommentProvider implements ProviderInterface
{
    public function __construct(
        private ApiCommentService $apiCommentService,
        private UserTokenResolver $userTokenResolver,
        private TranslatorInterface $translator,
    ) {}

    /**
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return Comment
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Comment
    {
        $isModerator = $this->apiCommentService->canModerate($this->userTokenResolver->getUser());

        $entity = $this->apiCommentService->findVisibleComment((int) $uriVariables['id']);
        if ($entity === null) {
            throw new NotFoundException(
                $this->translator->trans('api_errors.find.comment.not.found', domain: 'api_errors'),
            );
        }
        return (new CommentMapper($isModerator))->map($entity);
    }
}
