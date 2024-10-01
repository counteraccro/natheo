<?php
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Controller pour les authentifications via API
 */

namespace App\Controller\Api;

use App\Service\Admin\System\User\UserDataService;
use App\Service\Admin\System\User\UserService;
use App\Utils\Api\ApiConst;
use App\Utils\Api\ApiParametersParser;
use App\Utils\Api\ApiParametersRef;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

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
     * @param Request $request
     * @param ApiParametersParser $apiParametersParser
     * @param UserService $userService
     * @param TranslatorInterface $translator
     * @param UserDataService $userDataService
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \DateMalformedStringException
     */
    #[Route('/user', name: 'auth_user', methods: ['POST'], format: 'json')]
    public function authUser(
        Request             $request,
        ApiParametersParser $apiParametersParser,
        UserService         $userService,
        TranslatorInterface $translator,
        UserDataService     $userDataService
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $return = $apiParametersParser->parse(ApiParametersRef::PARAMS_REF_AUTH_USER, $data);

        if (!empty($return)) {
            return $this->apiResponse(ApiConst::API_MSG_ERROR, [], $return, Response::HTTP_FORBIDDEN);
        }

        $user = $userService->getUserByEmailAndPassword($data['username'], $data['password']);

        if ($user === null || (count($user->getRoles()) === 1 && $user->getRoles()[0] === 'ROLE_USER')) {
            return $this->apiResponse(ApiConst::API_MSG_ERROR, [], [$translator->trans('api_errors.user.not.found', domain: 'api_errors')], Response::HTTP_FORBIDDEN);
        }

        $token = $userDataService->generateUserToken($user);

        return $this->apiResponse(ApiConst::API_MSG_SUCCESS, ['token' => $token]);
    }


}
