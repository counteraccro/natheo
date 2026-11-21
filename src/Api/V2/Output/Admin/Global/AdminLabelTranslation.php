<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Libellé d'un contenu d'administration dans une langue
 */

namespace App\Api\V2\Output\Admin\Global;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Attribute\Groups;

final readonly class AdminLabelTranslation
{
    /**
     * Groupe de sérialisation à ajouter aux groupes de la ressource qui expose ses libellés traduits
     * @var string
     */
    public const string GROUP = 'admin:label_translation';

    public function __construct(
        #[Groups([self::GROUP])] #[ApiProperty(required: true)] public string $locale,
        #[Groups([self::GROUP])] #[ApiProperty(required: true)] public string $label,
    ) {}
}
