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
    /**
     * @return string
     */
    public function getType(): string
    {
        return '/errors/404';
    }

    /**
     * Titre de la documentation OpenAPI, traduit dans les réponses par TranslatedErrorProvider
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return 'Not found';
    }

    /**
     * @return int|null
     */
    public function getStatus(): ?int
    {
        return 404;
    }

    /**
     * @return string|null
     */
    public function getDetail(): ?string
    {
        return $this->getMessage();
    }

    /**
     * @return string|null
     */
    public function getInstance(): ?string
    {
        return null;
    }
}
