<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Paramètre de requête « position » des menus de l'API v2, valeurs issues de MenuPosition
 */

namespace App\Api\V2\Parameter;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\QueryParameter;
use App\Enum\Admin\Content\Menu\MenuPosition;

final class MenuPositionParameter extends QueryParameter
{
    public function __construct()
    {
        parent::__construct(
            schema: [
                'type' => 'string',
                'enum' => array_map(fn(MenuPosition $position) => $position->getApiName(), MenuPosition::cases()),
            ],
            description: 'Position du menu',
        );
    }

    /**
     * Retourne la position demandée sur l'opération, null si absente
     * @param Operation $operation
     * @return MenuPosition|null
     */
    public static function getValueFrom(Operation $operation): ?MenuPosition
    {
        $value = $operation->getParameters()?->get('position', self::class)?->getValue(null);
        foreach (MenuPosition::cases() as $position) {
            if ($position->getApiName() === $value) {
                return $position;
            }
        }
        return null;
    }
}
