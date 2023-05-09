<?php
/**
 * Gestionnaire des notifications
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Controller\Admin;

use App\Service\Admin\Breadcrumb;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/{_locale}/notification', name: 'admin_notification_',
    requirements: ['_locale' => '%app.supported_locales%'])]
#[IsGranted('ROLE_USER')]
class NotificationController extends AppAdminController
{
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
}
