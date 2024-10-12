<?php

/**
 * Dashboard
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Controller\Admin;

use App\Service\Admin\DashboardService;
use App\Service\Admin\System\User\UserDataService;
use App\Utils\Breadcrumb;
use App\Utils\Dashboard\DashboardKey;
use App\Utils\System\User\UserDataKey;
use App\Utils\Translate\Dashboard\DashboardTranslate;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/admin/{_locale}/dashboard', name: 'admin_dashboard_', requirements: ['_locale' => '%app.supported_locales%'])]
#[IsGranted('ROLE_USER')]
class DashboardController extends AppAdminController
{
    /**
     * Point d'entrée du dashboard
     * @param DashboardTranslate $dashboardTranslate
     * @param UserDataService $userDataService
     * @return Response
     */
    #[Route('/index', name: 'index')]
    #[Route('/', 'index_3')]
    #[Route('', name: 'index_2')]
    public function index(DashboardTranslate $dashboardTranslate, UserDataService $userDataService): Response
    {

        return $this->render('admin/dashboard/index.html.twig', [
            'translate' => $dashboardTranslate->getTranslate(),
            'urls' => [
                'dashboard_help_first_connexion' => [
                    'load_block_dashboard' => $this->generateUrl('admin_dashboard_load_block', ['id' => DashboardKey::DASHBOARD_HELP_FIRST_CONNEXION_ID]),
                    'update_user_data' => $this->generateUrl('admin_user_update_user_data'),
                ],
                'dashboard_last_comments' => [
                    'load_block_dashboard' => $this->generateUrl('admin_dashboard_load_block', ['id' => 'todo-a-faire']),
                ]
            ],
            'datas' => [
                'dashboard_help_first_connexion' => [
                    'help_first_connexion' => $userDataService->getHelpFirstConnexion($this->getUser()),
                    'user_data_key_first_connexion' => UserDataKey::KEY_HELP_FIRST_CONNEXION
                ],
            ],
        ]);
    }

    /**
     * Charge un block du dashboard en fonction de son id
     * @param string $id
     * @param TranslatorInterface $translator
     * @param DashboardService $dashboardService
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/load-block-dashboard/{id}', name: 'load_block', methods: ['GET'])]
    public function loadDashboardBlock(
        string              $id,
        TranslatorInterface $translator,
        DashboardService    $dashboardService
    ): JsonResponse
    {
        $return = match ($id) {
            DashboardKey::DASHBOARD_HELP_FIRST_CONNEXION_ID => $dashboardService->getBlockHelpConfig(),
            default => ['success' => false, 'body' => null, 'error' => $translator->trans('dashboard.error.load.block', domain: 'dashboard')],
        };
        return $this->json($return);
    }

    /**
     * Page Démo pour les elements html
     * @param int $param
     * @return Response
     */
    #[IsGranted('ROLE_SUPER_ADMIN')]
    #[Route('/page-demo/{param}', name: 'page_demo')]
    public function pageDemo(int $param = 5): Response
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
