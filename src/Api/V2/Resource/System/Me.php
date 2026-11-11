<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Ressource API v2 : utilisateur du back-office de la session
 */

namespace App\Api\V2\Resource\System;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Model\Operation as OpenApiOperation;
use App\Api\V2\Exception\ForbiddenException;
use App\Api\V2\State\Provider\System\MeProvider;
use Symfony\Component\Serializer\Attribute\Groups;

#[
    ApiResource(
        operations: [
            new Get(
                uriTemplate: '/me',
                errors: [ForbiddenException::class],
                openapi: new OpenApiOperation(
                    summary: 'L\'utilisateur de la session',
                    description: 'Réservé à un access token de session.',
                ),
                normalizationContext: ['groups' => ['me:read']],
                security: "is_granted('ROLE_READ_API')",
                provider: MeProvider::class,
            ),
        ],
    ),
]
final class Me
{
    #[ApiProperty(required: true, identifier: false)]
    #[Groups(['me:read'])]
    public int $id = 0;

    #[ApiProperty(required: true)]
    #[Groups(['me:read'])]
    public string $login = '';

    #[ApiProperty(required: true)]
    #[Groups(['me:read'])]
    public string $email = '';

    #[ApiProperty(required: true)]
    #[Groups(['me:read'])]
    public ?string $firstname = null;

    #[ApiProperty(required: true)]
    #[Groups(['me:read'])]
    public ?string $lastname = null;

    /**
     * Rôles du back-office (ROLE_CONTRIBUTEUR, ROLE_ADMIN…)
     * @var string[]
     */
    #[ApiProperty(required: true)]
    #[Groups(['me:read'])]
    public array $roles = [];

    /**
     * Chemin public de l'avatar
     */
    #[ApiProperty(required: true)]
    #[Groups(['me:read'])]
    public ?string $avatar = null;

    /**
     * Droit de modérer les commentaires
     */
    #[ApiProperty(required: true)]
    #[Groups(['me:read'])]
    public bool $canModerate = false;
}
