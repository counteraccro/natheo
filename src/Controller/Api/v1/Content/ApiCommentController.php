<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Controller pour les API commentaires
 */

namespace App\Controller\Api\v1\Content;

use App\Controller\Api\v1\AppApiController;
use App\Dto\Api\Content\Comment\ApiAddCommentDto;
use App\Dto\Api\Content\Comment\ApiCommentByPageDto;
use App\Dto\Api\Content\Page\ApiFindPageDto;
use App\Http\Api\ApiResponse;
use App\Resolver\Api\Content\Comment\ApiAddCommentResolver;
use App\Resolver\Api\Content\Comment\ApiCommentByPageResolver;
use App\Utils\Api\ApiConst;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/{api_version}/comment', name: 'api_comment_', requirements: ['_version' => '%app.api_version%'])]
#[IsGranted("ROLE_READ_API")]
class ApiCommentController extends AppApiController
{
    /**
     * @param ApiCommentByPageDto $apiCommentByPageDto
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/page', name: 'by_page', methods: ['GET'])]
    public function getCommentsByPage(#[MapQueryString(
        resolver: ApiCommentByPageResolver::class
    )] ApiCommentByPageDto $apiCommentByPageDto): JsonResponse
    {

        $user = null;
        if ($apiCommentByPageDto->getUserToken() !== "") {
            $user = $this->getUserByUserToken($apiCommentByPageDto->getUserToken());
        }

        $apiCommentService = $this->getApiCommentService();
        $result = $apiCommentService->getCommentByPageIdOrSlug($apiCommentByPageDto, $user);

        return $this->apiResponse(ApiConst::API_MSG_SUCCESS, $result);
    }

    /**
     * Ajout un nouveau commentaire
     * @param ApiAddCommentDto $apiAddCommentDto
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/', name: 'add_comment', methods: ['POST'])]
    public function add(#[MapQueryString(
        resolver: ApiAddCommentResolver::class
    )] ApiAddCommentDto $apiAddCommentDto): JsonResponse
    {
        return $this->apiResponse(ApiConst::API_MSG_SUCCESS, [$apiAddCommentDto]);
    }
}
