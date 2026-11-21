<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Auteur d'un contenu d'administration
 */

namespace App\Api\V2\Output\Admin\Global;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Attribute\Groups;

final readonly class AdminAuthor
{
    /**
     * Groupe de sérialisation à ajouter aux groupes de la ressource qui expose son auteur
     * @var string
     */
    public const string GROUP = 'admin:author';

    public function __construct(
        #[Groups([self::GROUP])] #[ApiProperty(required: true)] public int $id,
        #[Groups([self::GROUP])] #[ApiProperty(required: true)] public string $login,
    ) {}
}
