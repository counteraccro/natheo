<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Controller pour la gestion des médias
 */
namespace App\Controller\Admin\Content;

use App\Controller\Admin\AppAdminController;
use App\Utils\Breadcrumb;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/{_locale}/media', name: 'admin_media_',
    requirements: ['_locale' => '%app.supported_locales%'])]
#[IsGranted('ROLE_CONTRIBUTEUR')]
class MediaController extends AppAdminController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        $breadcrumb = [
            Breadcrumb::DOMAIN => 'media',
            Breadcrumb::BREADCRUMB => [
                'media.index.page_title_h1' => '#'
            ]
        ];

        return $this->render('admin/content/media/index.html.twig', [
            'breadcrumb' => $breadcrumb,
        ]);
    }
}
