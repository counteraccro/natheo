<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Paramètre « sql » des collections d'administration : ajoute à la réponse la requête SQL des éléments de la page
 */

namespace App\Api\V2\Metadata;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\QueryParameter;

final class SqlParameter extends QueryParameter
{
    /**
     * Nom du paramètre
     * @var string
     */
    public const string KEY = 'sql';

    /**
     * Rôle exigé quand le paramètre est envoyé
     * @var string
     */
    public const string ROLE = 'ROLE_SUPER_ADMIN';

    public function __construct()
    {
        parent::__construct(
            key: self::KEY,
            schema: ['type' => 'boolean'],
            description: 'Ajoute « sql », la requête exécutable des éléments de la page (super administrateur)',
            castToNativeType: true,
        );
    }

    /**
     * Vrai quand l'opération demande la requête SQL
     * @param Operation $operation
     * @return bool
     */
    public static function isRequested(Operation $operation): bool
    {
        return filter_var(
            $operation->getParameters()?->get(self::KEY, self::class)?->getValue(false),
            FILTER_VALIDATE_BOOLEAN,
        );
    }
}
