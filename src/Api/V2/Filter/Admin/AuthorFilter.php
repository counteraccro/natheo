<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Filtre des éléments d'une collection d'administration dont l'utilisateur de la session est l'auteur
 */

namespace App\Api\V2\Filter\Admin;

use ApiPlatform\Metadata\BackwardCompatibleFilterDescriptionTrait;
use ApiPlatform\Metadata\FilterInterface;
use ApiPlatform\Metadata\JsonSchemaFilterInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Parameter;

final class AuthorFilter implements FilterInterface, JsonSchemaFilterInterface
{
    use BackwardCompatibleFilterDescriptionTrait;

    /**
     * Nom du paramètre déclaré sur les opérations
     * @var string
     */
    public const string PARAMETER = 'author';

    /**
     * Valeur désignant l'utilisateur de la session
     * @var string
     */
    public const string ME = 'me';

    /**
     * @param Parameter $parameter
     * @return array<string, mixed>
     */
    public function getSchema(Parameter $parameter): array
    {
        return ['type' => 'string', 'enum' => [self::ME]];
    }

    /**
     * Vrai quand les éléments de l'utilisateur de la session sont demandés
     * @param Operation $operation
     * @return bool
     */
    public static function isMine(Operation $operation): bool
    {
        return $operation->getParameters()?->get(self::PARAMETER)?->getValue(null) === self::ME;
    }
}
