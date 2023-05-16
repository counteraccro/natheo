<?php
/**
 * Gestionnaire des notifications
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Controller\Admin;

use App\Entity\Admin\User;
use App\Service\Admin\Breadcrumb;
use App\Service\Admin\NotificationService;
use App\Service\Admin\OptionSystemService;
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
     * @return Response
     */
    #[Route('/', name: 'index')]
    public function index(OptionSystemService $optionSystemService): Response
    {

        if (!$optionSystemService->canNotification()) {
            return $this->redirectToRoute('admin_dashboard_index');
        }

        $breadcrumb = [
            Breadcrumb::DOMAIN => 'notification',
            Breadcrumb::BREADCRUMB => [
                'notification.page_title_h1' => '#'
            ]
        ];

        return $this->render('admin/notification/index.html.twig', [
            'breadcrumb' => $breadcrumb,
            'page' => 1,
            'limit' => $this->optionUserService->getValueByKey(OptionUserKey::OU_NB_ELEMENT)
        ]);
    }

    /**
     * Retourne le nombre de notifications de l'utilisateur courant
     * @param NotificationService $notificationService
     * @return JsonResponse
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    #[Route('/ajax/number', name: 'number')]
    public function number(NotificationService $notificationService): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $number = $notificationService->getNbByUser($user);
        return $this->json(['number' => $number]);
    }

    /**
     * Retourne une liste de notifications en fonction de la pagination
     * @param NotificationService $notificationService
     * @return JsonResponse
     */
    #[Route('/ajax/list', name: 'list')]
    public function list(Request $request, NotificationService $notificationService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        /** @var User $user */
        $user = $this->getUser();
        $notifications = $notificationService->getByUserPaginate($data['page'], $data['limit'], $user);

        return $this->json(
            ['notifications' => $notifications, 'translation' => []]);
    }
}
