<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Libellé d'un contenu d'administration dans une langue, envoyé à l'API
 */

namespace App\Api\V2\Input\Admin\Global;

use ApiPlatform\Metadata\ApiProperty;

final class LabelTranslationInput
{
    #[ApiProperty(required: true)]
    public string $locale = '';

    #[ApiProperty(required: true)]
    public string $label = '';
}
