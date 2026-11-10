<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Provider de la liste paginée des commentaires actifs d'une page publiée
 */

namespace App\Api\V2\State\Provider\Content;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\QueryParameter;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\Pagination\TraversablePaginator;
use ApiPlatform\State\ProviderInterface;
use App\Api\V2\Exception\NotFoundException;
use App\Api\V2\Mapper\Content\CommentMapper;
use App\Api\V2\Resource\Content\Comment;
use App\Api\V2\Security\UserTokenResolver;
use App\Service\Api\Content\ApiCommentService;
use App\Service\Api\Content\Page\ApiPageService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @implements ProviderInterface<Comment>
 */
final readonly class CommentCollectionProvider implements ProviderInterface
{
    public function __construct(
        private ApiCommentService $apiCommentService,
        private ApiPageService $apiPageService,
        private UserTokenResolver $userTokenResolver,
        private Pagination $pagination,
        private TranslatorInterface $translator,
    ) {}

    /**
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return TraversablePaginator<Comment>
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): TraversablePaginator
    {
        $isModerator = $this->apiCommentService->canModerate($this->userTokenResolver->getUser());

        $page = $this->apiPageService->findPublishedPage((int) $uriVariables['pageId']);
        if ($page === null) {
            throw new NotFoundException(
                $this->translator->trans('api_errors.find.page.not.found', domain: 'api_errors'),
            );
        }

        $pageNumber = $this->pagination->getPage($context);
        $limit = $this->pagination->getLimit($operation, $context);
        $order = $operation->getParameters()?->get('order', QueryParameter::class)?->getValue(null);
        $paginator = $this->apiCommentService->findPageComments(
            $page,
            $pageNumber,
            $limit,
            $order === 'asc' ? \SortDirection::Ascending : \SortDirection::Descending,
        );

        $mapper = new CommentMapper($isModerator);
        $comments = [];
        foreach ($paginator as $entity) {
            $comments[] = $mapper->map($entity);
        }
        return new TraversablePaginator(new \ArrayIterator($comments), $pageNumber, $limit, $paginator->count());
    }
}
