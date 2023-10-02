<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Controller pour la gestion des pages
 */
namespace App\Controller\Admin\Content;

use App\Controller\Admin\AppAdminController;
use App\Service\Admin\Content\Page\PageService;
use App\Utils\Breadcrumb;
use App\Utils\System\Options\OptionUserKey;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/{_locale}/page', name: 'admin_page_', requirements: ['_locale' => '%app.supported_locales%'])]
#[IsGranted('ROLE_CONTRIBUTEUR')]
class PageController extends AppAdminController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {

        $breadcrumb = [
            Breadcrumb::DOMAIN => 'page',
            Breadcrumb::BREADCRUMB => [
                'page.index.page_title_h1' => '#'
            ]
        ];

        return $this->render('admin/content/page/index.html.twig', [
            'breadcrumb' => $breadcrumb,
            'page' => 1,
            'limit' => $this->optionUserService->getValueByKey(OptionUserKey::OU_NB_ELEMENT)
        ]);
    }

    /**
     * Charge le tableau grid de tag en ajax
     * @param Request $request
     * @param PageService $pageService
     * @return JsonResponse
     */
    #[Route('/ajax/load-grid-data', name: 'load_grid_data', methods: ['POST'])]
    public function loadGridData(Request $request, PageService $pageService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $grid = $pageService->getAllFormatToGrid($data['page'], $data['limit']);
        return $this->json($grid);
    }
}
