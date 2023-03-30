<?php

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Service lier aux options user
 */

namespace App\Service\Admin;

use App\Entity\Admin\OptionSystem;
use App\Entity\Admin\OptionUser;
use App\Entity\Admin\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\Translation\TranslatorInterface;

class OptionUserService extends AppAdminService
{
    /**
     * Clé option langue pour le user
     * @var string
     */
    const OU_DEFAULT_LANGUAGE = 'OU_DEFAULT_LANGUAGE';

    /**
     * Clé option theme du site pour le user
     * @var string
     */
    const OU_THEME_SITE = 'OU_THEME_SITE';

    /**
     * Clé option nb élements pour les users
     */
    const OU_NB_ELEMENT = 'OU_NB_ELEMENT';

    /**
     * Nom du fichier de config
     */
    const OPTION_USER_CONFIG_FILE = 'options_user.yaml';

    const KEY_SESSION_TAB_OPTIONS = 'users_options';

    /**
     * @var OptionSystemService
     */
    private OptionSystemService $optionSystemService;

    public function __construct(EntityManagerInterface $entityManager, ContainerBagInterface $containerBag,
                                TranslatorInterface    $translator, UrlGeneratorInterface $router,
                                OptionSystemService    $optionSystemService, Security $security,
                                RequestStack           $requestStack
    )
    {
        $this->optionSystemService = $optionSystemService;
        parent::__construct($entityManager, $containerBag, $translator, $router, $security, $requestStack);
    }

    /**
     * Permet de créer les options avec les valeurs par défaut pour le nouveau user
     * @param User $user
     * @return User
     */
    public function createOptionsUser(User $user): User
    {
        $options = [
            $this->optionSystemService::OS_THEME_SITE => self::OU_THEME_SITE,
            $this->optionSystemService::OS_DEFAULT_LANGUAGE => self::OU_DEFAULT_LANGUAGE,
            $this->optionSystemService::OS_NB_ELEMENT => self::OU_NB_ELEMENT
        ];

        foreach ($options as $optionSystemKey => $optionUserKey) {
            /** @var OptionSystem $option */
            $option = $this->optionSystemService->getByKey($optionSystemKey);
            $optionUser = new OptionUser();
            $optionUser->setKey($optionUserKey)->setValue($option->getValue());
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
        // Si pas de user, on chercher l'option système par défaut
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
     * @param bool $no_session
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


        $value = $this->getByKey($key)->getValue();

        // Mise à jour de la session avec les options sauvegardées
        $tabOptions[$key] = $value;
        $this->requestStack->getSession()->set(self::KEY_SESSION_TAB_OPTIONS, $tabOptions);
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
