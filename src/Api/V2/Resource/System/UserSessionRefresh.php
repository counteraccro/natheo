<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Ressource API v2 : renouvellement des tokens d'une session par son refresh token
 */

namespace App\Api\V2\Resource\System;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation as OpenApiOperation;
use ApiPlatform\Validator\Exception\ValidationException;
use App\Api\V2\Exception\InvalidGrantException;
use App\Api\V2\State\Processor\System\CreateUserSessionRefreshProcessor;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[
    ApiResource(
        operations: [
            new Post(
                uriTemplate: '/user_session_refreshes',
                status: 200,
                errors: [InvalidGrantException::class, ValidationException::class],
                openapi: new OpenApiOperation(
                    summary: 'Renouvelle les tokens d\'une session',
                    description: 'Le refresh token est remplacé à chaque appel ; réutiliser un refresh token ' .
                        'déjà remplacé ferme la session. Deux renouvellements simultanés avec le même refresh ' .
                        'token sont donc vus comme une réutilisation : le client ne doit en lancer qu\'un à la fois.',
                ),
                normalizationContext: ['groups' => ['user_session:create']],
                denormalizationContext: ['groups' => ['user_session_refresh:write']],
                security: "is_granted('ROLE_READ_API')",
                output: UserSession::class,
                processor: CreateUserSessionRefreshProcessor::class,
            ),
        ],
    ),
]
final class UserSessionRefresh
{
    #[ApiProperty(identifier: false, required: true)]
    #[Groups(['user_session_refresh:write'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $refreshToken = '';
}
