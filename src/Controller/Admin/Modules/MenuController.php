<?php
/**
 * Controller qui va gérer les menus dans l'administration pour le front
 * @author Gourdon Aymeric
 * @version 1.0
 * @package App\Controller\Admin\Module;
 */
namespace App\Controller\Admin\Modules;

use App\Controller\Admin\AppAdminController;
use App\Entity\Admin\Page\Page;
use App\Entity\Modules\Menu\Menu;
use App\Entity\Modules\Menu\MenuElement;
use App\Form\Modules\Menu\MenuElementType;
use App\Form\Modules\Menu\MenuType;
use App\Service\Module\Menu\MenuService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints\Date;

#[Route('/admin/menu', name: 'admin_menu_')]
class MenuController extends AppAdminController
{
    const SESSION_KEY_FILTER = 'session_menu_filter';
    const SESSION_KEY_PAGE = 'session_menu_page';

    /**
     * Point d'entrée de la gestion des menus
     * @return Response
     */
    #[Route('/index/{page}', name: 'index')]
    public function index(int $page = 1): Response
    {

        $this->session->remove(MenuService::SESSION_KEY_MENU_ID);
        $this->session->remove(MenuService::SESSION_KEY_ELEMENT_MENU);

        $breadcrumb = [
            $this->translator->trans('admin_dashboard#Dashboard') => 'admin_dashboard_index',
            $this->translator->trans('admin_system#Modules') => '',
            $this->translator->trans('admin_menu#Gestion des menus') => '',
        ];

        $fieldSearch = [
            'name' => $this->translator->trans("admin_tag#Nom"),
        ];

        return $this->render('admin/modules/menu/index.html.twig', [
            'breadcrumb' => $breadcrumb,
            'fieldSearch' => $fieldSearch,
            'page' => $page
        ]);
    }

    /**
     * Permet de lister les menus
     * @param int $page
     */
    #[Route('/ajax/listing/{page}', name: 'ajax_listing')]
    public function listing(int $page = 1): Response
    {

        /*$this->setPageInSession(self::SESSION_KEY_PAGE, $page);
        $limit = $this->optionService->getOptionElementParPage();

        $filter = $this->getCriteriaGeneriqueSearch(self::SESSION_KEY_FILTER);

        /** @var TagRepository $routeRepo
        $tagRepo = $this->doctrine->getRepository(Tag::class);
        $listeTags = $tagRepo->listeTagPaginate($page, $limit, $filter);

        return $this->render('admin/modules/tag/ajax/ajax-listing.html.twig', [
            'listeTags' => $listeTags,
            'page' => $page,
            'limit' => $limit,
            'route' => 'admin_tag_ajax_listing',
        ]);*/
    }

