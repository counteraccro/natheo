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
        $config = $this->getOptionSystemService()->getOptionsSystemConfig();

        $return = [];
        foreach ($this->getAllOptionSystemWithValue() as $option => $value) {
            $return[] = $this->toPublicOption($option, $value, $config);
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
        $option = self::KEY_PREFIX . strtoupper($key);
        if ($key !== strtolower($key) || !in_array($option, $this->getWhiteListeOptionSystem(), true)) {
            return null;
        }

        $optionSystemService = $this->getOptionSystemService();
        return $this->toPublicOption(
            $option,
            $optionSystemService->getValueByKey($option),
            $optionSystemService->getOptionsSystemConfig(),
        );
    }

    /**
     * Clé et valeur d'une option au format de l'API v2, booléen pour les options de type boolean
     * @param string $option clé de l'option système (OS_SITE_NAME)
     * @param string|null $value
     * @param array $config configuration des options système
     * @return array{key: string, value: bool|string|null}
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function toPublicOption(string $option, ?string $value, array $config): array
    {
        $type = $this->getOptionConfigService()->findByKey($config, $option)['type'] ?? null;
        return [
            'key' => strtolower(substr($option, strlen(self::KEY_PREFIX))),
            'value' => $type === 'boolean' && $value !== null ? $value === '1' : $value,
        ];
    }
}
