<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Langue des ressources de contenu : paramètre « locale » limité à app.supported_locales,
 * en-tête Accept-Language à défaut
 */

namespace App\Api\V2\Filter;

use ApiPlatform\Metadata\BackwardCompatibleFilterDescriptionTrait;
use ApiPlatform\Metadata\FilterInterface;
use ApiPlatform\Metadata\HeaderParameter;
use ApiPlatform\Metadata\JsonSchemaFilterInterface;
use ApiPlatform\Metadata\Operation;
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

    /**
     * Retourne la langue demandée et l'en-tête Accept-Language de l'opération, nommés comme les
     * arguments de AppApiService::resolveLocale()
     * @param Operation $operation
     * @return array{locale: string|null, acceptLanguage: string|null}
     */
    public static function getValues(Operation $operation): array
    {
        $parameters = $operation->getParameters();
        $locale = $parameters?->get('locale')?->getValue(null);
        $acceptLanguage = $parameters?->get(self::HEADER, HeaderParameter::class)?->getValue(null);
        return [
            'locale' => is_string($locale) && $locale !== '' ? $locale : null,
            'acceptLanguage' => is_string($acceptLanguage) ? $acceptLanguage : null,
        ];
    }
}
