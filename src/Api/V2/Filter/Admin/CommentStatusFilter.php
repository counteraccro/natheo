<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Filtre des commentaires d'administration par statut de modération, valeurs lues dans CommentStatus
 */

namespace App\Api\V2\Filter\Admin;

use ApiPlatform\Metadata\BackwardCompatibleFilterDescriptionTrait;
use ApiPlatform\Metadata\FilterInterface;
use ApiPlatform\Metadata\JsonSchemaFilterInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Parameter;
use App\Enum\Admin\Comment\CommentStatus;

final class CommentStatusFilter implements FilterInterface, JsonSchemaFilterInterface
{
    use BackwardCompatibleFilterDescriptionTrait;

    /**
     * Nom du paramètre déclaré sur les opérations
     * @var string
     */
    public const string PARAMETER = 'moderation';

    /**
     * @param Parameter $parameter
     * @return array<string, mixed>
     */
    public function getSchema(Parameter $parameter): array
    {
        return ['type' => 'string', 'enum' => CommentStatus::getSlugs()];
    }

    /**
     * Statut demandé, null sans filtre
     * @param Operation $operation
     * @return CommentStatus|null
     */
    public static function getValue(Operation $operation): ?CommentStatus
    {
        $value = $operation->getParameters()?->get(self::PARAMETER)?->getValue(null);
        return CommentStatus::tryFromSlug(is_string($value) ? $value : null);
    }
}
