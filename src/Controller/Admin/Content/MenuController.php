<?php

namespace App\Controller\Admin\Content;

use App\Controller\Admin\AppAdminController;
use App\Entity\Admin\Content\Menu\Menu;
use App\Service\Admin\Content\Menu\MenuService;
use App\Utils\Breadcrumb;
use App\Utils\System\Options\OptionUserKey;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/admin/{_locale}/menu', name: 'admin_menu_', requirements: ['_locale' => '%app.supported_locales%'])]
#[IsGranted('ROLE_CONTRIBUTEUR')]
class MenuController extends AppAdminController
{
    /**
     * Index listing des tags
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        $breadcrumb = [
            Breadcrumb::DOMAIN => 'tag',
            Breadcrumb::BREADCRUMB => [
                'tag.index.page_title_h1' => '#'
            ]
        ];

        return $this->render('admin/content/menu/index.html.twig', [
            'breadcrumb' => $breadcrumb,
            'page' => 1,
            'limit' => $this->optionUserService->getValueByKey(OptionUserKey::OU_NB_ELEMENT)
        ]);
    }

    /**
     * Charge le tableau grid de page en ajax
     * @param MenuService $menuService
     * @param Request $request
     * @param int $page
     * @param int $limit
     * @return JsonResponse
     */
    #[Route('/ajax/load-grid-data/{page}/{limit}', name: 'load_grid_data', methods: ['GET'])]
    public function loadGridData(
        MenuService $menuService,
        Request     $request,
        int         $page = 1,
        int         $limit = 20
    ): JsonResponse
    {
        $search = $request->query->get('search');
        $grid = [];
        //$grid = $pageService->getAllFormatToGrid($page, $limit, $search);
        return $this->json($grid);
    }

    /**
     * Active ou désactive un menu
     * @param Menu $menu
     * @param MenuService $menuService
     * @param TranslatorInterface $translator
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/update-disabled/{id}', name: 'update_disabled', methods: 'PUT')]
    public function updateDisabled(
        Menu                $menu,
        MenuService         $menuService,
        TranslatorInterface $translator,
    ): JsonResponse
    {
        $menu->setDisabled(!$menu->isDisabled());
        $menuService->save($menu);

        $msg = $translator->trans('menu.success.no.disabled', ['label' => $menu->getName()], 'menu');
        if ($menu->isDisabled()) {
            $msg = $translator->trans('menu.success.disabled', ['label' => $menu->getName()], 'menu');
        }
        return $this->json($menuService->getResponseAjax($msg));
    }
}
