<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Controller pour les menus via API
 */
namespace App\Controller\Api\v1;

use App\Dto\Api\Menu\ApiFindMenuDto;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
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
     */
    #[Route('/find/{pageSlug}', name: 'find', methods: ['GET'])]
    public function find( #[MapQueryString] ApiFindMenuDto $apiFindMenuDto): JsonResponse
    {
        return $this->json([
            'return' => $apiFindMenuDto->pageSlug,
        ]);
    }
}
