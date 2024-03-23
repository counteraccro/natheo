<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Controller pour la gestion des pages
 */

namespace App\Controller\Admin\Content;

use App\Controller\Admin\AppAdminController;
use App\Entity\Admin\Content\Page\Page;
use App\Entity\Admin\System\User;
use App\Service\Admin\Content\Page\PageService;
use App\Service\Global\DateService;
use App\Utils\Breadcrumb;
use App\Utils\Content\Page\PageConst;
use App\Utils\Content\Page\PageFactory;
use App\Utils\Content\Page\PageHistory;
use App\Utils\Content\Page\PagePopulate;
use App\Utils\Content\Page\PageTranslate;
use App\Utils\System\Options\OptionUserKey;
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
     * @param PageService $pageService
     * @param int $page
     * @param int $limit
     * @return JsonResponse
     */
    #[Route('/ajax/load-grid-data/{page}/limit}', name: 'load_grid_data', methods: ['GET'])]
    public function loadGridData(PageService $pageService, int $page = 1, int $limit = 20): JsonResponse
    {
        $grid = $pageService->getAllFormatToGrid($page, $limit);
        return $this->json($grid);
    }

    /**
     * Active ou désactive une page
     * @param Page $page
     * @param PageService $pageService
     * @param TranslatorInterface $translator
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/ajax/update-disabled/{id}', name: 'update_disabled', methods: 'PUT')]
    public function updateDisabled(
        Page                $page,
        PageService         $pageService,
        TranslatorInterface $translator,
        Request             $request
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
     * @return JsonResponse
     */
    #[Route('/ajax/delete/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(
        Page                  $page,
        PageService           $pageService,
        TranslatorInterface   $translator,
        Request               $request,
        ContainerBagInterface $containerBag
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
     * Création / édition d'une page
     * @param PageService $pageService
     * @param int|null $id
     * @return Response
     */
    #[Route('/add/', name: 'add')]
    #[Route('/update/{id}', name: 'update')]
    public function add(
        PageService $pageService,
        PageTranslate $pageTranslate,
        int         $id = null
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
                'list_content' => $pageService->getAllContent()
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
                'is_unique_url_page' => $this->generateUrl('admin_page_is_unique_url_page')
            ]
        ]);
    }


    /**
     * Permet de charger le contenu du tab content
     * @param PageService $pageService
     * @param Request $request
     * @return JsonResponse
     * @throws ExceptionInterface
     */
    #[Route('/ajax/load-tab-content', name: 'load_tab_content')]
    public function loadTabContent(
        PageService $pageService,
        Request     $request): JsonResponse
    {
        $locales = $pageService->getLocales();
        $data = json_decode($request->getContent(), true);

        if ($data['id'] === null) {
            $pageFactory = new PageFactory($locales['locales']);
            $page = $pageFactory->create()->getPage();
            $page->setRender(PageConst::RENDER_1_BLOCK);
            $page->setStatus(PageConst::STATUS_DRAFT);
            $page->getPageContents()->clear();
        } else {
            $page = $pageService->findOneById(Page::class, $data['id']);
        }
        $pageArray = $pageService->convertEntityToArray($page, ['createdAt', 'updateAt', 'user']);

        return $this->json([
            'page' => $pageArray,
            'history' => $pageService->getDiffBetweenHistoryAndPage($page)
        ]);
    }

    /**
     * Sauvegarde à un instant T l'objet page dans un fichier plat au format json
     * @param ContainerBagInterface $containerBag
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/ajax/auto-save', name: 'auto_save')]
    public function autoSave(ContainerBagInterface $containerBag, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        /** @var User $user */
        $user = $this->getUser();

        try {
            $pageHistory = new PageHistory($containerBag, $user);
            $pageHistory->save($data['page']);
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            return $this->json(['status' => false, 'error' => $e->getMessage()]);
        }
        return $this->json(['status' => true]);
    }

    /**
     * Charge le tableau d'historique
     * @param ContainerBagInterface $containerBag
     * @param DateService $dateService
     * @param int $id
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/load-tab-history/{id}', name: 'load_tab_history', methods: ['GET'])]
    public function loadTabHistory(
        ContainerBagInterface $containerBag,
        DateService           $dateService,
        int                   $id = 0
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

        return $this->json([
            'page' => $page,
            'msg' => $translator->trans('page.page_history.success', ['id' => $data['row_id']], domain: 'page')
        ]);
    }

    /**
     * Permet de sauvegarder une page
     * @param Request $request
     * @param PageService $pageService
     * @param TranslatorInterface $translator
     * @return JsonResponse
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

        return $this->json([
            'msg' => $translator->trans('page.save.success', domain: 'page'),
            'url_redirect' => $this->generateUrl('admin_page_update', ['id' => $page->getId()]),
            'redirect' => $redirect
        ]);
    }

    /**
     * Permet de créer un nouveau contenu
     * @param Request $request
     * @param PageService $pageService
     * @return JsonResponse
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
     * @param Request $request
     * @param PageService $pageService
     * @return JsonResponse
     */
    #[Route('/ajax/content-by-id', name: 'liste_content_by_id')]
    public function listeContentByIdContent(Request $request, PageService $pageService): JsonResponse
    {
        return $this->json([
            'list' => [100 => 'Rendu test 1', 1000 => 'Rendu test 2'],
            'selected' => 100,
            'label' => 'label à traduire Fonction PageController::listeContentByIdContent',
            'help' => 'help à traduire'
        ]);
    }

    #[Route('/ajax/is-unique-url-page', name: 'is_unique_url_page')]
    public function isUniqueUrlPage(Request $request, PageService $pageService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        return $this->json([
            'is_unique' => $pageService->isUniqueUrl($data['url'], $data['id'])
        ]);
    }
}
