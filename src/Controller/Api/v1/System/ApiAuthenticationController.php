<?php

declare(strict_types=1);
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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[
    Route(
        '/api/{api_version}/authentication',
        name: 'api_authentication_',
        requirements: ['api_version' => '%app.api_version%'],
    ),
]
#[IsGranted('ROLE_READ_API')]
class ApiAuthenticationController extends AppApiController
{
    /**
     * Retourne le role si l'authentification est un succès
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[
        Route(
            '{trailingSlash}',
            name: 'auth',
            requirements: ['trailingSlash' => '/?'],
            defaults: ['trailingSlash' => ''],
            methods: ['GET'],
            format: 'json',
        ),
    ]
    public function auth(): JsonResponse
    {
        return $this->apiResponse(ApiConst::API_MSG_SUCCESS, [
            'roles' => $this->getUser()->getRoles(),
        ]);
    }

    /**
     * Permet d'authentifier un utilisateur et retourne un token si l'authentification est bonne
     * Les tentatives sont limitées par couple IP / login (rate limiter api_auth_user)
     * @param ApiAuthUserDto $apiAuthUserDto
     * @param Request $request
     * @param RateLimiterFactoryInterface $apiAuthUserLimiter
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/user', name: 'auth_user', methods: ['POST'], format: 'json')]
    public function authUser(
        #[MapRequestPayload(resolver: ApiAuthUserResolver::class)] ApiAuthUserDto $apiAuthUserDto,
        Request $request,
        RateLimiterFactoryInterface $apiAuthUserLimiter,
    ): JsonResponse {
        $translator = $this->getTranslator();
        $userService = $this->getUserService();
        $userDataService = $this->getUserDataService();

        $limiter = $apiAuthUserLimiter->create(
            $request->getClientIp() . '-' . mb_strtolower($apiAuthUserDto->getUsername()),
        );
        $limit = $limiter->consume();
        if (!$limit->isAccepted()) {
            throw new HttpException(
                Response::HTTP_TOO_MANY_REQUESTS,
                $translator->trans('api_errors.too.many.requests', domain: 'api_errors'),
                headers: ['Retry-After' => strval(max(0, $limit->getRetryAfter()->getTimestamp() - time()))],
            );
        }

        $user = $userService->getUserByEmailAndPassword($apiAuthUserDto->getUsername(), $apiAuthUserDto->getPassword());
        if ($user === null || (count($user->getRoles()) === 1 && $user->getRoles()[0] === 'ROLE_USER')) {
            throw new HttpException(
                Response::HTTP_UNAUTHORIZED,
                $translator->trans('api_errors.user.not.found', domain: 'api_errors'),
            );
        }
        $limiter->reset();
        $token = $userDataService->generateUserToken($user);
        return $this->apiResponse(ApiConst::API_MSG_SUCCESS, ['token' => $token]);
    }
}
