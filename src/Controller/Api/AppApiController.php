<?php
/**
 * Controller global pour l'API
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Controller\Api;

use App\Http\Api\ApiResponse;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class AppApiController extends AbstractController
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
    protected function apiResponse(string $message, mixed $data, array $errors, int $status = 200, array $headers = []): JsonResponse
    {
        if (empty($headers)) {
            $headers = ['Content-Type' => 'application/json'];
        }

        $body = $this->formatApiResponse($message, $data,$errors, $status);

        if ($this->container->has('serializer')) {
            $json = $this->container->get('serializer')->serialize($body, 'json', array_merge([
                'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS,
            ], []));
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
}
