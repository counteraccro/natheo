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
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
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
     * @var OptionSystemService
     */
    private OptionSystemService $optionSystemService;

    public function __construct(EntityManagerInterface $entityManager, ContainerBagInterface $containerBag,
                                TranslatorInterface $translator, UrlGeneratorInterface $router, OptionSystemService $optionSystemService, Security $security)
    {
        $this->optionSystemService = $optionSystemService;
        parent::__construct($entityManager, $containerBag, $translator, $router, $security);
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

        foreach($options as $optionSystemKey => $optionUserKey)
        {
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
        if($this->security->getUser() === null)
        {
            $key = str_replace('OU_', 'OS_', $key);
            return $this->optionSystemService->getByKey($key);
        }

        $optionServiceRepo = $this->getRepository(OptionUser::class);
        return $optionServiceRepo->findOneBy(['key' => $key, 'user' => $this->security->getUser()->getId()]);
    }
}