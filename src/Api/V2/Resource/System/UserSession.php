<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Ressource API v2 : session d'un utilisateur du back-office
 */

namespace App\Api\V2\Resource\System;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation as OpenApiOperation;
use ApiPlatform\Validator\Exception\ValidationException;
use App\Api\V2\Exception\ForbiddenException;
use App\Api\V2\Exception\InvalidGrantException;
use App\Api\V2\Exception\NotFoundException;
use App\Api\V2\Exception\TooManyRequestsException;
use App\Api\V2\Input\System\CreateUserSessionInput;
use App\Api\V2\State\Processor\System\CreateUserSessionProcessor;
use App\Api\V2\State\Processor\System\DeleteUserSessionProcessor;
use App\Api\V2\State\Provider\System\UserSessionCollectionProvider;
use App\Api\V2\State\Provider\System\UserSessionProvider;
use Symfony\Component\Serializer\Attribute\Groups;

#[
    ApiResource(
        operations: [
            new Post(
                uriTemplate: '/user_sessions',
                errors: [InvalidGrantException::class, TooManyRequestsException::class, ValidationException::class],
                openapi: new OpenApiOperation(
                    summary: 'Ouvre une session pour un utilisateur du back-office',
                    description: 'Renvoie un access token (15 min) à transmettre dans l\'en-tête Authorization à la ' .
                        'place du token d\'API, et un refresh token pour le renouveler. La session porte les rôles ' .
                        'du token d\'API de la requête. Refusée pour un utilisateur sans rôle d\'administration ; ' .
                        'tentatives limitées par IP et email.',
                ),
                normalizationContext: ['groups' => ['user_session:create']],
                security: "is_granted('ROLE_READ_API')",
                input: CreateUserSessionInput::class,
                processor: CreateUserSessionProcessor::class,
            ),
            new GetCollection(
                uriTemplate: '/user_sessions',
                paginationEnabled: false,
                errors: [ForbiddenException::class],
                openapi: new OpenApiOperation(
                    summary: 'Les sessions actives de l\'utilisateur',
                    description: 'Réservé à un access token de session.',
                ),
                normalizationContext: ['groups' => ['user_session:list']],
                security: "is_granted('ROLE_READ_API')",
                provider: UserSessionCollectionProvider::class,
            ),
            new Get(
                uriTemplate: '/user_sessions/{id}',
                requirements: ['id' => '\d+|current'],
                errors: [ForbiddenException::class, NotFoundException::class],
                openapi: new OpenApiOperation(
                    summary: 'Une session active de l\'utilisateur',
                    description: 'Réservé à un access token de session ; current désigne la session de la requête.',
                ),
                normalizationContext: ['groups' => ['user_session:read']],
                security: "is_granted('ROLE_READ_API')",
                provider: UserSessionProvider::class,
            ),
            new Delete(
                uriTemplate: '/user_sessions/{id}',
                requirements: ['id' => '\d+|current'],
                errors: [ForbiddenException::class, NotFoundException::class],
                openapi: new OpenApiOperation(
                    summary: 'Ferme une session de l\'utilisateur',
                    description: 'Réservé à un access token de session ; current ferme la session de la requête.',
                ),
                security: "is_granted('ROLE_READ_API')",
                provider: UserSessionProvider::class,
                processor: DeleteUserSessionProcessor::class,
            ),
        ],
    ),
]
final class UserSession
{
    #[ApiProperty(required: true, identifier: true)]
    #[Groups(['user_session:read', 'user_session:list', 'user_session:create'])]
    public int $id = 0;

    /**
     * Session de la requête
     */
    #[ApiProperty(required: true)]
    #[Groups(['user_session:read', 'user_session:list'])]
    public bool $current = false;

    #[ApiProperty(required: true)]
    #[Groups(['user_session:read', 'user_session:list'])]
    public ?string $userAgent = null;

    #[ApiProperty(required: true)]
    #[Groups(['user_session:read', 'user_session:list'])]
    public ?string $ip = null;

    #[ApiProperty(required: true)]
    #[Groups(['user_session:read', 'user_session:list'])]
    public ?\DateTimeInterface $createdAt = null;

    #[ApiProperty(required: true)]
    #[Groups(['user_session:read', 'user_session:list'])]
    public ?\DateTimeInterface $lastUsedAt = null;

    /**
     * Token à transmettre dans l'en-tête Authorization (Bearer), renvoyé seulement à sa création
     */
    #[ApiProperty(required: true)]
    #[Groups(['user_session:create'])]
    public ?string $accessToken = null;

    #[ApiProperty(required: true)]
    #[Groups(['user_session:create'])]
    public ?\DateTimeInterface $accessTokenExpiresAt = null;

    /**
     * Token de renouvellement (POST /user_session_refreshes), renvoyé seulement à sa création
     */
    #[ApiProperty(required: true)]
    #[Groups(['user_session:create'])]
    public ?string $refreshToken = null;

    #[ApiProperty(required: true)]
    #[Groups(['user_session:create'])]
    public ?\DateTimeInterface $refreshTokenExpiresAt = null;
}
