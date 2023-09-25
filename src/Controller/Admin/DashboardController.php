<?php

/**
 * Dashboard
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Controller\Admin;

use App\Utils\Breadcrumb;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/{_locale}', name: 'admin_dashboard_', requirements: ['_locale' => '%app.supported_locales%'])]
#[IsGranted('ROLE_USER')]
class DashboardController extends AppAdminController
{
    #[Route('/dashboard/index', name: 'index')]
    #[Route('/dashboard', 'index_3')]
    #[Route('/', name: 'index_2')]
    public function index(): Response
    {
        return $this->render('admin/dashboard/index.html.twig', [
            'controller_name' => 'IndexController',
        ]);
    }

    /**
     * Page Démo pour les elements html
     * @return Response
     */
    #[IsGranted('ROLE_SUPER_ADMIN')]
    #[Route('/page-demo', name: 'page_demo')]
    public function pageDemo(): Response
    {
        $breadcrumb = [
            Breadcrumb::DOMAIN => 'message',
            Breadcrumb::BREADCRUMB => [
                'pagedemo.element.html' => '#'
            ]
        ];

        return $this->render('admin/dashboard/page_demo.html.twig', ['breadcrumb' => $breadcrumb]);
    }
}
