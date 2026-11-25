<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Données de création d'une FAQ
 */

namespace App\Api\V2\Input\Admin\Content;

use ApiPlatform\Metadata\ApiProperty;
use App\Api\V2\Input\Admin\Global\TitleTranslationInput;
use App\Api\V2\Validator\Admin\Content\ValidFaqInput;

#[ValidFaqInput]
final class CreateFaqInput
{
    public bool $disabled = false;

    /**
     * Titre de chaque langue du site
     * @var TitleTranslationInput[]
     */
    #[ApiProperty(required: true)]
    public array $translations = [];
}
