<?php
/**
 * Gestionnaire des notifications
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Controller\Admin;

use App\Entity\Admin\Notification;
use App\Entity\Admin\System\User;
use App\Enum\Admin\Global\Breadcrumb;
use App\Service\Admin\GridService;
use App\Service\Admin\NotificationService;
use App\Service\Admin\System\OptionSystemService;
use App\Utils\System\Options\OptionSystemKey;
use App\Utils\System\Options\OptionUserKey;
use App\Utils\Translate\NotificationTranslate;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

#[
    Route(
        '/admin/{_locale}/notification',
        name: 'admin_notification_',
        requirements: ['_locale' => '%app.supported_locales%'],
    ),
]
#[IsGranted('ROLE_USER')]
class NotificationController extends AppAdminController
{
    /**
     * Notification de l'Utilisateur
     * @param OptionSystemService $optionSystemService
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/', name: 'index')]
    public function index(OptionSystemService $optionSystemService): Response
    {
        if (!$optionSystemService->canNotification()) {
            return $this->redirectToRoute('admin_dashboard_index');
        }
        $nbDay = $optionSystemService->getValueByKey(OptionSystemKey::OS_PURGE_NOTIFICATION);

        $breadcrumb = [
            Breadcrumb::DOMAIN->value => 'notification',
            Breadcrumb::BREADCRUMB->value => [
                'notification.page_title_h1' => '#',
            ],
        ];

        return $this->render('admin/notification/index.html.twig', [
            'breadcrumb' => $breadcrumb,
            'page' => 1,
            'limit' => $this->optionUserService->getValueByKey(OptionUserKey::OU_NB_ELEMENT),
            'nbDay' => $nbDay,
        ]);
    }

    /**
     * Retourne le nombre de notifications de l'utilisateur courant
     * @param NotificationService $notificationService
     * @return JsonResponse
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    #[Route('/ajax/number', name: 'number', methods: ['GET'])]
    public function number(NotificationService $notificationService): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $number = $notificationService->getNbByUser($user);
        return $this->json(['number' => $number]);
    }

    /**
     * Retourne une liste de notifications en fonction de la pagination
     * @param Request $request
     * @param NotificationService $notificationService
     * @param GridService $gridService
     * @param int $page
     * @param int $limit
     * @param int $pOnlyNotRead
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ExceptionInterface
     */
    #[Route('/ajax/list/{page}/{limit}/{pOnlyNotRead}', name: 'list', methods: ['GET'])]
    public function list(
        Request $request,
        NotificationService $notificationService,
        NotificationTranslate $notificationTranslate,
        GridService $gridService,
        int $page = 1,
        int $limit = 20,
        int $pOnlyNotRead = 1,
    ): JsonResponse {
        $onlyNotRead = false;
        if ($pOnlyNotRead === 1) {
            $onlyNotRead = true;
        }
        /** @var User $user */
        $user = $this->getUser();
        $notifications = $notificationService->getByUserPaginate($page, $limit, $user, $onlyNotRead);
        $notifications = $notificationService->convertEntityToArray($notifications, ['user']);

        return $this->json([
            'notifications' => $notifications,
            'translation' => $notificationTranslate->getTranslate(),
            'urlRead' => $this->generateUrl('admin_notification_read'),
            'urlReadAll' => $this->generateUrl('admin_notification_read_all'),
            'listLimit' => $gridService->addOptionsSelectLimit([])['listLimit'],
            'locale' => $request->getLocale(),
        ]);
    }

    /**
     * Met le status lu à une notification envoyé en paramètre
     * @param Request $request
     * @param NotificationService $notificationService
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/read-notification', name: 'read', methods: ['POST'])]
    public function read(Request $request, NotificationService $notificationService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        /** @var Notification $notification */
        $notification = $notificationService->findOneById(Notification::class, $data['id']);
        $notification->setRead(true);
        $notificationService->save($notification);

        return $this->json(['success' => true]);
    }

    /**
     * Permet de purger les notifications
     * @param OptionSystemService $optionSystemService
     * @param NotificationService $notificationService
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/purge', name: 'purge', methods: ['POST'])]
    public function purge(OptionSystemService $optionSystemService, NotificationService $notificationService): Response
    {
        $nbDay = $optionSystemService->getValueByKey(OptionSystemKey::OS_PURGE_NOTIFICATION);
        $notificationService->purge($nbDay, $this->getUser()->getId());
        return $this->json(['success' => true]);
    }

    /**
     * Permet de marquer toutes les notifications non lu, en lu en fonction de l'utilisateur
     * @param NotificationService $notificationService
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/readAll', name: 'read_all')]
    public function readAll(NotificationService $notificationService): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $notificationService->readAll($user);
        return $this->json(['success' => true]);
    }
}
