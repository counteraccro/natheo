<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Controller pour la gestion des menus
 */

namespace App\Controller\Admin\Content;

use App\Controller\Admin\AppAdminController;
use App\Entity\Admin\Content\Menu\Menu;
use App\Entity\Admin\Content\Menu\MenuElement;
use App\Enum\Admin\Content\Menu\MenuLinkTarget;
use App\Enum\Admin\Global\Breadcrumb;
use App\Service\Admin\Content\Menu\MenuService;
use App\Service\Admin\Content\Page\PageService;
use App\Service\Admin\System\OptionSystemService;
use App\Utils\Content\Menu\MenuConvertToArray;
use App\Utils\Content\Menu\MenuPopulate;
use App\Utils\System\Options\OptionSystemKey;
use App\Utils\System\Options\OptionUserKey;
use App\Utils\Translate\Content\MenuTranslate;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/admin/{_locale}/menu', name: 'admin_menu_', requirements: ['_locale' => '%app.supported_locales%'])]
#[IsGranted('ROLE_CONTRIBUTEUR')]
class MenuController extends AppAdminController
{
    /**
     * Index listing des tags
     * @param MenuService $menuService
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/', name: 'index')]
    public function index(MenuService $menuService): Response
    {
        $breadcrumb = [
            Breadcrumb::DOMAIN->value => 'menu',
            Breadcrumb::BREADCRUMB->value => [
                'menu.index.page_title_h1' => '#',
            ],
        ];

        $errorDefault = $menuService->getErrorDefaultTypeMenu();

        return $this->render('admin/content/menu/index.html.twig', [
            'breadcrumb' => $breadcrumb,
            'page' => 1,
            'limit' => $this->optionUserService->getValueByKey(OptionUserKey::OU_NB_ELEMENT),
            'errorDefault' => $errorDefault,
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
        Request $request,
        int $page = 1,
        int $limit = 20,
    ): JsonResponse {
        $queryParams = [
            'search' => $request->query->get('search'),
            'orderField' => $request->query->get('orderField'),
            'order' => $request->query->get('order'),
            'locale' => $request->getLocale(),
        ];

        $filter = $request->query->get('filter');
        $userId = null;
        if ($filter === self::FILTER_ME) {
            $userId = $this->getUser()->getId();
        }

        $grid = $menuService->getAllFormatToGrid($page, $limit, $queryParams, $userId);
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
        #[MapEntity(id: 'id')] Menu $menu,
        MenuService $menuService,
        TranslatorInterface $translator,
    ): JsonResponse {
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
        #[MapEntity(id: 'id')] Menu $menu,
        MenuService $menuService,
        TranslatorInterface $translator,
    ): JsonResponse {
        $titre = $menu->getName();
        $menuService->remove($menu);

        $msg = $translator->trans('menu.remove.success', ['label' => $titre], domain: 'menu');
        return $this->json($menuService->getResponseAjax($msg));
    }

    /**
     * Permet de switcher un menu en mode défaut
     * @param Menu $menu
     * @param MenuService $menuService
     * @param TranslatorInterface $translator
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/switch-default/{id}', name: 'switch_default', methods: ['PUT'])]
    public function switchDefaultMenu(
        #[MapEntity(id: 'id')] Menu $menu,
        MenuService $menuService,
        TranslatorInterface $translator,
    ): JsonResponse {
        $menu->setDefaultMenu(true);
        $menuService->save($menu);
        $menuService->switchDefaultMenuToFalse($menu->getId(), $menu->getPosition());
        $msg = $translator->trans('menu.success.switch.default', ['label' => $menu->getName()], 'menu');
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
    public function add(MenuService $menuService, MenuTranslate $menuTranslate, ?int $id = null): Response
    {
        $breadcrumbTitle = 'menu.update.menu_title_h1';
        if ($id === null) {
            $breadcrumbTitle = 'menu.add.menu_title_h1';
        } else {
            $menu = $menuService->findOneById(Menu::class, $id);
            if ($menu === null) {
                return $this->redirectToRoute('admin_menu_index');
            }
        }

        $breadcrumb = [
            Breadcrumb::DOMAIN->value => 'menu',
            Breadcrumb::BREADCRUMB->value => [
                'menu.index.page_title' => 'admin_menu_index',
                $breadcrumbTitle => '#',
            ],
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
                'list_type' => $menuService->getListType(),
                'list_target_value' => [
                    'blank' => MenuLinkTarget::LINK_TARGET_BLANK->value,
                    'self' => MenuLinkTarget::LINK_TARGET_SELF->value,
                ],
            ],
            'urls' => [
                'load_menu' => $this->generateUrl('admin_menu_load_menu'),
                'save_menu' => $this->generateUrl('admin_menu_save_menu'),
                'list_parent_menu_element' => $this->generateUrl('admin_menu_list_parent_menu_element'),
            ],
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
        MenuConvertToArray $menuJson,
        OptionSystemService $optionSystemService,
        PageService $pageService,
        ?int $id = null,
    ): JsonResponse {
        $menu = $menuJson->convertToArray($id);
        $name = $optionSystemService->getValueByKey(OptionSystemKey::OS_SITE_NAME);
        $logo = $optionSystemService->getValueByKey(OptionSystemKey::OS_LOGO_SITE);
        $urlSite = $optionSystemService->getValueByKey(OptionSystemKey::OS_ADRESSE_SITE);
        $allElement = [];
        if (isset($menu['allElements'])) {
            $allElement = $menu['allElements'];
            unset($menu['allElements']);
        }

        return $this->json([
            'menu' => $menu,
            'data' => [
                'all_elements' => $allElement,
                'name' => $name,
                'logo' => $logo,
                'url_site' => $urlSite,
                'pages' => $pageService->getAllTitleAndUrlPage(),
            ],
        ]);
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
    public function save(Request $request, MenuService $menuService, TranslatorInterface $translator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['menu']['name'])) {
            return $this->json([
                'success' => false,
                'msg' => $translator->trans('menu.new.error.no.name', domain: 'menu'),
            ]);
        }

        if (empty($data['menu']['menuElements'])) {
            return $this->json([
                'success' => false,
                'msg' => $translator->trans('menu.new.error.no.menu.element', domain: 'menu'),
            ]);
        }

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

        if ($menu->isDefaultMenu()) {
            $menuService->switchDefaultMenuToFalse($menu->getId(), $menu->getPosition());
        }

        $response = $menuService->getResponseAjax($msgSuccess);
        $response['redirect'] = $redirect;
        $response['url'] = $this->generateUrl('admin_menu_update', ['id' => $menu->getId()]);
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
    public function getListParent(MenuService $menuService, ?int $menuId = null, ?int $elementId = null): JsonResponse
    {
        $listeParent = $menuService->getListeParentByMenuElement($menuId, $elementId);

        return $this->json(['listParent' => $listeParent]);
    }
}
