<?php

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Service lier aux options système
 */

namespace App\Service\Admin;

use App\Entity\Admin\OptionSystem;
use App\Utils\Options\OptionSystemKey;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Yaml\Yaml;

class OptionSystemService extends AppAdminService
{
    const OPTION_SYSTEM_CONFIG_FILE = 'options_system.yaml';


    /**
     * Retourne l'ensemble des options systèmes
     * @return array|object[]
     */
    public function getAll(): array
    {
        $optionServiceRepo = $this->getRepository(OptionSystem::class);
        return $optionServiceRepo->findAll();
    }

    /**
     * Retourne une option système en fonction de sa clé
     * @param string $key
     * @return object|null
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
     */
    public function getValueByKey(string $key): ?string
    {
        if ($this->getByKey($key) != null) {
            return $this->getByKey($key)->getValue();
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
        $kernel = $this->containerBag->get('kernel.project_dir');
        return $kernel . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'cms' . DIRECTORY_SEPARATOR .
            self::OPTION_SYSTEM_CONFIG_FILE;
    }

    /**
     * Retourne le fichier de config des options system sous la forme d'un tableau
     * @return array
     */
    public function getOptionsSystemConfig(): array
    {
        $return = [];
        try {
            $return = Yaml::parseFile($this->getPathConfig());
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            die($e->getMessage());
        }
        return $return;
    }

    /**
     * Permet de sauvegarder la valeur d'une clé
     * @param string $key
     * @param string $value
     * @return void
     */
    public function saveValueByKee(string $key, string $value): void
    {
        $optionServiceRepo = $this->entityManager->getRepository(OptionSystem::class);
        /* @var OptionSystem $optionSystem */
        $optionSystem = $this->getByKey($key);
        $optionSystem->setValue($value);
        $optionServiceRepo->save($optionSystem, true);
    }

    /**
     * Renvoi true ou false en fonction de l'option OS_MAIL_NOTIFICATION
     * @return bool
     */
    public function canSendMailNotification(): bool
    {
        if ($this->getValueByKey(OptionSystemKey::OS_MAIL_NOTIFICATION) == 1) {
            return true;
        }
        return false;
    }

    /**
     * Renvoi true ou false en fonction de l'option OS_ALLOW_DELETE_DATA
     * @return bool
     */
    public function canDelete(): bool
    {
        if ($this->getValueByKey(OptionSystemKey::OS_ALLOW_DELETE_DATA) == 1) {
            return true;
        }
        return false;
    }

    /**
     * Renvoi true ou false en fonction de l'option OS_REPLACE_DELETE_USER
     * @return bool
     */
    public function canReplace(): bool
    {
        if ($this->getValueByKey(OptionSystemKey::OS_REPLACE_DELETE_USER) == 1) {
            return true;
        }
        return false;
    }

    /**
     * Renvoi true ou false en fonction de l'option OS_NOTIFICATION
     * @return bool
     */
    public function canNotification(): bool
    {
        if ($this->getValueByKey(OptionSystemKey::OS_NOTIFICATION) == 1) {
            return true;
        }
        return false;
    }

}
