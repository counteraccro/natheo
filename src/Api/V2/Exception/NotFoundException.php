<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Ressource de l'API v2 introuvable ou non publiée (404), déclarée dans les erreurs des opérations
 */

namespace App\Api\V2\Exception;

use ApiPlatform\Metadata\Exception\ProblemExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class NotFoundException extends NotFoundHttpException implements ProblemExceptionInterface
{
    use ProblemExceptionTrait;

    /**
     * Titre de la documentation OpenAPI, traduit dans les réponses par TranslatedErrorProvider
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return 'Not found';
    }
}
