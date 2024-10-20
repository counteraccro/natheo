<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Controller pour les pages via API
 */
namespace App\Controller\Api\v1\Content;

use App\Controller\Api\v1\AppApiController;
use App\Dto\Api\Content\Page\ApiFindPageDto;
use App\Resolver\Api\Content\Page\ApiFindPageResolver;
use App\Utils\Api\ApiConst;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/{api_version}/page', name: 'api_page_', requirements: ['_version' => '%app.api_version%'])]
#[IsGranted("ROLE_READ_API")]
class ApiPageController extends AppApiController
{
    /**
     * Retourne une page en fonction de son slug
     * @param ApiFindPageDto $apiFindPageDto
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/find', name: 'find')]
    public function index(#[MapQueryString(
        resolver: ApiFindPageResolver::class
    )] ApiFindPageDto $apiFindPageDto): JsonResponse
    {
        $user = null;
        if ($apiFindPageDto->getUserToken() !== "") {
            $user = $this->getUserByUserToken($apiFindPageDto->getUserToken());
        }

        return $this->apiResponse(ApiConst::API_MSG_SUCCESS, ['menu' => '', 'dto' => $apiFindPageDto]);
    }
}
