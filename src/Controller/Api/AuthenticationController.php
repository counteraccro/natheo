<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/{_version}', name: 'api_authentication', requirements: ['_version' => '%app.api_version%'])]
class AuthenticationController extends AbstractController
{
    #[Route('/authentication', name: '')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/Api/AuthenticationController.php',
        ]);
    }

    #[Route('/demo', name: 'demo', methods: ['GET'])]
    #[IsGranted("ROLE_READ_API")]
    public function demo(): JsonResponse
    {
        return $this->json(['message' => $this->getUser()]);
    }
}
