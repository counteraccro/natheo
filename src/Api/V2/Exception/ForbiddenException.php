<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Action refusée par l'API v2 (403), déclarée dans les erreurs des opérations
 */

namespace App\Api\V2\Exception;

use ApiPlatform\Metadata\Exception\ProblemExceptionInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class ForbiddenException extends AccessDeniedHttpException implements ProblemExceptionInterface
{
    use ProblemExceptionTrait;

    /**
     * Titre de la documentation OpenAPI, traduit dans les réponses par TranslatedErrorProvider
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return 'Forbidden';
    }
}
