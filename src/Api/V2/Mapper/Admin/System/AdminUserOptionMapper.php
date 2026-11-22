<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Convertit une option de options_user.yaml et la valeur de l'utilisateur en ressource d'administration
 */

namespace App\Api\V2\Mapper\Admin\System;

use App\Api\V2\Output\Admin\Global\Permissions;
use App\Api\V2\Output\Admin\System\AdminOptionChoice;
use App\Api\V2\Resource\Admin\System\AdminUserOption;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class AdminUserOptionMapper
{
    public function __construct(private TranslatorInterface $translator) {}

    /**
     * @param string $key clé de l'option (OU_ADMIN_THEME)
     * @param array $config configuration de l'option
     * @param string $value valeur de l'utilisateur
     * @return AdminUserOption
     */
    public function map(string $key, array $config, string $value): AdminUserOption
    {
        $resource = new AdminUserOption();
        $resource->id = strtolower($key);
        $resource->label = $this->translator->trans((string) ($config['label'] ?? $key));
        $resource->help = isset($config['help']) ? $this->translator->trans((string) $config['help']) : null;
        $resource->value = $value;
        $resource->choices = $this->mapChoices($config);
        $resource->permissions = new Permissions(empty($config['disabled']), false, false);
        return $resource;
    }

    /**
     * Valeurs d'une option à liste (« valeur:clé de traduction|… »), vide pour les autres types
     * @param array $config
     * @return AdminOptionChoice[]
     */
    private function mapChoices(array $config): array
    {
        if (($config['type'] ?? null) !== 'select' || !isset($config['list_value'])) {
            return [];
        }
        $choices = [];
        foreach (explode('|', (string) $config['list_value']) as $item) {
            [$value, $label] = array_pad(explode(':', $item, 2), 2, '');
            $choices[] = new AdminOptionChoice($value, $this->translator->trans($label));
        }
        return $choices;
    }
}
