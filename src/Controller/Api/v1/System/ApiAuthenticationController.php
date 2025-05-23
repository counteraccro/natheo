<?php
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Controller pour les authentifications via API
 */

namespace App\Controller\Api\v1\System;

use App\Controller\Api\v1\AppApiController;
use App\Dto\Api\Authentication\ApiAuthUserDto;
use App\Resolver\Api\ApiAuthUserResolver;
use App\Utils\Api\ApiConst;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/{api_version}/authentication', name: 'api_authentication_', requirements: ['_version' => '%app.api_version%'])]
#[IsGranted("ROLE_READ_API")]
class ApiAuthenticationController extends AppApiController
{
    /**
     * Retourne le role si l'authentification est un succès
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('', name: 'auth', methods: ['GET'], format: 'json')]
    #[Route('/', name: 'auth', methods: ['GET'], format: 'json')]
    public function auth(): JsonResponse
    {
        return $this->apiResponse(ApiConst::API_MSG_SUCCESS, [
            'roles' => $this->getUser()->getRoles()
        ]);
    }

    /**
     * Permet d'authentifier un utilisateur et retourne un token si l'authentification est bonne
     * @param ApiAuthUserDto $apiAuthUserDto
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \DateMalformedStringException
     */
    #[Route('/user', name: 'auth_user', methods: ['POST'], format: 'json')]
    public function authUser(
        #[MapRequestPayload(
            resolver: ApiAuthUserResolver::class
        )] ApiAuthUserDto $apiAuthUserDto,
    ): JsonResponse
    {

        $translator = $this->getTranslator();
        $userService = $this->getUserService();
        $userDataService = $this->getUserDataService();

        $user = $userService->getUserByEmailAndPassword($apiAuthUserDto->getUsername(), $apiAuthUserDto->getPassword());
        if ($user === null || (count($user->getRoles()) === 1 && $user->getRoles()[0] === 'ROLE_USER')) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, $translator->trans('api_errors.user.not.found', domain: 'api_errors'));
        }
        $token = $userDataService->generateUserToken($user);
        return $this->apiResponse(ApiConst::API_MSG_SUCCESS, ['token' => $token]);
    }


}
