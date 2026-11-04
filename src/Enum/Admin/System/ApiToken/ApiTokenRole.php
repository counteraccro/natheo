<?php

declare(strict_types=1);
/**
 * Rôles des tokens API
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Enum\Admin\System\ApiToken;

enum ApiTokenRole: string
{
    case READ = 'ROLE_READ_API';

    case WRITE = 'ROLE_WRITE_API';

    case ADMIN = 'ROLE_ADMIN_API';

    /**
     * Retourne les rôles sous la forme [rôle => rôle]
     * @return array<string, string>
     */
    public static function choices(): array
    {
        $values = array_column(self::cases(), 'value');
        return array_combine($values, $values);
    }
}
