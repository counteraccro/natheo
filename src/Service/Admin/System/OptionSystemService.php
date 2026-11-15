<?php

declare(strict_types=1);

/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Service lier aux options système
 */

namespace App\Service\Admin\System;

use App\Enum\Admin\System\Options\OptionSystem as OptionSystemEnum;
use App\Entity\Admin\System\OptionSystem;
use App\Service\Admin\AppAdminService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Yaml\Yaml;

class OptionSystemService extends AppAdminService
{
    const OPTION_SYSTEM_CONFIG_FILE = 'options_system.yaml';

    /**
     * Configuration des options système, lue une seule fois
     * @var array|null
     */
    private ?array $optionsSystemConfig = null;

    /**
     * Retourne l'ensemble des options systèmes
     * @return array|object[]
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getAll(): array
    {
        $optionServiceRepo = $this->getRepository(OptionSystem::class);
        return $optionServiceRepo->findAll();
    }

    /**
     * Retourne une option système en fonction de sa clé
     * @param string $key
     * @return OptionSystem|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getByKey(string $key): ?object
    {
        $optionServiceRepo = $this->getRepository(OptionSystem::class);
        return $optionServiceRepo->findOneBy(['key' => $key]);
    }

    /**
     * Retourne la valeur d'une option système en fonction de sa clé
     * @param string $key
     * @return string|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getValueByKey(string $key): ?string
    {
        $result = $this->getByKey($key);
        if ($result != null) {
            return $result->getValue();
        }
        return null;
    }

    /**
     * Retourne le chemin du fichier yaml des options system
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getPathConfig(): string
    {
        $containerBag = $this->getContainerBag();

        $kernel = $containerBag->get('kernel.project_dir');
        return $kernel .
            DIRECTORY_SEPARATOR .
            'config' .
            DIRECTORY_SEPARATOR .
            'cms' .
            DIRECTORY_SEPARATOR .
            self::OPTION_SYSTEM_CONFIG_FILE;
    }

    /**
     * Retourne le fichier de config des options system sous la forme d'un tableau
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getOptionsSystemConfig(): array
    {
        return $this->optionsSystemConfig ??= Yaml::parseFile($this->getPathConfig());
    }

    /**
     * Retourne la configuration d'une option système en fonction de sa clé, null si elle n'est pas dans la config
     * @param string $key
     * @return array|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getOptionConfigByKey(string $key): ?array
    {
        return $this->getOptionConfigService()->findByKey($this->getOptionsSystemConfig(), $key);
    }

    /**
     * Met à jour une option depuis l'interface d'administration.
     * Refuse les clés absentes de la config, les options désactivées et les valeurs invalides.
     * Une option traduisible demande une langue du site : seule la valeur de cette langue est remplacée
     * @param string $key
     * @param string $value
     * @param string|null $locale langue de la valeur, pour une option traduisible uniquement
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function updateValueFromAdmin(string $key, string $value, ?string $locale = null): bool
    {
        $optionConfigService = $this->getOptionConfigService();
        $optionConfig = $this->getOptionConfigByKey($key);
        if (
            !$optionConfigService->isEditable($optionConfig) ||
            $optionConfigService->isTranslatable($optionConfig) !== ($locale !== null)
        ) {
            return false;
        }

        if ($locale === null) {
            if (!$optionConfigService->isValidValue($optionConfig, $value)) {
                return false;
            }
            $this->saveValueByKee($key, $value);
            return true;
        }

        if (!in_array($locale, $this->getSupportedLocales(), true)) {
            return false;
        }
        // Seule la langue par défaut est obligatoire, les autres reprennent sa valeur
        if ($locale !== $this->getDefaultLanguage()) {
            unset($optionConfig['required']);
        }
        if (!$optionConfigService->isValidValue($optionConfig, $value)) {
            return false;
        }

        $translations = $this->decodeTranslations($this->getValueByKey($key));
        $translations[$locale] = $value;
        $this->saveValueByKee($key, json_encode($translations, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
        return true;
    }

    /**
     * Valeur d'une option dans une langue. Pour une option traduisible, une langue vide reprend la valeur
     * de la langue par défaut, puis la première valeur renseignée ; une option non traduisible renvoie sa valeur telle quelle
     * @param string $key
     * @param string|null $locale langue voulue, langue par défaut du site si null
     * @return string|null null si l'option n'est pas en base
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getTranslatedValueByKey(string $key, ?string $locale = null): ?string
    {
        $value = $this->getValueByKey($key);
        if ($value === null || !$this->getOptionConfigService()->isTranslatable($this->getOptionConfigByKey($key))) {
            return $value;
        }

        $translations = $this->decodeTranslations($value);
        $default = $this->getDefaultLanguage();
        $candidates = [$translations[$locale ?? $default] ?? '', $translations[$default] ?? '', ...$translations];
        foreach ($candidates as $candidate) {
            if ($candidate !== '') {
                return $candidate;
            }
        }
        return '';
    }

    /**
     * Valeurs par langue d'une option traduisible, stockées en objet JSON.
     * Une valeur qui n'est pas un objet JSON (enregistrée avant que l'option soit traduisible) est celle de la langue par défaut
     * @param string|null $value
     * @return array<string, string>
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function decodeTranslations(?string $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        $decoded = json_decode($value, true);
        if (is_array($decoded) && $decoded !== [] && !array_is_list($decoded)) {
            return array_filter($decoded, 'is_string');
        }
        return [$this->getDefaultLanguage() => $value];
    }

    /**
     * Langue par défaut du site (option OS_DEFAULT_LANGUAGE)
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getDefaultLanguage(): string
    {
        return $this->getValueByKey(OptionSystemEnum::OS_DEFAULT_LANGUAGE->value) ?:
            OptionSystemEnum::OS_DEFAULT_LANGUAGE->getDefault() ?? 'fr';
    }

    /**
     * Permet de sauvegarder la valeur d'une clé.
     * Si l'option est déclarée dans la config mais absente en base (ajoutée après l'installation), elle est créée
     * @param string $key
     * @param string $value
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function saveValueByKee(string $key, string $value): void
    {
        /* @var OptionSystem $optionSystem */
        $optionSystem = $this->getByKey($key);
        if ($optionSystem === null) {
            if ($this->getOptionConfigByKey($key) === null) {
                return;
            }
            $optionSystem = new OptionSystem();
            $optionSystem->setKey($key);
        }
        $optionSystem->setValue($value);
        $this->save($optionSystem);
    }

    /**
     * Redemande le consentement aux scripts du site public : les choix faits avant maintenant ne valent plus
     * @return \DateTimeImmutable date enregistrée
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function resetFrontConsent(): \DateTimeImmutable
    {
        $date = new \DateTimeImmutable();
        $this->saveValueByKee(OptionSystemEnum::OS_FRONT_CONSENT_RESET_AT->value, $date->format(DATE_ATOM));
        return $date;
    }

    /**
     * Renvoi true ou false en fonction de l'option OS_MAIL_NOTIFICATION
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function canSendMailNotification(): bool
    {
        if ($this->getValueByKey(OptionSystemEnum::OS_MAIL_NOTIFICATION->value) == 1) {
            return true;
        }
        return false;
    }

    /**
     * Renvoi true ou false en fonction de l'option OS_ALLOW_DELETE_DATA
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function canDelete(): bool
    {
        if ($this->getValueByKey(OptionSystemEnum::OS_ALLOW_DELETE_DATA->value) == 1) {
            return true;
        }
        return false;
    }

    /**
     * Renvoi true ou false en fonction de l'option OS_REPLACE_DELETE_USER
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function canReplace(): bool
    {
        if ($this->getValueByKey(OptionSystemEnum::OS_REPLACE_DELETE_USER->value) == 1) {
            return true;
        }
        return false;
    }

    /**
     * Renvoi true ou false en fonction de l'option OS_NOTIFICATION
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function canNotification(): bool
    {
        if ($this->getValueByKey(OptionSystemEnum::OS_NOTIFICATION->value) == 1) {
            return true;
        }
        return false;
    }

    /**
     * Renvoi true si l'option OS_PAGE_VALIDATION est active
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function isPageValidationActive(): bool
    {
        return $this->getValueByKey(OptionSystemEnum::OS_PAGE_VALIDATION->value) == 1;
    }
}
