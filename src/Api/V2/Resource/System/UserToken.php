<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Ressource API v2 : token d'un utilisateur du back-office
 */

namespace App\Api\V2\Resource\System;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation as OpenApiOperation;
use ApiPlatform\Validator\Exception\ValidationException;
use App\Api\V2\Exception\ForbiddenException;
use App\Api\V2\Exception\TooManyRequestsException;
use App\Api\V2\Exception\UnauthorizedException;
use App\Api\V2\Input\System\AuthenticateUserInput;
use App\Api\V2\Security\UserTokenHeader;
use App\Api\V2\Security\UserTokenResolver;
use App\Api\V2\State\Processor\System\AuthenticateUserProcessor;
use App\Api\V2\State\Processor\System\RevokeUserTokenProcessor;

#[
    ApiResource(
        operations: [
            new Post(
                uriTemplate: '/authentication/user',
                status: 200,
                errors: [UnauthorizedException::class, TooManyRequestsException::class, ValidationException::class],
                openapi: new OpenApiOperation(
                    summary: 'Authentifie un utilisateur du back-office',
                    description: 'Renvoie le token à transmettre dans l\'en-tête X-User-Token. Une nouvelle ' .
                        'authentification remplace le token précédent. Refusé pour un utilisateur sans rôle ' .
                        'd\'administration ; tentatives limitées par IP et email.',
                ),
                security: "is_granted('ROLE_READ_API')",
                input: AuthenticateUserInput::class,
                processor: AuthenticateUserProcessor::class,
            ),
            new Delete(
                uriTemplate: '/authentication/user',
                errors: [ForbiddenException::class],
                openapi: new OpenApiOperation(
                    summary: 'Déconnecte l\'utilisateur du back-office',
                    description: 'Révoque le token de l\'en-tête X-User-Token.',
                ),
                security: "is_granted('ROLE_READ_API')",
                read: false,
                processor: RevokeUserTokenProcessor::class,
                parameters: [
                    UserTokenResolver::HEADER => new UserTokenHeader(documentedAsRequired: true),
                ],
            ),
        ],
    ),
]
final readonly class UserToken
{
    public function __construct(
        /**
         * Token à transmettre dans l'en-tête X-User-Token
         */
        #[ApiProperty(required: true, identifier: false)] public string $token,
        #[ApiProperty(required: true)] public \DateTimeInterface $expiresAt,
    ) {}
}
