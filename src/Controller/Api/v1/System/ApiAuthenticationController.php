<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.2
 * Controller pour les authentifications via API
 */

namespace App\Controller\Api\v1\System;

use App\Controller\Api\v1\AppApiController;
use App\Dto\Api\Authentication\ApiAuthUserDto;
use App\Resolver\Api\ApiAuthUserResolver;
use App\Service\Api\ApiService;
use App\Service\Api\System\User\UserSessionService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Enum\Api\ApiMessage;

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
        return $this->apiResponse(ApiMessage::SUCCESS->value, [
            'roles' => $this->getUser()->getRoles(),
        ]);
    }

    /**
     * Ouvre une session API pour un utilisateur et retourne son access token si l'authentification est bonne
     * Les tentatives sont limitées par couple IP / login (rate limiter api_auth_user)
     * @param ApiAuthUserDto $apiAuthUserDto
     * @param Request $request
     * @param UserSessionService $userSessionService
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/user', name: 'auth_user', methods: ['POST'], format: 'json')]
    public function authUser(
        #[MapRequestPayload(resolver: ApiAuthUserResolver::class)] ApiAuthUserDto $apiAuthUserDto,
        Request $request,
        UserSessionService $userSessionService,
    ): JsonResponse {
        $translator = $this->getTranslator();
        $clientIp = (string) $request->getClientIp();

        $result = $userSessionService->authenticate(
            $apiAuthUserDto->getUsername(),
            $apiAuthUserDto->getPassword(),
            $clientIp,
        );
        if ($result->isThrottled()) {
            throw new HttpException(
                Response::HTTP_TOO_MANY_REQUESTS,
                $translator->trans('api_errors.too.many.requests', domain: 'api_errors'),
                headers: ['Retry-After' => strval($result->retryAfter)],
            );
        }
        if ($result->user === null) {
            throw new HttpException(
                Response::HTTP_UNAUTHORIZED,
                $translator->trans('api_errors.user.not.found', domain: 'api_errors'),
            );
        }

        $tokens = $userSessionService->create(
            $result->user,
            $request->attributes->get(ApiService::ATTRIBUTE_API_TOKEN),
            $request->headers->get('User-Agent'),
            $clientIp,
        );
        return $this->apiResponse(ApiMessage::SUCCESS->value, ['token' => $tokens->accessToken]);
    }
}
