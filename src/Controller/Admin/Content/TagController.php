<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Controller pour la gestion des tags
 */

namespace App\Controller\Admin\Content;

use App\Controller\Admin\AppAdminController;
use App\Service\Admin\Breadcrumb;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/{_locale}/tag', name: 'admin_tag_', requirements: ['_locale' => '%app.supported_locales%'])]
#[IsGranted('ROLE_CONTRIBUTEUR')]
class TagController extends AppAdminController
{
    /**
     * Index listing des tags
     * @return Response
     */
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        $breadcrumb = [
            Breadcrumb::DOMAIN => 'tag',
            Breadcrumb::BREADCRUMB => [
                'tag.index.page_title_h1' => '#'
            ]
        ];

        return $this->render('admin/content/tag/index.html.twig', [
            'breadcrumb' => $breadcrumb,
        ]);
    }
}
