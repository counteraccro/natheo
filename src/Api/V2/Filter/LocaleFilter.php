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
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class LocaleFilter implements FilterInterface, JsonSchemaFilterInterface
{
    use BackwardCompatibleFilterDescriptionTrait;

    /**
     * Nom de l'en-tête de langue déclaré sur les opérations
     * @var string
     */
    public const string HEADER = 'Accept-Language';

    /**
     * Description du paramètre « locale »
     * @var string
     */
    public const string LOCALE_DESCRIPTION = 'Langue du contenu, prioritaire sur l\'en-tête Accept-Language';

    /**
     * Description de l'en-tête Accept-Language
     * @var string
     */
    public const string HEADER_DESCRIPTION = 'Langues acceptées (ex : en-US,en;q=0.9), langue par défaut du site si aucune n\'est supportée';

    public function __construct(#[Autowire('%app.supported_locales%')] private string $supportedLocales) {}

    /**
     * @param Parameter $parameter
     * @return array<string, mixed>
     */
    public function getSchema(Parameter $parameter): array
    {
        return ['type' => 'string', 'enum' => explode('|', $this->supportedLocales)];
    }
}
