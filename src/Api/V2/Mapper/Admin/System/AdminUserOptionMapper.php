<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Convertit une option de options_user.yaml et la valeur de l'utilisateur en ressource d'administration
 */

namespace App\Api\V2\Mapper\Admin\System;

use App\Api\V2\Output\Admin\Global\Permissions;
use App\Api\V2\Output\Admin\System\AdminOptionChoice;
use App\Api\V2\Resource\Admin\System\AdminUserOption;

final readonly class AdminUserOptionMapper
{
    /**
     * @param string $key clé de l'option (OU_ADMIN_THEME)
     * @param array $config configuration de l'option
     * @param string $value valeur de l'utilisateur
     * @param array{label: string, help: string|null, choices: array<string, string>} $labels libellés traduits
     * @return AdminUserOption
     */
    public function map(string $key, array $config, string $value, array $labels): AdminUserOption
    {
        $resource = new AdminUserOption();
        $resource->id = strtolower($key);
        $resource->label = $labels['label'];
        $resource->help = $labels['help'];
        $resource->value = $value;
        $resource->choices = array_map(
            fn(string|int $choice, string $label) => new AdminOptionChoice((string) $choice, $label),
            array_keys($labels['choices']),
            $labels['choices'],
        );
        $resource->permissions = new Permissions(empty($config['disabled']), false, false);
        return $resource;
    }
}
