<?php
/**
 * Controller global pour l'API
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Controller\Api\v1;

use App\Entity\Admin\System\User;
use App\Http\Api\ApiResponse;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AppApiController extends AppApiHandlerController
{
    /**
     * Créer une réponse d'API formatée
     * @param string $message
     * @param mixed $data
     * @param array $errors
     * @param int $status
     * @param array $headers
     * @return ApiResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function apiResponse(
        string $message,
        mixed $data,
        array $errors = [],
        int $status = 200,
        array $headers = [],
    ): JsonResponse {
        if (empty($headers)) {
            $headers = ['Content-Type' => 'application/json'];
        }

        $body = $this->formatApiResponse($message, $data, $errors, $status);

        if ($this->container->has('serializer')) {
            $json = $this->container->get('serializer')->serialize(
                $body,
                'json',
                array_merge(
                    [
                        'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS,
                    ],
                    [],
                ),
            );
            return new JsonResponse($json, $status, $headers, true);
        }
        return new JsonResponse($body, $status, $headers, false);
    }

    /**
     * Format le retour d'API
     * @param string $message
     * @param mixed $data
     * @param array $errors
     * @param int $status
     * @return array
     */
    private function formatApiResponse(string $message, mixed $data, array $errors, int $status): array
    {
        if ($data === null) {
            $data = [];
        }

        $response = [
            'code_http' => $status,
            'message' => $message,
            'data' => $data,
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return $response;
    }

    /**
     * Retourne un utilisateur en fonction de son token, si token invalide génère une HttpException
     * @param string $userToken
     * @return User
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getUserByUserToken(string $userToken): User
    {
        $apiUserService = $this->getApiUserService();
        $translator = $this->getTranslator();
        $user = $apiUserService->getUserByUserToken($userToken);
        if ($user === null) {
            throw new HttpException(
                Response::HTTP_FORBIDDEN,
                $translator->trans($translator->trans('api_errors.user.token.not.found', domain: 'api_errors')),
            );
        }
        return $user;
    }
}
