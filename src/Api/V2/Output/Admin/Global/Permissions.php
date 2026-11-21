<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Droits de l'utilisateur sur un élément d'administration
 */

namespace App\Api\V2\Output\Admin\Global;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Attribute\Groups;

final readonly class Permissions
{
    /**
     * Groupe de sérialisation à ajouter aux groupes de la ressource qui expose ses droits
     * @var string
     */
    public const string GROUP = 'admin:permissions';

    public function __construct(
        #[Groups([self::GROUP])] #[ApiProperty(required: true)] public bool $edit,
        #[Groups([self::GROUP])] #[ApiProperty(required: true)] public bool $disable,
        #[Groups([self::GROUP])] #[ApiProperty(required: true)] public bool $delete,
    ) {}
}
