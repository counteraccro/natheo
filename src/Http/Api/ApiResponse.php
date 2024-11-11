<?php
/**
 * Créer une réponse d'API en cas d'erreur
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Http\Api;

use Symfony\Component\HttpFoundation\JsonResponse;

class ApiResponse extends JsonResponse
{
    /**
     * ApiResponse constructor.
     *
     * @param string $message
     * @param mixed $data
     * @param array $errors
     * @param int $status
     * @param array $headers
     * @param bool $json
     */
    public function __construct(string $message, mixed $data = null, array $errors = [], int $status = 200, array $headers = [], bool $json = false)
    {
        parent::__construct($this->format($message, $data, $errors, $status), $status, $headers, $json);
    }

    /**
     * Format the API response.
     *
     * @param string $message
     * @param mixed|null $data
     * @param array $errors
     * @param int $status
     * @return array
     */
    private function format(string $message, mixed $data, array $errors, int $status): array
    {
        if ($data === null) {
            $data = [];
        }

        $response = [
            'code_http' => $status,
            'message' => $message,
        ];

        if ($data) {
            $response['data'] = $data;
        }

        if ($errors) {
            $response['errors'] = $errors;
        }

        return $response;
    }
}
