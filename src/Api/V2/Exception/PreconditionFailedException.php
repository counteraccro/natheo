<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Réponse 412 quand l'en-tête If-Match ne correspond plus à la version de l'élément
 */

namespace App\Api\V2\Exception;

use ApiPlatform\Metadata\Exception\ProblemExceptionInterface;
use Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException;

final class PreconditionFailedException extends PreconditionFailedHttpException implements ProblemExceptionInterface
{
    use ProblemExceptionTrait;

    /**
     * Titre de la documentation OpenAPI, traduit dans les réponses par TranslatedErrorProvider
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return 'Precondition failed';
    }
}
