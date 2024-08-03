<?php

namespace App\Controller\Admin\Content;

use App\Controller\Admin\AppAdminController;
use App\Entity\Admin\Content\Menu\Menu;
use App\Entity\Admin\Content\Menu\MenuElement;
use App\Service\Admin\Content\Menu\MenuService;
use App\Service\Admin\Content\Page\PageService;
use App\Service\Admin\System\OptionSystemService;
use App\Utils\Breadcrumb;
use App\Utils\Content\Menu\MenuConvertToArray;
use App\Utils\Content\Menu\MenuPopulate;
use App\Utils\System\Options\OptionSystemKey;
use App\Utils\System\Options\OptionUserKey;
use App\Utils\Translate\Content\MenuTranslate;
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
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
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
                'save_menu' => $this->generateUrl('admin_menu_save_menu'),
                'delete_menu_element' => $this->generateUrl('admin_menu_delete_menu_element'),
                'new_menu_element' => $this->generateUrl('admin_menu_new_menu_element'),
                'update_parent_menu_element' => $this->generateUrl('admin_menu_update_parent_menu_element'),
                'list_parent_menu_element' => $this->generateUrl('admin_menu_list_parent_menu_element'),
                'reorder_menu_element' => $this->generateUrl('admin_menu_reorder_menu_element')
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
        PageService         $pageService,
        int                 $id = null
    ): JsonResponse
    {
        $menu = $menuJson->convertToArray($id);
        $name = $optionSystemService->getValueByKey(OptionSystemKey::OS_SITE_NAME);
        $logo = $optionSystemService->getValueByKey(OptionSystemKey::OS_LOGO_SITE);
        $urlSite = $optionSystemService->getValueByKey(OptionSystemKey::OS_ADRESSE_SITE);
        $allElement = [];
        if (isset($menu['allElements'])) {
            $allElement = $menu['allElements'];
            unset($menu['allElements']);
        }


        return $this->json(['menu' => $menu, 'data' => [
            'all_elements' => $allElement,
            'name' => $name,
            'logo' => $logo,
            'url_site' => $urlSite,
            'pages' => $pageService->getAllTitleAndUrlPage(),
        ]]);
    }

    /**
     * @param Request $request
     * @param MenuService $menuService
     * @param TranslatorInterface $translator
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/save-menu', name: 'save_menu', methods: ['POST'])]
    public function save(
        Request     $request,
        MenuService $menuService,
        TranslatorInterface $translator
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $menu = new Menu();
        $menu->setUser($this->getUser());
        $redirect = true;
        $msgSuccess = $translator->trans('menu.new.success.save', domain: 'menu');
        if (isset($data['menu']['id']) && $data['menu']['id'] > 0) {
            $menu = $menuService->findOneById(Menu::class, $data['menu']['id']);
            $redirect = false;
            $msgSuccess = $translator->trans('menu.edit.success.save', domain: 'menu');
        }
        $menuPopulate = new MenuPopulate($menu, $data['menu'], $menuService);
        $menu = $menuPopulate->populate()->getMenu();
        $menuService->save($menu);
        $response = $menuService->getResponseAjax($msgSuccess);
        $response['redirect'] = $redirect;
        $response['url'] = $this->generateUrl('admin_menu_update', ['id' => $menu->getId()]);
        return $this->json($response);
    }

    /**
     * Supprime un menuElement
     * @param MenuService $menuService
     * @param TranslatorInterface $translator
     * @param int|null $id
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/delete-menu-element/{id}', name: 'delete_menu_element', methods: ['DELETE'])]
    public function deleteMenuElement(
        MenuService         $menuService,
        TranslatorInterface $translator,
        int                 $id = null,
    ): JsonResponse
    {
        if ($id === null) {
            return $this->json($menuService->getResponseAjax());
        }

        $menuElement = $menuService->findOneById(MenuElement::class, $id);
        $menuService->remove($menuElement);
        return $this->json($menuService->getResponseAjax($translator->trans('menu.element.remove.success', [], 'menu')));
    }

    /**
     * Créer un nouveau menuElement
     * @param MenuService $menuService
     * @param TranslatorInterface $translator
     * @param Request $request
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/new-menu-element', name: 'new_menu_element', methods: ['POST'])]
    public function newMenuElement(
        MenuService         $menuService,
        TranslatorInterface $translator,
        Request             $request
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $id = $menuService->addMenuElement($data['idMenu'], $data['columP'], $data['rowP'], $data['idParent']);
        $response = $menuService->getResponseAjax($translator->trans('menu.element.new.success', domain: 'menu'));
        $response['id'] = $id;
        return $this->json($response);
    }

    /**
     * Change le parent d'un menuElement
     * @param MenuService $menuService
     * @param TranslatorInterface $translator
     * @param Request $request
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/update-parent', name: 'update_parent_menu_element', methods: ['PATCH'])]
    public function changeParent(
        MenuService         $menuService,
        TranslatorInterface $translator,
        Request             $request
    )
    {
        $data = json_decode($request->getContent(), true);
        $menuService->updateParent($data['id'], $data['columP'], $data['rowP'], $data['idParent']);

        $response = $menuService->getResponseAjax($translator->trans('menu.element.change.parent.success', domain: 'menu'));
        $response['id'] = $data['id'];
        return $this->json($response);
    }

    /**
     * Retourne la liste des parents disponible
     * @param MenuService $menuService
     * @param int|null $menuId
     * @param int|null $elementId
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/get-list-parent/{menuId}/{elementId}', name: 'list_parent_menu_element', methods: ['GET'])]
    public function getListParent(
        MenuService $menuService,
        int         $menuId = null,
        int         $elementId = null
    ): JsonResponse
    {
        $listeParent = $menuService->getListeParentByMenuElement($menuId, $elementId);

        return $this->json(['listParent' => $listeParent]);
    }

    /**
     * Réordonne les colonnes ou row des menus elements
     * @param Request $request
     * @param MenuService $menuService
     * @param TranslatorInterface $translator
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/reorder-menu-element', name: 'reorder_menu_element', methods: ['PATCH'])]
    public function reorderMenuElement(
        Request $request,
        MenuService $menuService,
        TranslatorInterface $translator,
    )
    {
        $data = json_decode($request->getContent(), true);
        $menuService->reorderMenuElement($data['data']);
        $return = $menuService->getResponseAjax($translator->trans('menu.element.reorder.success', domain: 'menu'));
        return $this->json($return);
    }
}
