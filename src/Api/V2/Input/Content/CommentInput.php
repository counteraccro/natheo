<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Données d'ajout d'un commentaire dans l'API v2
 */

namespace App\Api\V2\Input\Content;

use ApiPlatform\Metadata\ApiProperty;
use App\Api\V2\Resource\Content\Page;
use Symfony\Component\Validator\Constraints as Assert;

final class CommentInput
{
    /**
     * Page commentée, par son IRI (/api/v2/pages/{id})
     */
    #[ApiProperty(required: true)]
    #[Assert\NotNull]
    public ?Page $page = null;

    #[ApiProperty(required: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $author = '';

    #[Assert\Email]
    #[Assert\Length(max: 255)]
    public ?string $email = null;

    /**
     * Texte en Markdown brut, stocké sans assainissement
     */
    #[ApiProperty(required: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 10000)]
    public string $comment = '';

    /**
     * User-Agent du visiteur, celui de la requête à défaut
     */
    #[Assert\Length(max: 1000)]
    public ?string $userAgent = null;
}
