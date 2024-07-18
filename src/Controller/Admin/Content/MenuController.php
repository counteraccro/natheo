<?php

namespace App\Controller\Admin\Content;

use App\Controller\Admin\AppAdminController;
use App\Entity\Admin\Content\Menu\Menu;
use App\Service\Admin\Content\Menu\MenuService;
use App\Service\Admin\Content\Page\PageService;
use App\Service\Admin\System\OptionSystemService;
use App\Utils\Breadcrumb;
use App\Utils\Content\Menu\MenuConvertToArray;
use App\Utils\System\Options\OptionSystemKey;
use App\Utils\System\Options\OptionUserKey;
use App\Utils\Translate\Content\MenuTranslate;
use App\Utils\Translate\Content\PageTranslate;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
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
        $grid = $menuService->getAllFormatToGrid($page, $limit, $search);
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

    /**
     * Permet de supprimer un menu
     * @param Menu $menu
     * @param MenuService $menuService
     * @param TranslatorInterface $translator
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/delete/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(
        Menu                $menu,
        MenuService         $menuService,
        TranslatorInterface $translator,
    ): JsonResponse
    {
        $titre = $menu->getName();
        $menuService->remove($menu);

        $msg = $translator->trans('menu.remove.success', ['label' => $titre], domain: 'menu');
        return $this->json($menuService->getResponseAjax($msg));
    }


    /**
     * Création / édition d'un menu
     * @param MenuService $menuService
     * @param MenuTranslate $menuTranslate
     * @param int|null $id
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/add/', name: 'add')]
    #[Route('/update/{id}', name: 'update')]
    public function add(
        MenuService   $menuService,
        MenuTranslate $menuTranslate,
        int           $id = null
    ): Response
    {
        $breadcrumbTitle = 'menu.update.menu_title_h1';
        if ($id === null) {
            $breadcrumbTitle = 'menu.add.menu_title_h1';
        }

        $breadcrumb = [
            Breadcrumb::DOMAIN => 'menu',
            Breadcrumb::BREADCRUMB => [
                'menu.index.page_title' => 'admin_menu_index',
                $breadcrumbTitle => '#'
            ]
        ];

        $translate = $menuTranslate->getTranslate();
        $locales = $menuService->getLocales();

        return $this->render('admin/content/menu/add_update.html.twig', [
            'breadcrumb' => $breadcrumb,
            'translate' => $translate,
            'locales' => $locales,
            'id' => $id,
            'datas' => [
                'list_position' => $menuService->getListPosition(),
                'list_type' => $menuService->getListType()

            ],
            'urls' => [
                'load_menu' => $this->generateUrl('admin_menu_load_menu'),
                'save_menu' => $this->generateUrl('admin_menu_save_menu')
            ]
        ]);
    }

    /**
     * Charge un menu en fonction de son id
     * @param MenuConvertToArray $menuJson
     * @param OptionSystemService $optionSystemService
     * @param PageService $pageService
     * @param int|null $id
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/load-menu/{id}', name: 'load_menu', methods: ['GET'])]
    public function getMenuById(
        MenuConvertToArray  $menuJson,
        OptionSystemService $optionSystemService,
        PageService $pageService,
        int                 $id = null
    ): JsonResponse
    {
        $menu = $menuJson->convertToArray($id);
        $name = $optionSystemService->getValueByKey(OptionSystemKey::OS_SITE_NAME);
        $logo = $optionSystemService->getValueByKey(OptionSystemKey::OS_LOGO_SITE);
        $urlSite = $optionSystemService->getValueByKey(OptionSystemKey::OS_ADRESSE_SITE);
        $allElement = $menu['allElements'];
        unset($menu['allElements']);

        return $this->json(['menu' => $menu, 'data' => [
            'all_elements' => $allElement,
            'name' => $name,
            'logo' => $logo,
            'url_site' => $urlSite,
            'pages' => $pageService->getAllTitleAndUrlPage(),
        ]]);
    }

    /**
     * @param MenuService $menuService
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/save-menu', name: 'save_menu', methods: ['POST'])]
    public function save(
        MenuService $menuService
    ): JsonResponse
    {
        return $this->json($menuService->getResponseAjax());
    }
}
