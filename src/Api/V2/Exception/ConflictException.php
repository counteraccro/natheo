<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Réponse 409 quand l'état de la ressource empêche une requête valide (ex : suppression d'un élément utilisé)
 */

namespace App\Api\V2\Exception;

use ApiPlatform\Metadata\Exception\ProblemExceptionInterface;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

final class ConflictException extends ConflictHttpException implements ProblemExceptionInterface
{
    use ProblemExceptionTrait;

    /**
     * Titre de la documentation OpenAPI, traduit dans les réponses par TranslatedErrorProvider
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return 'Conflict';
    }
}
