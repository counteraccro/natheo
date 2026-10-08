<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Service de lecture et validation des fichiers de config des options (options_system.yaml, options_user.yaml)
 */

namespace App\Service\Admin\System;

use App\Service\Admin\AppAdminService;

class OptionConfigService extends AppAdminService
{
    /**
     * Retourne la configuration d'une option en fonction de sa clé, null si elle n'est pas dans la config
     * @param array $config
     * @param string $key
     * @return array|null
     */
    public function findByKey(array $config, string $key): ?array
    {
        foreach (reset($config) ?: [] as $category) {
            if (isset($category['options'][$key])) {
                return $category['options'][$key];
            }
        }
        return null;
    }

    /**
     * Vérifie qu'une option peut être modifiée (présente dans la config et non désactivée)
     * @param array|null $optionConfig
     * @return bool
     */
    public function isEditable(?array $optionConfig): bool
    {
        return $optionConfig !== null && empty($optionConfig['disabled']);
    }

    /**
     * Vérifie qu'une valeur respecte la configuration de l'option (type, required, validation)
     * @param array $optionConfig
     * @param string $value
     * @return bool
     */
    public function isValidValue(array $optionConfig, string $value): bool
    {
        if (!empty($optionConfig['required']) && trim($value) === '') {
            return false;
        }

        switch ($optionConfig['type']) {
            case 'boolean':
                return in_array($value, ['0', '1'], true);
            case 'select':
                $allowedValues = array_map(
                    fn(string $item) => explode(':', $item)[0],
                    explode('|', $optionConfig['list_value']),
                );
                return in_array($value, $allowedValues, true);
        }

        if ($value === '' || !isset($optionConfig['validation'])) {
            return true;
        }

        return match ($optionConfig['validation']) {
            'email' => filter_var($value, FILTER_VALIDATE_EMAIL) !== false,
            'url' => filter_var($value, FILTER_VALIDATE_URL) !== false &&
                in_array(parse_url($value, PHP_URL_SCHEME), ['http', 'https'], true),
            default => true,
        };
    }
}
