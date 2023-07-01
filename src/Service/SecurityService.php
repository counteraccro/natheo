<?php

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Service qui gère les actions de sécurité
 */

namespace App\Service;

use App\Entity\Admin\User;
use App\Entity\Admin\UserData;
use App\Repository\Admin\UserDataRepository;
use App\Service\Admin\System\OptionSystemService;
use App\Utils\Options\OptionSystemKey;
use App\Utils\User\UserdataKey;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

class SecurityService extends AppService
{
    private OptionSystemService $optionSystemService;

    /** Constructeur
     * @param TranslatorInterface $translator
     * @param RequestStack $requestStack
     * @param Security $security
     * @param ContainerBagInterface $params
     * @param OptionSystemService $optionSystemService
     */
    public function __construct(
        TranslatorInterface    $translator,
        RequestStack           $requestStack,
        Security               $security,
        ContainerBagInterface  $params,
        EntityManagerInterface $entityManager,
        OptionSystemService    $optionSystemService
    )
    {
        $this->optionSystemService = $optionSystemService;
        parent::__construct($translator, $requestStack, $security, $params, $entityManager);
    }

    /**
     * Détermine si l'utilisateur peut changer ou non son mot de passe
     * @param string $key
     * @return User|null
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function canChangePassword(string $key): ?User
    {
        /** @var UserDataRepository $repo */
        $repo = $this->getRepository(UserData::class);
        $userData = $repo->findByKeyValue(UserdataKey::KEY_RESET_PASSWORD, $key);
        if ($userData === null) {
            return null;
        }

        $time = $this->optionSystemService->getValueByKey(OptionSystemKey::OS_MAIL_RESET_PASSWORD_TIME);
        $now = new \DateTime();

        if ($userData->getUpdateAt()->add(new \DateInterval('PT' . $time . 'M'))->getTimestamp() <
            $now->getTimestamp()) {
            return null;
        }

        return $userData->getUser();
    }
}
