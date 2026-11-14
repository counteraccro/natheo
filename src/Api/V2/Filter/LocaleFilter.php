<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Langue des ressources de contenu : paramètre « locale » limité à app.supported_locales,
 * en-tête Accept-Language à défaut (résolus par ApiLocaleListener)
 */

namespace App\Api\V2\Filter;

use ApiPlatform\Metadata\BackwardCompatibleFilterDescriptionTrait;
use ApiPlatform\Metadata\FilterInterface;
use ApiPlatform\Metadata\JsonSchemaFilterInterface;
use ApiPlatform\Metadata\Parameter;
use App\Service\Global\SupportedLocales;

final readonly class LocaleFilter implements FilterInterface, JsonSchemaFilterInterface
{
    use BackwardCompatibleFilterDescriptionTrait;

    public function __construct(private SupportedLocales $supportedLocales) {}

    /**
     * @param Parameter $parameter
     * @return array<string, mixed>
     */
    public function getSchema(Parameter $parameter): array
    {
        return ['type' => 'string', 'enum' => $this->supportedLocales->getAll()];
    }
}
