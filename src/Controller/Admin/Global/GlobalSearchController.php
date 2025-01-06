<?php

namespace App\Controller\Admin\Global;

use App\Service\Admin\GlobalSearchService;
use App\Utils\Breadcrumb;
use App\Utils\Translate\GlobalSearchTranslate;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[\Symfony\Component\Routing\Annotation\Route('/admin/{_locale}/search', name: 'admin_search_', requirements: ['_locale' => '%app.supported_locales%'])]
#[IsGranted('ROLE_USER')]
class GlobalSearchController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['POST'])]
    public function index(Request $request, GlobalSearchTranslate $globalSearchTranslate): Response
    {
        $breadcrumb = [
            Breadcrumb::DOMAIN => 'global_search',
            Breadcrumb::BREADCRUMB => [
                'global_search.index.page_title_h1' => '#'
            ]
        ];

        $search = $request->get('global-search-input', '');
        return $this->render('admin/global_search/index.html.twig', [
            'search' => $search,
            'breadcrumb' => $breadcrumb,
            'translate' => $globalSearchTranslate->getTranslate(),
            'urls' => [
                'listingPage' => $this->generateUrl('admin_page_index'),
                'searchPage' => $this->generateUrl('admin_search_global')
            ]
        ]);
    }

    /**
     * Effectue une recherche en fonction de l'entité et de search
     * @param Request $request
     * @param GlobalSearchService $globalSearchService
     * @param string|null $entity
     * @param string|null $search
     * @param int $page
     * @param int $limit
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/{entity}/{search}/{page}/{limit}', name: 'global', methods: ['GET'])]
    public function search(
        Request $request,
        GlobalSearchService $globalSearchService,
        string $entity = null,
        string $search = null,
        int $page = 1,
        int $limit = 20
    ): Response
    {
        $result = $globalSearchService->globalSearch($entity, $search, $page, $limit);
        return $this->json(['recherche page' => $search, 'result' => $result, 'paginate' => ['current' => $page, 'limit' => $limit]]);
    }
}