    /**
     * Permet de créer / éditer un tag
     * @param Menu|null $menu
     * @return RedirectResponse|Response
     */
    #[Route('/add/', name: 'add')]
    #[Route('/edit/{id}', name: 'edit')]
    public function createUpdate(Menu $menu = null): RedirectResponse|Response
    {
        $breadcrumb = [
            $this->translator->trans('admin_dashboard#Dashboard') => 'admin_dashboard_index',
            $this->translator->trans('admin_system#Modules') => '',
            $this->translator->trans('admin_menu#Gestion des menu') => ['admin_menu_index', ['page' => $this->getPageInSession(self::SESSION_KEY_PAGE)]]
        ];

        if ($menu == null) {
            $action = 'add';
            $menu = new Menu();
            $title = $this->translator->trans('admin_menu#Ajouter un menu');
            $breadcrumb[$title] = '';
            $flashMsg = $this->translator->trans('admin_menu#Menu Créé avec succès');
            $this->session->set(MenuService::SESSION_KEY_MENU_ID, 0);
            $this->session->set(MenuService::SESSION_KEY_ELEMENT_MENU, []);
        } else {
            $action = 'edit';
            $title = $this->translator->trans('admin_menu#Edition du menu ') . '#' . $menu->getId();
            $breadcrumb[$title] = '';
            $flashMsg = $this->translator->trans('admin_menu#Menu édité avec succès');
            $this->session->set(MenuService::SESSION_KEY_MENU_ID, $menu->getId());
            $this->session->set(MenuService::SESSION_KEY_ELEMENT_MENU, $menu->getMenuElements()->toArray());
        }


        $form = $this->createForm(MenuType::class, $menu);

        $form->handleRequest($this->request->getCurrentRequest());
        if ($form->isSubmitted() && $form->isValid()) {

            /** @var Menu $menu */
            $menu = $form->getData();
            $menu->setUser($this->security->getUser());
            if($action == 'add')
            {
                $menu->setCreateOn(new \DateTime());
            }
            $menu->setEditOn(new \DateTime());


            $this->doctrine->getManager()->persist($menu);
            $this->doctrine->getManager()->flush();

            $param = [];
            $this->addFlash('success', $flashMsg);
            if ($action == 'edit') {
                $param = ['page' => $this->getPageInSession(self::SESSION_KEY_PAGE)];
            }
            return $this->redirectToRoute('admin_menu_index', $param);
        }

        return $this->render('admin/modules/menu/create-update.html.twig', [
            'breadcrumb' => $breadcrumb,
            'form' => $form->createView(),
            'title' => $title,
            'menu' => $menu,
            'action' => $action,
        ]);
    }

    /**
     * Permet de voir le rendu du menu en fonction du thème selectionné
     * @param int $mode
     * @return RedirectResponse|Response
     */
    #[Route('/ajax/exemple-render/{type}', name: 'ajax_exemple_render')]
    public function showMenuRender(string $type = 'left'): RedirectResponse|Response
    {

        $theme = $this->themeService->getCurrentTheme();
        $pathThemeModule = $this->themeService::RELATIVE_PATH_MODULES;
        $pathThemeCss = $this->themeService::RELATIVE_PATH_ASSET_CSS;

        $menuElements = $this->menuService->getMenuElements();

        return $this->render('admin/modules/menu/ajax/exemple-render.html.twig', [
            'theme' => $theme,
            'menu' => $menuElements,
            'pathThemeModule' => $pathThemeModule,
            'pathThemeCss' => $pathThemeCss,
            'type' => $type
        ]);
    }

    /**
     * Permet de voir les elements du menu
     * @return RedirectResponse|Response
     */
    #[Route('/ajax/element-menu/', name: 'ajax_menu_element')]
    public function showElementMenu(): RedirectResponse|Response
    {
        $menuElements = $this->menuService->getMenuElements();
        $tab = [];
        foreach($menuElements as $menuElement)
        {
            if($menuElement->getParent() == null)
            {
                $tab = $this->menuService->treeMenuElement($menuElement, $tab, 0, true);
            }
        }

        return $this->render('admin/modules/menu/ajax/menu-elements.html.twig', [
            'menuElements' => $tab
        ]);
    }

