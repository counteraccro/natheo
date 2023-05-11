<?php
/**
 * Gestionnaire des notifications
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Controller\Admin;

use App\Service\Admin\Breadcrumb;
use Symfony\Component\HttpFoundation\JsonResponse;
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
    public function index(): Response
    {
        $breadcrumb = [
            Breadcrumb::DOMAIN => 'notification',
            Breadcrumb::BREADCRUMB => [
                'notification.page_title_h1' => '#'
            ]
        ];

        return $this->render('admin/notification/index.html.twig', [
            'breadcrumb' => $breadcrumb,
        ]);
    }

    /**
     * Retourne le nombre de notifications de l'utilisateur courant
     * @return JsonResponse
     */
    #[Route('/ajax/number', name: 'number')]
    public function number(): JsonResponse
    {
        $number = rand(0, 30);
        return $this->json(['number' => $number]);
    }
}
