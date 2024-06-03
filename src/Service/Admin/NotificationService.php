<?php
/**
 * Service pour les notifications
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Service\Admin;

use App\Entity\Admin\Notification;
use App\Entity\Admin\System\User;
use App\Repository\Admin\NotificationRepository;
use App\Service\Admin\System\OptionSystemService;
use App\Utils\Notification\NotificationFactory;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;
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
use Symfony\Contracts\Translation\TranslatorInterface;

class NotificationService extends AppAdminService
{

    /**
     * Permet d'ajouter une notification
     * @param User $user
     * @param string $key
     * @param array $params
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function add(User $user, string $key, array $params): void
    {
        $optionSystemService = $this->getOptionSystemService();
        if (!$optionSystemService->canNotification()) {
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
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function addForFixture(User $user, string $key, array $params): User
    {
        $optionSystemService = $this->getOptionSystemService();
        if (!$optionSystemService->canNotification()) {
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
    public function getNbByUser(User $user): int
    {
        /** @var NotificationRepository $repo */
        $repo = $this->getRepository(Notification::class);
        return $repo->getNbByUser($user);
    }

    /**
     * Retourne une liste de Notification paginé de l'utilisateur et traduit dans la langue de l'utilisateur
     * @param int $page
     * @param int $limit
     * @param User $user
     * @param bool $onlyNotRead : si true, ne retourne que les non lu
     * @return Paginator
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getByUserPaginate(int $page, int $limit, User $user, bool $onlyNotRead = false): Paginator
    {
        $translator = $this->getTranslator();

        /** @var NotificationRepository $repo */
        $repo = $this->getRepository(Notification::class);
        $list = $repo->getByUserPaginate($page, $limit, $user, $onlyNotRead);

        /** @var Notification $notification */
        foreach ($list as $notification) {
            $parameter = json_decode($notification->getParameters(), true);
            $notification->setTitle($translator->trans($notification->getTitle(), domain: 'notification'));
            $notification->setContent($translator->trans(
                $notification->getContent(),
                $parameter,
                domain: 'notification'
            ));
        }
        return $list;
    }

    /**
     * Retourne les traductions pour le listing des notifications
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getTranslateListNotifications(): array
    {
        $translator = $this->getTranslator();

        return [
            'nb_notifification_show_start' => $translator->trans(
                'notification.nb.show.start',
                domain: 'notification'
            ),
            'nb_notifification_show_end' => $translator->trans(
                'notification.nb.show.end',
                domain: 'notification'
            ),
            'loading' => $translator->trans('notification.loading', domain: 'notification'),
            'empty' => $translator->trans('notification.empty', domain: 'notification'),
            'onlyNotRead' => $translator->trans('notification.only_not_read', domain: 'notification'),
            'readAll' => $translator->trans('notification.read_All', domain: 'notification'),
            'all' => $translator->trans('notification.all', domain: 'notification'),
            'allSuccess' => $translator->trans('notification.all_success', domain: 'notification'),
        ];
    }

    /**
     * Permet de purger les notifications lues qui ont dépassé un X nombre de jours
     * en fonction de l'user
     * @param int $nbDay
     * @param int $userId
     * @return void
     */
    public function purge(int $nbDay, int $userId): void
    {
        $repo = $this->getRepository(Notification::class);
        $repo->removeAfterDay($nbDay, $userId);
    }

    /**
     * Met en status lu toutes les notifications non lus du user
     * @param User $user
     * @return void
     */
    public function readAll(User $user): void
    {
        $repo = $this->getRepository(Notification::class);
        $repo->readAll($user);
    }

}
