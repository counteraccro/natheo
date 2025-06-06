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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Exception\HttpException;
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
        $translator = $this->getTranslator();
        $apiCommentService = $this->getApiCommentService();
        $comment = $apiCommentService->addNewComment($apiAddCommentDto);

        if ($comment->getId() === null) {
            throw new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR, $translator->trans($translator->trans('api_errors.comment.not.save', domain: 'api_errors')));
        }

        return $this->apiResponse(ApiConst::API_MSG_SUCCESS, ['id' => $comment->getId()], status: Response::HTTP_CREATED);
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
    public function moderateComment(#[MapQueryString(
        resolver: ApiModerateCommentResolver::class
    )] ApiModerateCommentDto $apiModerateCommentDto, #[MapEntity(id: 'id')] Comment $comment): JsonResponse
    {
        $user = $this->getUserByUserToken($apiModerateCommentDto->getUserToken());
        return $this->apiResponse(ApiConst::API_MSG_SUCCESS, [$apiModerateCommentDto, $user->getEmail(), 'comment' => [$comment->getId()]], status: Response::HTTP_OK);
    }
}
