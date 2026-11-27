<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Valeur proposée par une option à liste, avec son libellé traduit
 */

namespace App\Api\V2\Output\Admin\System;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Attribute\Groups;

final readonly class AdminOptionChoice
{
    /**
     * Groupe de sérialisation à ajouter aux groupes de la ressource qui expose ses choix
     * @var string
     */
    public const string GROUP = 'admin:option_choice';

    public function __construct(
        #[Groups([self::GROUP])] #[ApiProperty(required: true)] public string $value,
        #[Groups([self::GROUP])] #[ApiProperty(required: true)] public string $label,
    ) {}
}
