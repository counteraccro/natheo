<?php
/**
 * Controller pour la recherche globale
 * @author Gourdon Aymeric
 * @version 2.0
 */
namespace App\Controller\Admin\Global;

use App\Controller\Admin\AppAdminController;
use App\Enum\Admin\Global\Breadcrumb;
use App\Service\Admin\GlobalSearchService;
use App\Utils\System\Options\OptionUserKey;
use App\Utils\Translate\GlobalSearchTranslate;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/{_locale}/search', name: 'admin_search_', requirements: ['_locale' => '%app.supported_locales%'])]
#[IsGranted('ROLE_USER')]
class GlobalSearchController extends AppAdminController
{
    /**
     * Point d'entrée pour la recherche
     * @param Request $request
     * @param GlobalSearchTranslate $globalSearchTranslate
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/', name: 'index', methods: ['POST'])]
    public function index(Request $request, GlobalSearchTranslate $globalSearchTranslate): Response
    {
        $breadcrumb = [
            Breadcrumb::DOMAIN->value => 'global_search',
            Breadcrumb::BREADCRUMB->value => [
                'global_search.index.page_title_h1' => '#',
            ],
        ];

        $search = $request->request->get('global-search-input', '');
        return $this->render('admin/global_search/index.html.twig', [
            'search' => $search,
            'breadcrumb' => $breadcrumb,
            'translate' => $globalSearchTranslate->getTranslate(),
            'urls' => [
                'listingPage' => $this->generateUrl('admin_page_index'),
                'searchPage' => $this->generateUrl('admin_search_global'),
            ],
            'limit' => intval($this->optionUserService->getValueByKey(OptionUserKey::OU_NB_ELEMENT)),
            'page' => 1,
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
    #[Route('/{entity}/{page}/{limit}/{search}', name: 'global', methods: ['GET'])]
    public function search(
        Request $request,
        GlobalSearchService $globalSearchService,
        ?string $entity = null,
        int $page = 1,
        int $limit = 20,
        ?string $search = null,
    ): Response {
        $result = ['elements' => [], 'total' => 0];
        if ($search !== null) {
            $result = $globalSearchService->globalSearch($entity, $search, $page, $limit);
        }

        return $this->json([
            'recherche page' => $search,
            'result' => $result,
            'paginate' => ['current' => $page, 'limit' => $limit],
        ]);
    }
}
