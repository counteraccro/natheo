<?php

/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Service lier aux options user
 */

namespace App\Service\Admin\System;

use App\Entity\Admin\System\OptionSystem;
use App\Entity\Admin\System\OptionUser;
use App\Entity\Admin\System\User;
use App\Service\Admin\AppAdminService;
use App\Utils\System\Options\OptionSystemKey;
use App\Utils\System\Options\OptionUserKey;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Yaml\Yaml;

class OptionUserService extends AppAdminService
{
    /**
     * Nom du fichier de config
     */
    const OPTION_USER_CONFIG_FILE = 'options_user.yaml';

    const KEY_SESSION_TAB_OPTIONS = 'users_options';

    /**
     * Permet de créer les options avec les valeurs par défaut pour le nouveau user
     * @param User $user
     * @return User
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function createOptionsUser(User $user): User
    {
        $optionSystemService = $this->getOptionSystemService();

        $options = [
            OptionSystemKey::OS_DEFAULT_LANGUAGE => OptionUserKey::OU_DEFAULT_LANGUAGE,
            OptionSystemKey::OS_NB_ELEMENT => OptionUserKey::OU_NB_ELEMENT,
        ];

        foreach ($options as $optionSystemKey => $optionUserKey) {
            /** @var OptionSystem $option */
            $option = $optionSystemService->getByKey($optionSystemKey);
            $optionUser = new OptionUser();
            $optionUser->setKey($optionUserKey)->setValue($option->getValue());
            $user->addOptionsUser($optionUser);
        }

        $options = [
            OptionUserKey::OU_DEFAULT_PERSONAL_DATA_RENDER => 'email',
        ];
        foreach ($options as $key => $value) {
            $optionUser = new OptionUser();
            $optionUser->setKey($key)->setValue($value);
            $user->addOptionsUser($optionUser);
        }
        return $user;
    }

    /**
     * Retourne une option user en fonction de sa clé
     * Si le user n'existe pas, retourne l'équivalent en option système
     * @param string $key
     * @return object|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getByKey(string $key): ?object
    {
        $optionSystemService = $this->getOptionSystemService();
        $security = $this->getSecurity();

        // Si pas de user, on cherche l'option système par défaut
        if ($security->getUser() === null) {
            $key = str_replace('OU_', 'OS_', $key);
            return $optionSystemService->getByKey($key);
        }

        $repo = $this->getRepository(OptionUser::class);
        return $repo->findOneBy(['key' => $key, 'user' => $security->getUser()->getId()]);
    }

    /**
     * Retourne une valeur option user en fonction de sa clé
     * @param string $key
     * @param bool $session
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getValueByKey(string $key, bool $session = true): string
    {
        $requestStack = $this->getRequestStack();

        if ($session) {
            // Priorité à la valeur en session
            $tabOptions = $requestStack->getSession()->get(self::KEY_SESSION_TAB_OPTIONS, []);
            if (isset($tabOptions[$key])) {
                return $tabOptions[$key];
            }
        }

        $obj = $this->getByKey($key);
        if ($obj != null) {
            $value = $obj->getValue();
        } else {
            // Cas très paticulier, dans le cas de la sauvegarde d'une notification quand le user s'auto delete ou
            // s'auto anonymise
            return 'fr';
        }

        // Mise à jour de la session avec les options sauvegardées
        if ($requestStack->getCurrentRequest() != null) {
            $tabOptions[$key] = $value;
            $requestStack->getSession()->set(self::KEY_SESSION_TAB_OPTIONS, $tabOptions);
        }
        return $value;
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
            self::OPTION_USER_CONFIG_FILE;
    }

    /**
     * Retourne le fichier de config des options system sous la forme d'un tableau
     * @return array
     */
    public function getOptionsUserConfig(): array
    {
        $return = [];
        try {
            $return = Yaml::parseFile($this->getPathConfig());
        } catch (NotFoundExceptionInterface | ContainerExceptionInterface $e) {
            die($e->getMessage());
        }
        return $return;
    }

    /**
     * Retourne l'ensemble des options users du user courant
     * @return array|object[]
     */
    public function getAll(): array
    {
        $security = $this->getSecurity();
        $repo = $this->getRepository(OptionUser::class);
        return $repo->findBy(['user' => $security->getUser()->getId()]);
    }

    /**
     * Permet de sauvegarder la valeur d'une clée
     * @param string $key
     * @param string $value
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function saveValueByKee(string $key, string $value): void
    {
        $requestStack = $this->getRequestStack();

        $repo = $this->getRepository(OptionUser::class);
        /* @var OptionUser $optionUser */
        $optionUser = $this->getByKey($key);
        $optionUser->setValue($value);
        $repo->save($optionUser, true);

        // Mise à jour de la session avec les options sauvegardées
        $tabOptions = $requestStack->getSession()->get(self::KEY_SESSION_TAB_OPTIONS, []);
        $tabOptions[$key] = $value;
        $requestStack->getSession()->set(self::KEY_SESSION_TAB_OPTIONS, $tabOptions);
    }
}
