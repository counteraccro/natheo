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
use App\Service\Admin\Content\Tag\TagComponentService;
use App\Service\Admin\Content\Tag\TagService;
use App\Service\Global\DateService;
use App\Utils\Breadcrumb;
use App\Utils\Content\Page\PageFactory;
use App\Utils\Content\Page\PageHistory;
use App\Utils\Content\Page\PagePopulate;
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
     * @param Request $request
     * @param PageService $pageService
     * @return JsonResponse
     */
    #[Route('/ajax/load-grid-data', name: 'load_grid_data', methods: ['POST'])]
    public function loadGridData(Request $request, PageService $pageService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $grid = $pageService->getAllFormatToGrid($data['page'], $data['limit']);
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
    #[Route('/ajax/update-disabled/{id}', name: 'update_disabled')]
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

        return $this->json(['type' => 'success', 'msg' => $msg]);
    }

    /**
     * Permet de supprimer une page
     * @param Page $page
     * @param PageService $pageService
     * @param TranslatorInterface $translator
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/ajax/delete/{id}', name: 'delete')]
    public function delete(
        Page                $page,
        PageService         $pageService,
        TranslatorInterface $translator,
        Request             $request
    ): JsonResponse
    {
        $titre = $page->getPageTranslationByLocale($request->getLocale())->getTitre();

        $pageService->remove($page);
        return $this->json(['type' => 'success', 'msg' => $translator->trans(
            'page.remove.success',
            ['label' => $titre],
            domain: 'page'
        )]);
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

        $translate = $pageService->getPageTranslation();
        $locales = $pageService->getLocales();

        return $this->render('admin/content/page/add_update.html.twig', [
            'breadcrumb' => $breadcrumb,
            'translate' => $translate,
            'locales' => $locales,
            'id' => $id,
            'datas' => [
                'list_status' => $pageService->getAllStatus(),
                'list_render' => $pageService->getAllRender()
            ],
            'urls' => [
                'load_tab_content' => $this->generateUrl('admin_page_load_tab_content'),
                'load_tab_history' => $this->generateUrl('admin_page_load_tab_history'),
                'auto_save' => $this->generateUrl('admin_page_auto_save'),
                'reload_page_history' => $this->generateUrl('admin_page_reload_page_history'),
                'auto_complete_tag' => $this->generateUrl('admin_tag_search'),
                'tag_by_name' => $this->generateUrl('admin_tag_tag_by_name'),
                'save' => $this->generateUrl('admin_page_save')
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
            $page = $pageFactory->create();
        } else {
            $page = $pageService->findOneById(Page::class, $data['id']);
        }
        $page = $pageService->convertEntityToArray($page, ['createdAt', 'updateAt', 'user']);

        return $this->json([
            'page' => $page
        ]);
    }

    /**
     * Sauvegarde à un instant T l'objet page dans un fichier plat
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
     * @param Request $request
     * @param DateService $dateService
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/load-tab-history', name: 'load_tab_history')]
    public function loadTabHistory(
        ContainerBagInterface $containerBag,
        Request               $request,
        DateService           $dateService
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        /** @var User $user */
        $user = $this->getUser();

        $pageHistory = new PageHistory($containerBag, $user);

        $id = $data['id'];
        if ($id === null) {
            $id = 0;
        }
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

        $id = $data['id'];
        if ($id === null) {
            $id = 0;
        }
        $page = $pageHistory->getPageHistoryById($id, $data['row_id']);

        return $this->json([
            'page' => $page,
            'msg' => $translator->trans('page.page_history.success', ['id' => $data['row_id']], domain: 'page')
        ]);
    }

    /**
     * Permet de sauvegarder une page
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/ajax/save', name: 'save')]
    public function save(Request $request, PageService $pageService, TranslatorInterface $translator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $pageFactory = new PageFactory($pageService->getLocales()['locales']);

        $page = $pageFactory->create();
        if(isset($data['page']['id']) && $data['page']['id'] > 0)
        {
            $page = $pageService->findOneById(Page::class, $data['page']['id']);
        }

        $pagePopulate = new PagePopulate($page, $data['page'], $pageService);
        $page = $pagePopulate->populate()->getPage();
        $pageService->save($page);

        return $this->json([
            'msg' => $translator->trans('page.save.success', domain: 'page')
        ]);
    }
}
