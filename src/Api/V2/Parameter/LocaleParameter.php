<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Paramètre de requête « locale » des ressources de contenu de l'API v2 (langues de app.supported_locales)
 */

namespace App\Api\V2\Parameter;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\QueryParameter;

final class LocaleParameter extends QueryParameter
{
    public function __construct()
    {
        // Langues acceptées ajoutées par LocaleParameterMetadataFactory
        parent::__construct(
            schema: ['type' => 'string'],
            description: 'Langue du contenu, langue par défaut du site si absente',
        );
    }

    /**
     * Retourne la langue demandée sur l'opération, null si absente
     * @param Operation $operation
     * @return string|null
     */
    public static function getValueFrom(Operation $operation): ?string
    {
        $value = $operation->getParameters()?->get('locale', self::class)?->getValue(null);
        return is_string($value) && $value !== '' ? $value : null;
    }
}
