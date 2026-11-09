<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Ressource API v2 : lecture d'une page publiée, comptée dans ses statistiques
 */

namespace App\Api\V2\Resource\Content;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Validator\Exception\ValidationException;
use App\Api\V2\Exception\NotFoundException;
use App\Api\V2\State\Processor\Content\CreatePageReadProcessor;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[
    ApiResource(
        operations: [
            new Post(
                uriTemplate: '/page_reads',
                errors: [NotFoundException::class, ValidationException::class],
                status: 204,
                description: 'Compte une lecture de la page',
                denormalizationContext: ['groups' => ['page_read:write']],
                security: "is_granted('ROLE_READ_API')",
                output: false,
                processor: CreatePageReadProcessor::class,
            ),
        ],
    ),
]
final class PageRead
{
    /**
     * Page lue, par son IRI (/api/v2/pages/{id})
     */
    #[ApiProperty(identifier: false, required: true)]
    #[Groups(['page_read:write'])]
    #[Assert\NotNull]
    public ?Page $page = null;
}
