<?php

declare(strict_types=1);
/**
 * Exception HTTP d'API portant une liste d'erreurs, renvoyée telle quelle dans la clé "errors"
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Http\Api;

use Symfony\Component\HttpKernel\Exception\HttpException;

class ApiHttpException extends HttpException
{
    /**
     * @param int $statusCode
     * @param array $errors liste des messages d'erreur
     * @param array $headers
     */
    public function __construct(int $statusCode, private readonly array $errors, array $headers = [])
    {
        parent::__construct($statusCode, implode(' ', $errors), headers: $headers);
    }

    /**
     * Retourne la liste des messages d'erreur
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
