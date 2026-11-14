<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * En-tête Accept-Language des ressources de contenu, utilisé quand « locale » est absent
 */

namespace App\Api\V2\Metadata;

use ApiPlatform\Metadata\HeaderParameter;
use App\Api\V2\Filter\LocaleFilter;

final class AcceptLanguageParameter extends HeaderParameter
{
    public function __construct()
    {
        parent::__construct(schema: ['type' => 'string'], description: LocaleFilter::HEADER_DESCRIPTION);
    }
}
