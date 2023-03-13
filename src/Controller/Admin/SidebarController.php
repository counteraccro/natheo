<?php
/**
 * Sidebar
 * @author Gourdon Aymeric
 * @version 1.0
 */
namespace App\Controller\Admin;

use App\Service\Admin\SidebarElementService;
use App\Utils\Debug;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/{_locale}/sidebar', name: 'admin_sidebar_', requirements: ['_locale' => '%app.supported_locales%'])]
#[IsGranted('ROLE_SUPER_ADMIN')]
class SidebarController extends AbstractController
{
    /**
     * Point d'entrée
     * @return Response
     */
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        $breadcrumb = [
            'sidebar.page_title_h1' => '#'
        ];

        return $this->render('admin/sidebar/index.html.twig', [
            'breadcrumb' => $breadcrumb,
            'page' => 1,
            'limit' => 6
        ]);
    }

    /**
     * Charge le tableau grid de sidebar en ajax
     * @param Request $request
     * @param SidebarElementService $sidebarElementService
     * @return JsonResponse
     */
    #[Route('/ajax/load-grid-data', name: 'load_grid_data')]
    public function loadGridData(Request $request, SidebarElementService $sidebarElementService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $grid = $sidebarElementService->getAllFormatToGrid($data['page'], $data['limit']);
        return $this->json($grid);
    }
}
