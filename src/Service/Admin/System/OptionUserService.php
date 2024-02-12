<?php

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Service lier aux options user
 */

namespace App\Service\Admin\System;

use App\Entity\Admin\System\OptionSystem;
use App\Entity\Admin\System\OptionUser;
use App\Entity\Admin\System\User;
use App\Service\Admin\AppAdminService;
use App\Utils\System\Options\OptionSystemKey;
use App\Utils\System\Options\OptionUserKey;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\Translation\TranslatorInterface;

class OptionUserService extends AppAdminService
{

    /**
     * Nom du fichier de config
     */
    const OPTION_USER_CONFIG_FILE = 'options_user.yaml';

    const KEY_SESSION_TAB_OPTIONS = 'users_options';

    /**
     * @var OptionSystemService
     */
    private OptionSystemService $optionSystemService;

    /**
     * @param ContainerInterface $handlers
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        #[AutowireLocator([
            'logger' => LoggerInterface::class,
            'entityManager' => EntityManagerInterface::class,
            'containerBag' => ContainerBagInterface::class,
            'translator' => TranslatorInterface::class,
            'router' => UrlGeneratorInterface::class,
            'security' => Security::class,
            'requestStack' => RequestStack::class,
            'parameterBag' => ParameterBagInterface::class,
            'optionSystemService' => OptionSystemService::class,
        ])]
        private readonly ContainerInterface $handlers
    )
    {
        $this->optionSystemService = $this->handlers->get('optionSystemService');
        parent::__construct($handlers);
    }

    /**
     * Permet de créer les options avec les valeurs par défaut pour le nouveau user
     * @param User $user
     * @return User
     */
    public function createOptionsUser(User $user): User
    {
        $options = [
            OptionSystemKey::OS_THEME_SITE => OptionUserKey::OU_THEME_SITE,
            OptionSystemKey::OS_DEFAULT_LANGUAGE => OptionUserKey::OU_DEFAULT_LANGUAGE,
            OptionSystemKey::OS_NB_ELEMENT => OptionUserKey::OU_NB_ELEMENT
        ];

        foreach ($options as $optionSystemKey => $optionUserKey) {
            /** @var OptionSystem $option */
            $option = $this->optionSystemService->getByKey($optionSystemKey);
            $optionUser = new OptionUser();
            $optionUser->setKey($optionUserKey)->setValue($option->getValue());
            $user->addOptionsUser($optionUser);
        }

        $options = [
            OptionUserKey::OU_DEFAULT_PERSONAL_DATA_RENDER => 'email'
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
     */
    public function getByKey(string $key): ?object
    {
        // Si pas de user, on cherche l'option système par défaut
        if ($this->security->getUser() === null) {
            $key = str_replace('OU_', 'OS_', $key);
            return $this->optionSystemService->getByKey($key);
        }

        $repo = $this->getRepository(OptionUser::class);
        return $repo->findOneBy(['key' => $key, 'user' => $this->security->getUser()->getId()]);
    }

    /**
     * Retourne une valeur option user en fonction de sa clé
     * @param string $key
     * @param bool $session
     * @return string
     */
    public function getValueByKey(string $key, bool $session = true): string
    {
        if ($session) {
            // Priorité à la valeur en session
            $tabOptions = $this->requestStack->getSession()->get(self::KEY_SESSION_TAB_OPTIONS, []);
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
        if ($this->requestStack->getCurrentRequest() != null) {
            $tabOptions[$key] = $value;
            $this->requestStack->getSession()->set(self::KEY_SESSION_TAB_OPTIONS, $tabOptions);
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
        $kernel = $this->containerBag->get('kernel.project_dir');
        return $kernel . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'cms' . DIRECTORY_SEPARATOR .
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
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
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
        $repo = $this->getRepository(OptionUser::class);
        return $repo->findBy(['user' => $this->security->getUser()->getId()]);
    }

    /**
     * Permet de sauvegarder la valeur d'une clée
     * @param string $key
     * @param string $value
     * @return void
     */
    public function saveValueByKee(string $key, string $value): void
    {
        $repo = $this->entityManager->getRepository(OptionUser::class);
        /* @var OptionUser $optionUser */
        $optionUser = $this->getByKey($key);
        $optionUser->setValue($value);
        $repo->save($optionUser, true);

        // Mise à jour de la session avec les options sauvegardées
        $tabOptions = $this->requestStack->getSession()->get(self::KEY_SESSION_TAB_OPTIONS, []);
        $tabOptions[$key] = $value;
        $this->requestStack->getSession()->set(self::KEY_SESSION_TAB_OPTIONS, $tabOptions);
    }
}
