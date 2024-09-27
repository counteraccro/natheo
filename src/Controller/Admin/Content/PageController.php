<?php
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Controller pour la gestion des pages
 */

namespace App\Controller\Admin\Content;

use App\Controller\Admin\AppAdminController;
use App\Entity\Admin\Content\Page\Page;
use App\Entity\Admin\System\User;
use App\Service\Admin\Content\Menu\MenuService;
use App\Service\Admin\Content\Page\PageService;
use App\Service\Global\DateService;
use App\Utils\Breadcrumb;
use App\Utils\Content\Page\PageConst;
use App\Utils\Content\Page\PageFactory;
use App\Utils\Content\Page\PageHistory;
use App\Utils\Content\Page\PagePopulate;
use App\Utils\System\Options\OptionUserKey;
use App\Utils\Translate\Content\PageTranslate;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

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
     * Charge le tableau grid de page en ajax
     * @param PageService $pageService
     * @param Request $request
     * @param int $page
     * @param int $limit
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/load-grid-data/{page}/{limit}', name: 'load_grid_data', methods: ['GET'])]
    public function loadGridData(
        PageService $pageService,
        Request     $request,
        int         $page = 1,
        int         $limit = 20
    ): JsonResponse
    {
        $search = $request->query->get('search');
        $grid = $pageService->getAllFormatToGrid($page, $limit, $search);
        return $this->json($grid);
    }

    /**
     * Active ou désactive une page
     * @param Page $page
     * @param PageService $pageService
     * @param TranslatorInterface $translator
     * @param Request $request
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/update-disabled/{id}', name: 'update_disabled', methods: 'PUT')]
    public function updateDisabled(
        #[MapEntity(id: 'id')] Page $page,
        PageService                 $pageService,
        TranslatorInterface         $translator,
        Request                     $request
    ): JsonResponse
    {
        $page->setDisabled(!$page->isDisabled());
        $pageService->save($page);

        $pageTranslation = $page->getPageTranslationByLocale($request->getLocale());

        $msg = $translator->trans('page.success.no.disabled', ['label' => $pageTranslation->getTitre()], 'page');
        if ($page->isDisabled()) {
            $msg = $translator->trans('page.success.disabled', ['label' => $pageTranslation->getTitre()], 'page');
        }
        return $this->json($pageService->getResponseAjax($msg));
    }

    /**
     * Permet de supprimer une page
     * @param Page $page
     * @param PageService $pageService
     * @param TranslatorInterface $translator
     * @param Request $request
     * @param ContainerBagInterface $containerBag
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/delete/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(
        #[MapEntity(id: 'id')] Page $page,
        PageService                 $pageService,
        TranslatorInterface         $translator,
        Request                     $request,
        ContainerBagInterface       $containerBag
    ): JsonResponse
    {
        $titre = $page->getPageTranslationByLocale($request->getLocale())->getTitre();
        $id = $page->getId();
        /** @var User $user */
        $user = $this->getUser();

        $pageService->remove($page);

        $pageHistory = new PageHistory($containerBag, $user);
        $pageHistory->removePageHistory($id);

        $msg = $translator->trans('page.remove.success', ['label' => $titre], domain: 'page');
        return $this->json($pageService->getResponseAjax($msg));
    }

    /**
     * Met à true le landing page et force toutes les autres pages à false
     * @param Page $page
     * @param PageService $pageService
     * @param TranslatorInterface $translator
     * @param Request $request
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/switch-landing-page/{id}', name: 'switch_Landing_page', methods: ['PUT'])]
    public function switchLandingPage(
        #[MapEntity(id: 'id')] Page $page,
        PageService                 $pageService,
        TranslatorInterface         $translator,
        Request                     $request,
    ): jsonResponse
    {
        $titre = $page->getPageTranslationByLocale($request->getLocale())->getTitre();

        $page->setLandingPage(true);
        $pageService->save($page);
        $pageService->switchLandingPage($page->getId());

        $msg = $translator->trans('page.switch.landing.page.success', ['label' => $titre], domain: 'page');
        return $this->json($pageService->getResponseAjax($msg));
    }


    /**
     * Création / édition d'une page
     * @param PageService $pageService
     * @param PageTranslate $pageTranslate
     * @param int|null $id
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/add/', name: 'add')]
    #[Route('/update/{id}', name: 'update')]
    public function add(
        PageService   $pageService,
        PageTranslate $pageTranslate,
        int           $id = null
    ): Response
    {
        $breadcrumbTitle = 'page.update.page_title_h1';
        if ($id === null) {
            $breadcrumbTitle = 'page.add.page_title_h1';
        }

        $breadcrumb = [
            Breadcrumb::DOMAIN => 'page',
            Breadcrumb::BREADCRUMB => [
                'page.index.page_title' => 'admin_page_index',
                $breadcrumbTitle => '#'
            ]
        ];

        $translate = $pageTranslate->getTranslate();
        $locales = $pageService->getLocales();

        return $this->render('admin/content/page/add_update.html.twig', [
            'breadcrumb' => $breadcrumb,
            'translate' => $translate,
            'locales' => $locales,
            'id' => $id,
            'datas' => [
                'list_status' => $pageService->getAllStatus(),
                'list_render' => $pageService->getAllRender(),
                'list_content' => $pageService->getAllContent(),
                'list_categories' => $pageService->getAllCategories()
            ],
            'urls' => [
                'load_tab_content' => $this->generateUrl('admin_page_load_tab_content'),
                'load_tab_history' => $this->generateUrl('admin_page_load_tab_history'),
                'auto_save' => $this->generateUrl('admin_page_auto_save'),
                'reload_page_history' => $this->generateUrl('admin_page_reload_page_history'),
                'auto_complete_tag' => $this->generateUrl('admin_tag_search'),
                'tag_by_name' => $this->generateUrl('admin_tag_tag_by_name'),
                'save' => $this->generateUrl('admin_page_save'),
                'new_content' => $this->generateUrl('admin_page_new_content'),
                'liste_content_by_id' => $this->generateUrl('admin_page_liste_content_by_id'),
                'is_unique_url_page' => $this->generateUrl('admin_page_is_unique_url_page'),
                'info_render_block' => $this->generateUrl('admin_page_info_render_block')
            ]
        ]);
    }


    /**
     * Permet de charger le contenu du tab content
     * @param PageService $pageService
     * @param MenuService $menuService
     * @param int|null $id
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws ExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/load-tab-content/{id}', name: 'load_tab_content', methods: ['GET'])]
    public function loadTabContent(
        PageService $pageService,
        MenuService $menuService,
        int         $id = null,
    ): JsonResponse
    {
        $locales = $pageService->getLocales();
        if ($id === null) {
            $pageFactory = new PageFactory($locales['locales']);
            $page = $pageFactory->create()->getPage();
            $page->setRender(PageConst::RENDER_1_BLOCK);
            $page->setStatus(PageConst::STATUS_DRAFT);
            $page->setCategory(PageConst::PAGE_CATEGORY_PAGE);
            $page->getPageContents()->clear();
        } else {
            $page = $pageService->findOneById(Page::class, $id);
        }
        $pageArray = $pageService->convertEntityToArray($page, ['createdAt', 'updateAt', 'user', 'menuElements', 'menus']);

        // On lie les menus à la page
        if (!$page->getMenus()->isEmpty()) {
            foreach ($page->getMenus() as $menu) {
                $pageArray['menus'][] = $menu->getId();
            }
        } else {
            $pageArray['menus'][] = "-1";
        }

        return $this->json([
            'page' => $pageArray,
            'history' => $pageService->getDiffBetweenHistoryAndPage($page),
            'menus' => $menuService->getListMenus()
        ]);
    }

    /**
     * Sauvegarde à un instant T l'objet page dans un fichier plat au format json
     * @param ContainerBagInterface $containerBag
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/ajax/auto-save', name: 'auto_save', methods: ['PUT'])]
    public function autoSave(ContainerBagInterface $containerBag, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        /** @var User $user */
        $user = $this->getUser();

        try {
            $pageHistory = new PageHistory($containerBag, $user);
            $pageHistory->save($data['page']);
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            return $this->json(['success' => false, 'msg' => $e->getMessage()]);
        }
        return $this->json(['success' => true]);
    }

    /**
     * Charge le tableau d'historique
     * @param ContainerBagInterface $containerBag
     * @param DateService $dateService
     * @param int|null $id
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/load-tab-history/{id}', name: 'load_tab_history', methods: ['GET'])]
    public function loadTabHistory(
        ContainerBagInterface $containerBag,
        DateService           $dateService,
        int                   $id = null
    ): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $pageHistory = new PageHistory($containerBag, $user);
        $history = $pageHistory->getHistory($id);

        foreach ($history as &$hist) {
            $dateDiff = new \DateTime();
            $dateDiff->setTimestamp($hist['time']);
            $hist['time'] = $dateService->getStringDiffDate($dateDiff, short: true);
        }
        return $this->json(['history' => $history]);
    }

    /**
     * Retourne une page en fonction de l'id de l'historique
     * @param ContainerBagInterface $containerBag
     * @param Request $request
     * @param TranslatorInterface $translator
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/reload-page-history', name: 'reload_page_history')]
    public function reloadPageHistory(
        ContainerBagInterface $containerBag,
        Request               $request,
        TranslatorInterface   $translator,
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        /** @var User $user */
        $user = $this->getUser();

        $pageHistory = new PageHistory($containerBag, $user);
        $page = $pageHistory->getPageHistoryById($data['row_id'], $data['id']);

        $success = true;
        $msg = $translator->trans('page.page_history.success', ['id' => $data['row_id']], domain: 'page');
        if (empty($page)) {
            $success = false;
            $msg = $translator->trans('page.page_history.error', domain: 'page');
        }

        return $this->json([
            'success' => $success,
            'page' => $page,
            'msg' => $msg
        ]);
    }

    /**
     * Permet de sauvegarder une page
     * @param Request $request
     * @param PageService $pageService
     * @param TranslatorInterface $translator
     * @param ContainerBagInterface $containerBag
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/save', name: 'save')]
    public function save(
        Request               $request,
        PageService           $pageService,
        TranslatorInterface   $translator,
        ContainerBagInterface $containerBag
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $pageFactory = new PageFactory($pageService->getLocales()['locales']);

        $page = $pageFactory->create()->getPage();
        $page->setUser($this->getUser());
        $redirect = true;
        if (isset($data['page']['id']) && $data['page']['id'] > 0) {
            $page = $pageService->findOneById(Page::class, $data['page']['id']);
            $redirect = false;
        }

        $pagePopulate = new PagePopulate($page, $data['page'], $pageService);
        $page = $pagePopulate->populate()->getPage();
        $pageService->save($page);

        /** @var User $user */
        $user = $this->getUser();
        $pageHistory = new PageHistory($containerBag, $user);
        $pageHistory->renamePageHistorySave($page->getId());

        $returnArray = $pageService->getResponseAjax($translator->trans('page.save.success', domain: 'page'));
        $returnArray['url_redirect'] = $this->generateUrl('admin_page_update', ['id' => $page->getId()]);
        $returnArray['redirect'] = $redirect;

        return $this->json($returnArray);
    }

    /**
     * Permet de créer un nouveau contenu
     * @param Request $request
     * @param PageService $pageService
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws ExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/new-content', name: 'new_content')]
    public function newContent(Request $request, PageService $pageService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $pageFactory = new PageFactory($pageService->getLocales()['locales']);
        $pageContent = $pageFactory->newPageContent($data['type'], $data['type_id'], $data['renderBlock']);

        $pageContent = $pageService->convertEntityToArray($pageContent);

        return $this->json([
            'pageContent' => $pageContent
        ]);
    }

    /**
     * Retourne une liste de contenu en fonction d'un id content
     * @param PageService $pageService
     * @param int|null $type
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/content-by-id/{type}', name: 'liste_content_by_id', methods: ['GET'])]
    public function listeContentByIdContent(PageService $pageService, int $type = null): JsonResponse
    {
        $return = $pageService->getListeContentByType($type);
        return $this->json($return);
    }

    /**
     * Vérifie si l'url de la page est unique
     * @param Request $request
     * @param PageService $pageService
     * @return JsonResponse
     */
    #[Route('/ajax/is-unique-url-page', name: 'is_unique_url_page', methods: ['POST'])]
    public function isUniqueUrlPage(Request $request, PageService $pageService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        return $this->json([
            'is_unique' => $pageService->isUniqueUrl($data['url'], $data['id'])
        ]);
    }

    /**
     * Retourne les infos du block
     * @param PageService $pageService
     * @param int $type
     * @param int $typeId
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/get-info-render-bock/{type}/{typeId}', name: 'info_render_block', methods: ['GET'])]
    public function getInfoRenderBlock(PageService $pageService, int $type = 0, int $typeId = 0): JsonResponse
    {
        $return = $pageService->getInfoContentByTypeAndTypeId($type, $typeId);
        return $this->json($return);
    }
}
