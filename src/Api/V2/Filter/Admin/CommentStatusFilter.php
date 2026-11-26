<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Filtre des commentaires d'administration par statut de modération, valeurs lues dans CommentStatus
 */

namespace App\Api\V2\Filter\Admin;

use ApiPlatform\Metadata\Operation;
use App\Api\V2\Filter\AbstractEnumSlugFilter;
use App\Enum\Admin\Comment\CommentStatus;

final class CommentStatusFilter extends AbstractEnumSlugFilter
{
    /**
     * Nom du paramètre déclaré sur les opérations
     * @var string
     */
    public const string PARAMETER = 'moderation';

    /**
     * Statut demandé, null sans filtre
     * @param Operation $operation
     * @return CommentStatus|null
     */
    public static function getValue(Operation $operation): ?CommentStatus
    {
        return CommentStatus::tryFromSlug(self::getSlug($operation));
    }

    /**
     * @return class-string<CommentStatus>
     */
    protected static function getEnum(): string
    {
        return CommentStatus::class;
    }

    /**
     * @return string
     */
    protected static function getParameterName(): string
    {
        return self::PARAMETER;
    }
}
