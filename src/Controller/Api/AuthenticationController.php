<?php

namespace App\Controller\Api;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/{_version}/authentication', name: 'api_authentication', requirements: ['_version' => '%app.api_version%'])]
class AuthenticationController extends AppApiController
{
    /**
     * Retourne le role si l'authentification est un succès
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('', name: 'auth', methods: ['GET'], format: 'json')]
    public function index(): JsonResponse
    {
       return $this->apiResponse('success', [
          'roles' => $this->getUser()->getRoles()
       ]);
    }

    #[Route('/demo', name: 'demo', methods: ['GET'])]
    #[IsGranted("ROLE_READ_API2")]
    public function demo(): JsonResponse
    {

        return $this->apiResponse('success', ['user' => $this->getUser()], []);
    }
}
