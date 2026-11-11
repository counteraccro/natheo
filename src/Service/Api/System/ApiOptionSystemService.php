<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Service ApiOptionSystem
 */

namespace App\Service\Api\System;

use App\Enum\Admin\System\Options\OptionSystem;
use App\Service\Api\AppApiService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ApiOptionSystemService extends AppApiService
{
    /**
     * Préfixe des clés d'options système retiré dans l'API v2
     * @var string
     */
    private const string KEY_PREFIX = 'OS_';

    /**
     * Retourne un tableau de clé d'options system autorisée pour l'API
     * @return array
     */
    public function getWhiteListeOptionSystem(): array
    {
        return [
            OptionSystem::OS_SITE_NAME->value,
            OptionSystem::OS_FRONT_SCRIPT_TOP->value,
            OptionSystem::OS_FRONT_SCRIPT_START_BODY->value,
            OptionSystem::OS_FRONT_SCRIPT_END_BODY->value,
            OptionSystem::OS_OPEN_COMMENT->value,
            OptionSystem::OS_ADRESSE_SITE->value,
            OptionSystem::OS_THEME_FRONT_SITE->value,
            OptionSystem::OS_FRONT_FOOTER_TEXTE->value,
            OptionSystem::OS_FRONT_FOOTER_SOCIAL_FACEBOOK_URL->value,
            OptionSystem::OS_FRONT_FOOTER_SOCIAL_INSTAGRAM_URL->value,
            OptionSystem::OS_FRONT_FOOTER_SOCIAL_GITHUB_URL->value,
            OptionSystem::OS_FRONT_FOOTER_SOCIAL_TIKTOK_URL->value,
            OptionSystem::OS_FRONT_FOOTER_SOCIAL_LINKEDIN_URL->value,
            OptionSystem::OS_FRONT_FOOTER_SOCIAL_YOUTUBE_URL->value,
            OptionSystem::OS_FRONT_FOOTER_SOCIAL_X_URL->value,
            OptionSystem::OS_FRONT_ROBOT_NO_INDEX->value,
            OptionSystem::OS_FRONT_ROBOT_NO_FOLLOW->value,
        ];
    }

    /**
     * Retourne la liste des options system avec la value
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getAllOptionSystemWithValue(): array
    {
        $optionSystemService = $this->getOptionSystemService();

        $return = [];
        foreach ($this->getWhiteListeOptionSystem() as $option) {
            $return[$option] = $optionSystemService->getValueByKey($option);
        }

        return $return;
    }

    /**
     * Options publiques de l'API v2, clés en minuscules sans préfixe et valeurs typées
     * @return list<array{key: string, value: bool|string|null}>
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getPublicOptions(): array
    {
        $types = $this->getOptionTypes();
        $optionSystemService = $this->getOptionSystemService();

        $return = [];
        foreach ($this->getWhiteListeOptionSystem() as $option) {
            $value = $optionSystemService->getValueByKey($option);
            $return[] = [
                'key' => strtolower(substr($option, strlen(self::KEY_PREFIX))),
                'value' => ($types[$option] ?? null) === 'boolean' && $value !== null ? $value === '1' : $value,
            ];
        }
        return $return;
    }

    /**
     * Option publique de l'API v2 par sa clé, null si elle n'est pas exposée
     * @param string $key clé en minuscules sans préfixe (site_name)
     * @return array{key: string, value: bool|string|null}|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function findPublicOption(string $key): ?array
    {
        foreach ($this->getPublicOptions() as $option) {
            if ($option['key'] === $key) {
                return $option;
            }
        }
        return null;
    }

    /**
     * Type de chaque option système déclaré dans la configuration, indexé par clé
     * @return array<string, string>
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getOptionTypes(): array
    {
        $types = [];
        foreach ($this->getOptionSystemService()->getOptionsSystemConfig()['options_system'] ?? [] as $category) {
            foreach ($category['options'] ?? [] as $key => $config) {
                $types[$key] = (string) ($config['type'] ?? '');
            }
        }
        return $types;
    }
}
