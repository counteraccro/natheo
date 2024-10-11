<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Controller pour les menus via API
 */
namespace App\Controller\Api\v1;

use App\Dto\Api\Menu\ApiFindMenuDto;
use App\Resolver\Api\ApiFindMenuResolver;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
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
    public function find( #[MapQueryString(
        resolver: ApiFindMenuResolver::class
    )] ApiFindMenuDto $apiFindMenuDto): JsonResponse
    {
        $userEmail = 'pas user';
        if($apiFindMenuDto->userToken !== "")
        {
            $user = $this->getUserByUserToken($apiFindMenuDto->userToken);
            $userEmail = $user->getEmail();
        }


        return $this->json([
            'return' => $apiFindMenuDto,
            'user' => $userEmail,
        ]);
    }
}
