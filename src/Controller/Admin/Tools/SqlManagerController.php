<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Controller pour la gestion des requêtes SQL
 */
namespace App\Controller\Admin\Tools;

use App\Controller\Admin\AppAdminController;
use App\Entity\Admin\Tools\SqlManager;
use App\Service\Admin\Tools\SqlManagerService;
use App\Utils\Breadcrumb;
use App\Utils\System\Options\OptionUserKey;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/{_locale}/sql-manager', name: 'admin_sql_manager_',
    requirements: ['_locale' => '%app.supported_locales%'])]
#[IsGranted('ROLE_CONTRIBUTEUR')]
class SqlManagerController extends AppAdminController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        $breadcrumb = [
            Breadcrumb::DOMAIN => 'sql_manager',
            Breadcrumb::BREADCRUMB => [
                'sql_manager.index.page_title_h1' => '#'
            ]
        ];

        return $this->render('admin/tools/sql_manager/index.html.twig', [
            'breadcrumb' => $breadcrumb,
            'page' => 1,
            'limit' => $this->optionUserService->getValueByKey(OptionUserKey::OU_NB_ELEMENT)
        ]);
    }

    /**
     * Charge le tableau grid de faq en ajax
     * @param FaqService $faqService
     * @param Request $request
     * @param int $page
     * @param int $limit
     * @return JsonResponse
     */
    #[Route('/ajax/load-grid-data/{page}/{limit}', name: 'load_grid_data', methods: ['GET'])]
    public function loadGridData(
        SqlManagerService $sqlManagerService,
        Request    $request,
        int        $page = 1,
        int        $limit = 20
    ): JsonResponse
    {
        $search = $request->query->get('search');
        $grid = $sqlManagerService->getAllFormatToGrid($page, $limit, $search);
        return $this->json($grid);
    }
}
