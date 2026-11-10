<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Limite de requêtes atteinte (429), avec l'en-tête Retry-After
 */

namespace App\Api\V2\Exception;

use ApiPlatform\Metadata\Exception\ProblemExceptionInterface;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

final class TooManyRequestsException extends TooManyRequestsHttpException implements ProblemExceptionInterface
{
    use ProblemExceptionTrait;

    /**
     * Titre de la documentation OpenAPI, traduit dans les réponses par TranslatedErrorProvider
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return 'Too many requests';
    }
}
