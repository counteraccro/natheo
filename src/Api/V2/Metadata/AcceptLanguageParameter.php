<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * En-tête Accept-Language des ressources de contenu, utilisé quand « locale » est absent
 */

namespace App\Api\V2\Metadata;

use ApiPlatform\Metadata\HeaderParameter;

final class AcceptLanguageParameter extends HeaderParameter
{
    /**
     * Nom de l'en-tête
     * @var string
     */
    public const string KEY = 'Accept-Language';

    public function __construct()
    {
        parent::__construct(
            key: self::KEY,
            schema: ['type' => 'string'],
            description: 'Langues acceptées (ex : en-US,en;q=0.9), langue par défaut du site si aucune n\'est supportée',
        );
    }
}
