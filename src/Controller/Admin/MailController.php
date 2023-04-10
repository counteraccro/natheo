<?php
/**
 * Gestionnaire des mails
 * @author Gourdon Aymeric
 * @version 1.0
 */
namespace App\Controller\Admin;

use App\Entity\Admin\MailTranslation;
use App\Service\Admin\Breadcrumb;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/{_locale}/mail', name: 'admin_mail_', requirements: ['_locale' => '%app.supported_locales%'])]
#[IsGranted('ROLE_SUPER_ADMIN')]
class MailController extends AbstractController
{
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
}
