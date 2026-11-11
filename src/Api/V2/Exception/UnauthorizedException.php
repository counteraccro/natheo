<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Authentification refusée par l'API v2 (401), avec l'en-tête WWW-Authenticate
 */

namespace App\Api\V2\Exception;

use ApiPlatform\Metadata\Exception\ProblemExceptionInterface;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

final class UnauthorizedException extends UnauthorizedHttpException implements ProblemExceptionInterface
{
    use ProblemExceptionTrait;

    /**
     * @param string $message
     */
    public function __construct(string $message = '')
    {
        parent::__construct('Bearer', $message);
    }

    /**
     * Titre de la documentation OpenAPI, traduit dans les réponses par TranslatedErrorProvider
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return 'Unauthorized';
    }
}
