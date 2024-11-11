<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Controller pour les menus via API
 */

namespace App\Controller\Api\v1\Content;

use App\Controller\Api\v1\AppApiController;
use App\Dto\Api\Content\Menu\ApiFindMenuDto;
use App\Resolver\Api\Content\Menu\ApiFindMenuResolver;
use App\Utils\Api\ApiConst;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/{api_version}/menu', name: 'api_menu_', requirements: ['_version' => '%app.api_version%'])]
#[IsGranted("ROLE_READ_API")]
class ApiMenuController extends AppApiController
{
    /**
     * Retourne un menu en fonction de l'url d'une page
     * @param ApiFindMenuDto $apiFindMenuDto
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/find', name: 'find', methods: ['GET'], format: 'json')]
    public function find(#[MapQueryString(
        resolver: ApiFindMenuResolver::class
    )] ApiFindMenuDto $apiFindMenuDto): JsonResponse
    {
        $user = null;
        if ($apiFindMenuDto->getUserToken() !== "") {
            $user = $this->getUserByUserToken($apiFindMenuDto->getUserToken());
        }

        $apiMenuService = $this->getApiMenuService();
        $menu = $apiMenuService->getMenuForApi($apiFindMenuDto, $user);

        $translator = $this->getTranslator();
        if (empty($menu)) {
            throw new HttpException(Response::HTTP_FORBIDDEN, $translator->trans('api_errors.find.menu.not.found', domain: 'api_errors'));
        }
        return $this->apiResponse(ApiConst::API_MSG_SUCCESS, $menu);
    }
}
