<?php
/**
 * Service pour les notifications
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Service\Admin;

use App\Entity\Admin\Notification;
use App\Entity\Admin\User;
use App\Repository\Admin\NotificationRepository;
use App\Utils\Notification\NotificationFactory;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class NotificationService extends AppAdminService
{

    /**
     * @var OptionSystemService
     */
    private OptionSystemService $optionSystemService;

    public function __construct(
        EntityManagerInterface $entityManager,
        ContainerBagInterface  $containerBag,
        TranslatorInterface    $translator,
        UrlGeneratorInterface  $router,
        Security               $security,
        RequestStack           $requestStack,
        OptionSystemService    $optionSystemService
    )
    {
        $this->optionSystemService = $optionSystemService;
        parent::__construct($entityManager, $containerBag, $translator, $router, $security, $requestStack);
    }

    /**
     * Permet d'ajouter une notification
     * @param User $user
     * @param string $key
     * @param array $params
     * @return void
     */
    public function add(User $user, string $key, array $params): void
    {
        if (!$this->optionSystemService->canNotification()) {
            return;
        }

        $notificationFactory = new NotificationFactory($user);
        $user = $notificationFactory->addNotification($key, $params)->getUser();
        $this->save($user);
    }

    /**
     * Création d'une notification pour une fixtures
     * @param User $user
     * @param string $key
     * @param array $params
     * @return User
     */
    public function addForFixture(User $user, string $key, array $params): User
    {
        if (!$this->optionSystemService->canNotification()) {
            return $user;
        }

        $notificationFactory = new NotificationFactory($user);
        return $notificationFactory->addNotification($key, $params)->getUser();
    }

    /**
     * Retourne le nombre de notifications en fonction du User
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function getNbByUser(User $user) : int
    {
        /** @var NotificationRepository $repo */
       $repo = $this->getRepository(Notification::class);
       return $repo->getNbByUser($user);
    }

}
