<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Processor qui ajoute un commentaire à une page publiée, limité par IP cliente
 */

namespace App\Api\V2\State\Processor\Content;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Api\V2\Exception\ForbiddenException;
use App\Api\V2\Exception\NotFoundException;
use App\Api\V2\Exception\TooManyRequestsException;
use App\Api\V2\Input\Content\CommentInput;
use App\Api\V2\Mapper\Content\CommentMapper;
use App\Api\V2\Resource\Content\Comment;
use App\Service\Api\Content\ApiCommentService;
use App\Service\Api\Content\Page\ApiPageService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @implements ProcessorInterface<CommentInput, Comment>
 */
final readonly class CreateCommentProcessor implements ProcessorInterface
{
    public function __construct(
        private ApiCommentService $apiCommentService,
        private ApiPageService $apiPageService,
        private TranslatorInterface $translator,
        #[Autowire(service: 'limiter.api_add_comment')] private RateLimiterFactoryInterface $limiter,
    ) {}

    /**
     * @param CommentInput $data
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return Comment
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Comment
    {
        /** @var Request $request */
        $request = $context['request'];
        $clientIp = (string) $request->getClientIp();

        // Un front serveur doit transmettre l'IP du visiteur via trusted_proxies (X-Forwarded-For)
        $limit = $this->limiter->create($clientIp)->consume();
        if (!$limit->isAccepted()) {
            throw new TooManyRequestsException(
                max(0, $limit->getRetryAfter()->getTimestamp() - time()),
                $this->translator->trans('api_errors.too.many.requests', domain: 'api_errors'),
            );
        }

        $page = $this->apiPageService->findPublishedPage((int) $data->page?->id);
        if ($page === null) {
            throw new NotFoundException(
                $this->translator->trans('api_errors.find.page.not.found', domain: 'api_errors'),
            );
        }
        if (!$this->apiCommentService->isOpenForComments($page)) {
            throw new ForbiddenException($this->translator->trans('api_errors.comment.not.open', domain: 'api_errors'));
        }

        $comment = $this->apiCommentService->createComment(
            $page,
            $this->apiPageService->getContentLocale(),
            $data->author,
            $data->email,
            $data->comment,
            $clientIp,
            $data->userAgent ?? (string) $request->headers->get('User-Agent'),
        );
        return (new CommentMapper(false))->map($comment);
    }
}
