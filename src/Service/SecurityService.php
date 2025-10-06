<?php

/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Service qui gère les actions de sécurité
 */

namespace App\Service;

use App\Entity\Admin\System\User;
use App\Entity\Admin\System\UserData;
use App\Repository\Admin\System\UserDataRepository;
use App\Service\Admin\System\OptionSystemService;
use App\Utils\System\Options\OptionSystemKey;
use App\Utils\System\User\UserDataKey;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

class SecurityService extends AppService
{
    private OptionSystemService $optionSystemService;

    public function __construct(
        #[
            AutowireLocator([
                'entityManager' => EntityManagerInterface::class,
                'containerBag' => ContainerBagInterface::class,
                'translator' => TranslatorInterface::class,
                'security' => Security::class,
                'requestStack' => RequestStack::class,
                'OptionSystemService' => OptionSystemService::class,
            ]),
        ]
        private readonly ContainerInterface $handlers,
    ) {
        $this->optionSystemService = $this->handlers->get('OptionSystemService');
        parent::__construct($handlers);
    }

    /**
     * Détermine si l'utilisateur peut changer ou non son mot de passe
     * @param string $key
     * @return User|null
     * @throws \DateMalformedIntervalStringException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function canChangePassword(string $key): ?User
    {
        /** @var UserDataRepository $repo */
        $repo = $this->getRepository(UserData::class);
        $userData = $repo->findByKeyValue(UserDataKey::KEY_RESET_PASSWORD, $key);

        if ($userData === null) {
            return null;
        }

        $time = $this->optionSystemService->getValueByKey(OptionSystemKey::OS_MAIL_RESET_PASSWORD_TIME);
        $now = new \DateTime();

        if (
            $userData
                ->getUpdateAt()
                ->add(new \DateInterval('PT' . $time . 'M'))
                ->getTimestamp() < $now->getTimestamp()
        ) {
            return null;
        }

        return $userData->getUser();
    }
}
