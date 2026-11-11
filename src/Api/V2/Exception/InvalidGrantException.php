<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Identifiants ou refresh token refusés (400 invalid_grant, RFC 6749 §5.2)
 */

namespace App\Api\V2\Exception;

use ApiPlatform\Metadata\Exception\ProblemExceptionInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class InvalidGrantException extends BadRequestHttpException implements ProblemExceptionInterface
{
    use ProblemExceptionTrait;

    /**
     * Type problem+json propre à ce refus, distinct des autres 400
     * @return string
     */
    public function getType(): string
    {
        return '/errors/invalid_grant';
    }

    /**
     * Titre de la documentation OpenAPI, traduit dans les réponses par TranslatedErrorProvider
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return 'Invalid grant';
    }
}
