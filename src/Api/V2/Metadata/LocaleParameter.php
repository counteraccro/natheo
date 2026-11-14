<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Paramètre « locale » des ressources de contenu, limité aux langues du site par LocaleFilter
 */

namespace App\Api\V2\Metadata;

use ApiPlatform\Metadata\QueryParameter;
use App\Api\V2\Filter\LocaleFilter;

final class LocaleParameter extends QueryParameter
{
    public function __construct()
    {
        parent::__construct(
            filter: LocaleFilter::class,
            property: 'locale',
            description: LocaleFilter::LOCALE_DESCRIPTION,
        );
    }
}
