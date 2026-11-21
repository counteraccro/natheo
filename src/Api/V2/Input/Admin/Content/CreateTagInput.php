<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Données de création d'un tag
 */

namespace App\Api\V2\Input\Admin\Content;

use ApiPlatform\Metadata\ApiProperty;
use App\Api\V2\Input\Admin\Global\LabelTranslationInput;
use App\Api\V2\Validator\Admin\Content\ValidTagInput;

#[ValidTagInput]
final class CreateTagInput
{
    /**
     * Couleur #rrggbb, tirée au hasard si absente
     */
    public ?string $color = null;

    public bool $disabled = false;

    /**
     * Au moins une langue du site ; une langue absente reçoit un libellé libre tiré du premier libellé
     * @var LabelTranslationInput[]
     */
    #[ApiProperty(required: true)]
    public array $translations = [];
}
