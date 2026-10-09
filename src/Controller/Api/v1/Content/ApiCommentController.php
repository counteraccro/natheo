<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Controller pour les API commentaires
 */

namespace App\Controller\Api\v1\Content;

use App\Controller\Api\v1\AppApiController;
use App\Dto\Api\Content\Comment\ApiAddCommentDto;
use App\Dto\Api\Content\Comment\ApiCommentByPageDto;
use App\Dto\Api\Content\Comment\ApiModerateCommentDto;
use App\Entity\Admin\Content\Comment\Comment;
use App\Resolver\Api\Content\Comment\ApiAddCommentResolver;
use App\Resolver\Api\Content\Comment\ApiCommentByPageResolver;
use App\Resolver\Api\Content\Comment\ApiModerateCommentResolver;
use App\Utils\Api\ApiConst;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/{api_version}/comment', name: 'api_comment_', requirements: ['api_version' => '%app.api_version%'])]
#[IsGranted('ROLE_READ_API')]
class ApiCommentController extends AppApiController
{
    /**
     * @param ApiCommentByPageDto $apiCommentByPageDto
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/page', name: 'by_page', methods: ['GET'])]
    public function getCommentsByPage(
        #[MapQueryString(resolver: ApiCommentByPageResolver::class)] ApiCommentByPageDto $apiCommentByPageDto,
    ): JsonResponse {
        $user = null;
        if ($apiCommentByPageDto->getUserToken() !== '') {
            $user = $this->getUserByUserToken($apiCommentByPageDto->getUserToken());
        }

        $apiCommentService = $this->getApiCommentService();
        $result = $apiCommentService->getCommentByPageIdOrSlug($apiCommentByPageDto, $user);

        return $this->apiResponse(ApiConst::API_MSG_SUCCESS, $result);
    }

    /**
     * Ajout un nouveau commentaire
     * @param ApiAddCommentDto $apiAddCommentDto
     * @param Request $request
     * @param RateLimiterFactoryInterface $apiAddCommentLimiter
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[
        Route(
            '{trailingSlash}',
            name: 'add_comment',
            requirements: ['trailingSlash' => '/?'],
            defaults: ['trailingSlash' => ''],
            methods: ['POST'],
        ),
    ]
    #[IsGranted('ROLE_WRITE_API')]
    public function add(
        #[MapRequestPayload(resolver: ApiAddCommentResolver::class)] ApiAddCommentDto $apiAddCommentDto,
        Request $request,
        RateLimiterFactoryInterface $apiAddCommentLimiter,
    ): JsonResponse {
        $translator = $this->getTranslator();
        $apiCommentService = $this->getApiCommentService();

        // Le front relaie tous les visiteurs depuis la même IP : on limite sur l'IP du visiteur transmise
        $ip = $apiAddCommentDto->getIp() !== '' ? $apiAddCommentDto->getIp() : $request->getClientIp();
        $limit = $apiAddCommentLimiter->create($ip)->consume();
        if (!$limit->isAccepted()) {
            throw new HttpException(
                Response::HTTP_TOO_MANY_REQUESTS,
                $translator->trans('api_errors.too.many.requests', domain: 'api_errors'),
                headers: ['Retry-After' => strval(max(0, $limit->getRetryAfter()->getTimestamp() - time()))],
            );
        }

        $comment = $apiCommentService->addNewComment($apiAddCommentDto);

        if ($comment->getId() === null) {
            throw new HttpException(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $translator->trans('api_errors.comment.not.save', domain: 'api_errors'),
            );
        }

        return $this->apiResponse(
            ApiConst::API_MSG_SUCCESS,
            ['id' => $comment->getId()],
            status: Response::HTTP_CREATED,
        );
    }

    /**
     * Edition d'un commentaire / modération
     * @param ApiModerateCommentDto $apiModerateCommentDto
     * @param Comment $comment
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/moderate/{id}', name: 'moderate_comment', methods: ['PUT'])]
    #[IsGranted('ROLE_WRITE_API')]
    public function moderateComment(
        #[MapRequestPayload(resolver: ApiModerateCommentResolver::class)] ApiModerateCommentDto $apiModerateCommentDto,
        #[MapEntity(id: 'id', message: 'Commentaire non disponible')] Comment $comment,
    ): JsonResponse {
        $translator = $this->getTranslator();
        $apiCommentService = $this->getApiCommentService();
        $user = $this->getUserByUserToken($apiModerateCommentDto->getUserToken());
        if (!$apiCommentService->canModerate($user)) {
            throw new HttpException(
                Response::HTTP_FORBIDDEN,
                $translator->trans('api_errors.comment.moderate.forbidden', domain: 'api_errors'),
            );
        }
        $apiCommentService->moderateComment($apiModerateCommentDto, $comment, $user);

        return $this->apiResponse(
            ApiConst::API_MSG_SUCCESS,
            [$translator->trans('api_errors.comment.moderate', domain: 'api_errors')],
            status: Response::HTTP_ACCEPTED,
        );
    }
}
