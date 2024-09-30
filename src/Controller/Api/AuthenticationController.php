<?php
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Controller pour les authentifications via API
 */

namespace App\Controller\Api;

use App\Security\Provider\ApiProvider;
use App\Service\Api\ApiService;
use App\Utils\Api\ApiParametersParser;
use App\Utils\Api\ApiParametersRef;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/{_version}/authentication', name: 'api_authentication_', requirements: ['_version' => '%app.api_version%'])]
#[IsGranted("ROLE_READ_API")]
class AuthenticationController extends AppApiController
{
    /**
     * Retourne le role si l'authentification est un succès
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/', name: 'auth', methods: ['GET'], format: 'json')]
    public function auth(): JsonResponse
    {
        return $this->apiResponse('success', [
            'roles' => $this->getUser()->getRoles()
        ]);
    }

    /**
     * Permet d'authentifier un utilisateur
     * @param Request $request
     * @param ApiParametersParser $apiParametersParser
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/user', name: 'auth_user', methods: ['POST'], format: 'json')]
    public function authUser(Request $request, ApiParametersParser $apiParametersParser): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $return = $apiParametersParser->parse(ApiParametersRef::PARAMS_REF_AUTH_USER, $data);

        if (!empty($return)) {
            return $this->apiResponse('error', [], $return, Response::HTTP_FORBIDDEN);
        }

        return $this->apiResponse('success', [
        ]);
    }


}
