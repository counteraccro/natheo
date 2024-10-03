<?php

namespace App\Controller\Api\v1;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[\Symfony\Component\Routing\Annotation\Route('/api/{api_version}/menu', name: 'api_menu_', requirements: ['_version' => '%app.api_version%'])]
#[IsGranted("ROLE_READ_API")]
class ApiMenuController extends AppApiController
{
    /**
     * Retourne un menu en fonction de l'url d'une page
     * @return JsonResponse
     */
    #[Route('/find/', name: 'find', methods: ['GET'])]
    public function find(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/Api/v1/ApiMenuController.php',
        ]);
    }
}
