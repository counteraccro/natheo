<?php
/**
 * Gestionnaire des notifications
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Controller\Admin;

use App\Entity\Admin\Notification;
use App\Entity\Admin\User;
use App\Service\Admin\Breadcrumb;
use App\Service\Admin\GridService;
use App\Service\Admin\NotificationService;
use App\Service\Admin\OptionSystemService;
use App\Utils\Options\OptionSystemKey;
use App\Utils\Options\OptionUserKey;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/{_locale}/notification', name: 'admin_notification_',
    requirements: ['_locale' => '%app.supported_locales%'])]
#[IsGranted('ROLE_USER')]
class NotificationController extends AppAdminController
{
    /**
     * Notification de l'Utilisateur
     * @param OptionSystemService $optionSystemService
     * @return Response
     */
    #[Route('/', name: 'index')]
    public function index(OptionSystemService $optionSystemService): Response
    {

        if (!$optionSystemService->canNotification()) {
            return $this->redirectToRoute('admin_dashboard_index');
        }
        $nbDay = $optionSystemService->getValueByKey(OptionSystemKey::OS_PURGE_NOTIFICATION);

        $breadcrumb = [
            Breadcrumb::DOMAIN => 'notification',
            Breadcrumb::BREADCRUMB => [
                'notification.page_title_h1' => '#'
            ]
        ];

        return $this->render('admin/notification/index.html.twig', [
            'breadcrumb' => $breadcrumb,
            'page' => 1,
            'limit' => $this->optionUserService->getValueByKey(OptionUserKey::OU_NB_ELEMENT),
            'nbDay' => $nbDay
        ]);
    }

    /**
     * Retourne le nombre de notifications de l'utilisateur courant
     * @param NotificationService $notificationService
     * @return JsonResponse
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    #[Route('/ajax/number', name: 'number', methods: ['POST'])]
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
     * @return JsonResponse
     */
    #[Route('/ajax/list', name: 'list', methods: ['POST'])]
    public function list(
        Request             $request,
        NotificationService $notificationService,
        GridService         $gridService,
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        /** @var User $user */
        $user = $this->getUser();
        $notifications = $notificationService->getByUserPaginate($data['page'], $data['limit'], $user);

        return $this->json([
            'notifications' => $notifications,
            'translation' => $notificationService->getTranslateListNotifications(),
            'urlRead' => $this->generateUrl('admin_notification_read'),
            'listLimit' => $gridService->addOptionsSelectLimit([])['listLimit'],
            'locale' => $request->getLocale()
        ]);
    }

    /**
     * Met le status lu à une notification envoyé en paramètre
     * @param Request $request
     * @param NotificationService $notificationService
     * @return JsonResponse
     */
    #[Route('/ajax/read-notification', name: 'read', methods: ['POST'])]
    public function read(Request $request, NotificationService $notificationService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        /** @var Notification $notification */
        $notification = $notificationService->findOneBy(Notification::class, $data['id']);
        $notification->setRead(true);
        $notificationService->save($notification);

        return $this->json(
            ['success' => true]);
    }

    /**
     * Permet de purger les commentaires
     * @param OptionSystemService $optionSystemService
     * @param NotificationService $notificationService
     * @return Response
     */
    #[Route('/ajax/purge', name: 'purge')]
    public function purge(OptionSystemService $optionSystemService, NotificationService $notificationService): Response
    {
        $nbDay = $optionSystemService->getValueByKey(OptionSystemKey::OS_PURGE_NOTIFICATION);
        $notificationService->purge($nbDay, $this->getUser()->getId());
        return $this->json(['success' => true]);
    }
}
