<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Page d'un commentaire d'administration, avec son titre dans la langue de contenu et son adresse sur le site
 */

namespace App\Api\V2\Output\Admin\Content;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Attribute\Groups;

final readonly class AdminCommentPage
{
    /**
     * Groupe de sérialisation à ajouter aux groupes de la ressource qui expose la page
     * @var string
     */
    public const string GROUP = 'admin:comment_page';

    /**
     * @param int $id
     * @param string $title
     * @param string|null $url adresse sur le site, lue avec le commentaire seul
     */
    public function __construct(
        #[Groups([self::GROUP])] #[ApiProperty(required: true)] public int $id,
        #[Groups([self::GROUP])] #[ApiProperty(required: true)] public string $title,
        #[Groups([self::GROUP])] #[ApiProperty(required: true)] public ?string $url = null,
    ) {}
}
