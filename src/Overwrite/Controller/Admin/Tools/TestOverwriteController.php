<?php

/**
 * Controller de test pour la surcharge
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Overwrite\Controller\Admin\Tools;

use App\Controller\Admin\AppAdminController;
use App\Enum\Admin\Global\Breadcrumb;
use App\Service\Admin\System\OptionSystemService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/{_locale}/overwrite', name: 'admin_overwrite_', requirements: ['_locale' => '%app.supported_locales%'])]
#[IsGranted('ROLE_USER')]
class TestOverwriteController extends AppAdminController
{
    /**
     * Page Démo pour les elements html
     * @param OptionSystemService $optionSystemService
     * @param $id
     * @return Response
     */
    #[IsGranted('ROLE_SUPER_ADMIN')]
    #[Route('/page-demo/{id}', name: 'page_demo')]
    public function pageDemo(OptionSystemService $optionSystemService, $id): Response
    {
        $breadcrumb = [
            Breadcrumb::DOMAIN->value => 'message',
            Breadcrumb::BREADCRUMB->value => [
                'pagedemo.element.html' => '#',
            ],
        ];

        return $this->render('overwrite/admin/tools/page_demo.html.twig', ['breadcrumb' => $breadcrumb, 'id' => $id]);
    }
}
