<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Service lier aux options système
 */

namespace App\Service\Admin;

use App\Entity\Admin\OptionSystem;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Yaml\Yaml;

class OptionSystemService extends AppAdminService
{
    const OPTION_SYSTEM_CONFIG_FILE = 'options_system.yaml';

    /**
     * Clé option nom du site
     * @var string
     */
    const OS_SITE_NAME = 'OS_SITE_NAME';

    /**
     * Clé option theme du site
     * @var string
     */
    const OS_THEME_SITE = 'OS_THEME_SITE';

    /**
     * Clé pour le logo du site
     */
    const OS_LOGO_SITE = 'OS_LOGO_SITE';

    /**
     * Clé option site ouvert
     * @var string
     */
    const OS_OPEN_SITE = 'OS_OPEN_SITE';

    /**
     * Clé option script header
     * @var string
     */
    const OS_FRONT_SCRIPT_TOP = 'OS_FRONT_SCRIPT_TOP';

    /**
     * Clé option script début body
     * @var string
     */
    const OS_FRONT_SCRIPT_START_BODY = 'OS_FRONT_SCRIPT_START_BODY';

    /**
     * Clé option script fin body
     * @var string
     */
    const OS_FRONT_SCRIPT_END_BODY = 'OS_FRONT_SCRIPT_END_BODY';

    /**
     * Clé option remplacement user delete
     * @var string
     */
    const OS_REPLACE_DELETE_USER = 'OS_REPLACE_DELETE_USER';

    /**
     * Clé option confirmation quitter form
     * @var string
     */
    const OS_CONFIRM_LEAVE_FORM = 'OS_CONFIRM_LEAVE_FORM';

    /**
     * Clé option authorisation suppression de données
     */
    const OS_ALLOW_DELETE_DATA = 'OS_ALLOW_DELETE_DATA';

    /**
     * Clé option langue par défaut pour le site
     */
    const OS_DEFAULT_LANGUAGE = 'OS_DEFAULT_LANGUAGE';

    /**
     * Clé option nb élements par défaut pour les users
     */
    const OS_NB_ELEMENT = 'OS_NB_ELEMENT';

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
     * Retourne le chemin du fichier yaml des options system
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getPathConfig(): string
    {
        $kernel = $this->containerBag->get('kernel.project_dir');
        return $kernel . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'cms' . DIRECTORY_SEPARATOR . self::OPTION_SYSTEM_CONFIG_FILE;
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
        }
        return $return;
    }

    /**
     * Permet de sauvegarder la valeur d'une clée
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
}