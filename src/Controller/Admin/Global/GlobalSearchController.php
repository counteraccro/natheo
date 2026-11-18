<?php

declare(strict_types=1);
/**
 * Controller pour la recherche globale
 * @author Gourdon Aymeric
 * @version 2.0
 */
namespace App\Controller\Admin\Global;

use App\Controller\Admin\AppAdminController;
use App\Enum\Admin\Global\Breadcrumb;
use App\Enum\Admin\Global\GlobalSearchEntity;
use App\Enum\Admin\System\Options\OptionUser;
use App\Service\Admin\GlobalSearchService;
use App\Service\Admin\Module\AdminMenuBuilder;
use App\Translate\Admin\Global\GlobalSearchTranslate;
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
     * @param AdminMenuBuilder $menuBuilder
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/', name: 'index', methods: ['POST'])]
    public function index(
        Request $request,
        GlobalSearchTranslate $globalSearchTranslate,
        AdminMenuBuilder $menuBuilder,
    ): Response {
        $breadcrumb = [
            Breadcrumb::DOMAIN->value => 'global_search',
            Breadcrumb::BREADCRUMB->value => [
                'global_search.index.title' => '#',
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
            'limit' => intval($this->optionUserService->getValueByKey(OptionUser::OU_NB_ELEMENT->value)),
            'page' => 1,
            'entities' => array_values(
                array_map(
                    fn(GlobalSearchEntity $type) => $type->value,
                    array_filter(
                        GlobalSearchEntity::cases(),
                        fn(GlobalSearchEntity $type) => $menuBuilder->isModuleVisible($type->getModule()),
                    ),
                ),
            ),
        ]);
    }

    /**
     * Effectue une recherche en fonction de l'entité et de search, vide si l'utilisateur ne voit pas son module
     * @param Request $request
     * @param GlobalSearchService $globalSearchService
     * @param AdminMenuBuilder $menuBuilder
     * @param string|null $entity
     * @param string|null $search
     * @param int $page
     * @param int $limit
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/{entity}/{page}/{limit}/{search}', name: 'global', requirements: ['search' => '.+'], methods: ['GET'])]
    public function search(
        Request $request,
        GlobalSearchService $globalSearchService,
        AdminMenuBuilder $menuBuilder,
        ?string $entity = null,
        int $page = 1,
        int $limit = 20,
        ?string $search = null,
    ): Response {
        $result = ['elements' => [], 'total' => 0];
        $module = GlobalSearchEntity::tryFrom((string) $entity)?->getModule();
        if ($search !== null && ($module === null || $menuBuilder->isModuleVisible($module))) {
            $result = $globalSearchService->globalSearch($entity, $search, $page, $limit);
        }

        return $this->json([
            'recherche page' => $search,
            'result' => $result,
            'paginate' => ['current' => $page, 'limit' => $limit],
        ]);
    }
}
