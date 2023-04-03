<?php
/**
 * Translation controller, gestion des traductions
 * @author Gourdon Aymeric
 * @version 1.0
 */
namespace App\Controller\Admin;

use App\Service\Admin\Breadcrumb;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/{_locale}/translation', name: 'admin_translation_',
    requirements: ['_locale' => '%app.supported_locales%'])]
#[IsGranted('ROLE_SUPER_ADMIN')]
class TranslationController extends AppAdminController
{
    #[Route('/admin/translation', name: 'index')]
    public function index(): Response
    {
        $breadcrumb = [
            Breadcrumb::DOMAIN => 'translate',
            Breadcrumb::BREADCRUMB => [
                'translate.page_title_h1' => '#'
            ]
        ];

        return $this->render('admin/translation/index.html.twig', [
            'breadcrumb' => $breadcrumb,
        ]);
    }
}