    /**
     * Popin d'ajout / suppression d'un element de menu
     * @param MenuElement|null $menuElement
     * @return RedirectResponse|Response
     */
    #[Route('/ajax/element-menu/add', name: 'ajax_menu_element_add')]
    #[Route('/ajax/element-menu/edit/{id}', name: 'ajax_menu_element_edit')]
    public function modalCreateUpdateMenuElement(MenuElement $menuElement = null): RedirectResponse|Response
    {

        $local = $this->request->getCurrentRequest()->getLocale();
        $menu_id = $this->session->get(MenuService::SESSION_KEY_MENU_ID);

        if($menuElement == null)
        {
            $action = 'add';
            $menuElement = new MenuElement();
            $title = $this->translator->trans('admin_menu#Ajouter un élément au menu');
            $url = $this->generateUrl('admin_menu_ajax_menu_element_add');
            $msg_loading = $this->translator->trans("admin_menu#Création de l'élément du menu en cours....");
            $menu_element_id = 0;
        }
        else {
            $action = 'edit';
            $title = $this->translator->trans('admin_menu#Edition de l\'élément') . ' #' . $menuElement->getId();
            $url = $this->generateUrl('admin_menu_ajax_menu_element_edit', ['id' => $menuElement->getId()]);
            $msg_loading = $this->translator->trans("admin_menu#Edition de l'élément du menu en cours....");
            $menu_element_id = $menuElement->getId();
        }

        $parent = $this->menuService->getListeMenuElementOrderByParentChildren();



        $form = $this->createForm(MenuElementType::class, $menuElement, [
            'attr' => [
                'id' => 'form-create-update-menu-element',
                'data-loading' => $msg_loading,
            ],
            'parent' => $parent,
            'menu_id' => $menu_id,
            'current_local' => $local
        ]);

        $form->handleRequest($this->request->getCurrentRequest());
        if ($form->isSubmitted() && $form->isValid()) {

            //var_dump($form->getData());
        }

        return $this->render('admin/modules/menu/ajax/modal-menu-elements.html.twig', [
            'title' => $title,
            'form' => $form->createView(),
            'url' => $url,
            'menu_element_id' => $menu_element_id
        ]);
    }

    /**
     * Calcul la liste des positions en fonction d'un element parent
     * @param MenuElement|null $menuElement
     * @return RedirectResponse|Response
     */
    #[Route('/ajax/position-parent/{id}', name: 'ajax_menu_position')]
    public function calculPositionByParent(MenuElement $menuElement = null): RedirectResponse|Response
    {
        return $this->render('admin/modules/menu/ajax/liste-position-parent.html.twig', [
            'menuElement' => $menuElement
        ]);
    }

    /**
     * Permet de générer l'url de la page courante
     * @param Page|null $page
     * @return JsonResponse
     */
    #[Route('/ajax/generate-url-page/{id}', name: 'ajax_menu_url_page')]
    public function getUrlByPage(Page $page = null): JsonResponse
    {

        $local = $this->request->getCurrentRequest()->getLocale();
        $frontUrl = $this->generateUrl('front_front_slug', ['slug' => 'slug'], UrlGeneratorInterface::ABSOLUTE_URL);
        $url = $this->translator->trans('admin_menu#Url') . ': ';

        if($page != null)
        {
            $slug = $page->getPageTranslations()->first()->getSlug();
            foreach ($page->getPageTranslations() as $pageTranslation)
            {
                if($pageTranslation->getLanguage() == $local)
                {
                    $slug = $pageTranslation->getSlug();
                }
            }

            $frontUrl = str_replace('slug', $slug, $frontUrl);
            return $this->json(['url' => $url . $frontUrl]);
        }
        return $this->json(['url' => '']);
    }

    /**
     * Permet de verifier si le slug du menu element est unique ou non
     * @param string $slug
     * @param MenuElement|null $menuElement
     * @return JsonResponse
     */
    #[Route('/ajax/check-slug-menu-element/{slug}/{id}', name: 'ajax_menu_element_check_slug')]
    public function checkSlugMenuElement(string $slug = "", MenuElement $menuElement = null): JsonResponse
    {
        $returnOk = ['success' => true, 'msg' => ""];
        $returnKo = ['success' => false, 'msg' => $this->translator->trans('admin_menu#Un élément de menu existe déjà avec ce slug')];

        $menuElementRepo = $this->doctrine->getRepository(MenuElement::class);
        $result = $menuElementRepo->findOneBy(['slug' => $slug]);

        if($result != null)
        {
            if($menuElement != null)
            {
                if($menuElement->getSlug() == $slug)
                {
                    return $this->json($returnOk);
                }
                return $this->json($returnKo);
            }
            return $this->json($returnKo);
        }
        return $this->json($returnOk);
    }
}
