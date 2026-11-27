<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.2
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
        return $this->flatten($config)[$key] ?? null;
    }

    /**
     * Configuration de chaque option indexée par sa clé, dans l'ordre du fichier, sans les catégories
     * @param array $config fichier de config lu
     * @return array<string, array>
     */
    public function flatten(array $config): array
    {
        $options = [];
        foreach (reset($config) ?: [] as $category) {
            $options += $category['options'] ?? [];
        }
        return $options;
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
     * Vérifie qu'une option a une valeur par langue (clé translatable de la config)
     * @param array|null $optionConfig
     * @return bool
     */
    public function isTranslatable(?array $optionConfig): bool
    {
        return !empty($optionConfig['translatable']);
    }

    /**
     * Valeurs d'une option de type liste (« valeur:libellé|… »), indexées par valeur ; libellé vide s'il manque
     * @param array $optionConfig
     * @return array<string, string>
     */
    public function getListValues(array $optionConfig): array
    {
        $values = [];
        if (!isset($optionConfig['list_value']) || $optionConfig['list_value'] === '') {
            return $values;
        }
        foreach (explode('|', (string) $optionConfig['list_value']) as $item) {
            [$value, $label] = array_pad(explode(':', $item, 2), 2, '');
            $values[$value] = $label;
        }
        return $values;
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
            // Valeur posée par sa propre action, jamais saisie
            case 'action':
                return false;
            case 'boolean':
                return in_array($value, ['0', '1'], true);
            case 'select':
                return array_key_exists($value, $this->getListValues($optionConfig));
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
