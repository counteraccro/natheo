<?php
/**
 * Gestionnaire des mails
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Controller\Admin;

use App\Entity\Admin\MailTranslation;
use App\Service\Admin\Breadcrumb;
use App\Service\Admin\MarkdownEditorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/{_locale}/mail', name: 'admin_mail_', requirements: ['_locale' => '%app.supported_locales%'])]
#[IsGranted('ROLE_SUPER_ADMIN')]
class MailController extends AbstractController
{
    /**
     * Point d'entrée de la gestion des emails
     * @return Response
     */
    #[Route('/', name: 'index')]
    public function index(): Response
    {

        $breadcrumb = [
            Breadcrumb::DOMAIN => 'mail',
            Breadcrumb::BREADCRUMB => [
                'mail.page_title_h1' => '#'
            ]
        ];

        return $this->render('admin/mail/index.html.twig', [
            'breadcrumb' => $breadcrumb,
        ]);
    }

    /**
     * Charge les données pour les emails en ajax
     * @return JsonResponse
     */
    #[Route('/ajax/load-data', name: 'load_data', methods: ['POST'])]
    public function loadData(MarkdownEditorService $markdownEditorService): JsonResponse
    {
        return $this->json(['translateEditor' => $markdownEditorService->getTranslate()]);
    }
}
