<?php

/**
 * Dashboard
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Controller\Admin;

use App\Service\Admin\System\User\UserDataService;
use App\Utils\Breadcrumb;
use App\Utils\System\User\UserDataKey;
use App\Utils\Translate\Dashboard\DashboardTranslate;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/{_locale}', name: 'admin_dashboard_', requirements: ['_locale' => '%app.supported_locales%'])]
#[IsGranted('ROLE_USER')]
class DashboardController extends AppAdminController
{
    /**
     * Point d'entrée du dashboard
     * @param DashboardTranslate $dashboardTranslate
     * @param UserDataService $userDataService
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/dashboard/index', name: 'index')]
    #[Route('/dashboard', 'index_3')]
    #[Route('/', name: 'index_2')]
    public function index(DashboardTranslate $dashboardTranslate, UserDataService $userDataService): Response
    {

        return $this->render('admin/dashboard/index.html.twig', [
            'translate' => $dashboardTranslate->getTranslate(),
            'urls' => [
                'dashboard_help_first_connexion' => [

                ]
            ],
            'datas' => [
                'dashboard_help_first_connexion' => [
                    'help_first_connexion' => $userDataService->getHelpFirstConnexion($this->getUser())
                ]
            ],
        ]);
    }

    /**
     * Page Démo pour les elements html
     * @param int $param
     * @return Response
     */
    #[IsGranted('ROLE_SUPER_ADMIN')]
    #[Route('/page-demo/{param}', name: 'page_demo')]
    public function pageDemo($param = 5): Response
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
