<?php

declare(strict_types=1);
/**
 * Actions courantes du journal des actions du back-office
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Enum\Admin\System\ActionLog;

enum ActionLogAction: string
{
    case CREATE = 'create';

    case UPDATE = 'update';

    case DELETE = 'delete';

    case DISABLE = 'disable';

    case ENABLE = 'enable';

    /**
     * Action déduite de la méthode HTTP d'une écriture
     * @param string $method
     * @return self
     */
    public static function fromMethod(string $method): self
    {
        return match (strtoupper($method)) {
            'POST' => self::CREATE,
            'DELETE' => self::DELETE,
            default => self::UPDATE,
        };
    }
}
