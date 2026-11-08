<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Controller pour la gestion des pages
 */

namespace App\Controller\Admin\Content;

use App\Controller\Admin\AppAdminController;
use App\Entity\Admin\Content\Page\Page;
use App\Entity\Admin\System\User;
use App\Enum\Admin\Content\Page\PageCategory;
use App\Enum\Admin\Content\Page\PageCsrfToken;
use App\Enum\Admin\Content\Page\PageMeta;
use App\Enum\Admin\Content\Page\PageRender;
use App\Enum\Admin\Content\Page\PageStatus;
use App\Enum\Admin\Content\Tag\TagCsrfToken;
use App\Enum\Admin\Global\Breadcrumb;
use App\Enum\Admin\System\Options\OptionSystem;
use App\Enum\Admin\System\Options\OptionUser;
use App\Service\Admin\Content\Comment\CommentService;
use App\Service\Admin\Content\Menu\MenuService;
use App\Service\Admin\Content\Page\PageDraftService;
use App\Service\Admin\Content\Page\PageService;
use App\Service\Admin\System\OptionSystemService;
use App\Service\Global\DateService;
use App\Factory\Admin\Content\Page\PageFactory;
use App\Populate\Admin\Content\Page\PagePopulate;
use App\Security\Voter\ContentVoter;
use App\Service\Admin\Content\Page\PageHistory;
use App\Service\Admin\System\User\PersonalData;
use App\Translate\Admin\Content\PageTranslate;
use App\Translate\Admin\Global\MarkdownEditorTranslate;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/admin/{_locale}/page', name: 'admin_page_', requirements: ['_locale' => '%app.supported_locales%'])]
#[IsGranted('ROLE_CONTRIBUTEUR')]
class PageController extends AppAdminController
{
    /**
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        $breadcrumb = [
            Breadcrumb::DOMAIN->value => 'page',
            Breadcrumb::BREADCRUMB->value => [
                'page.index.title' => '#',
            ],
        ];

        return $this->render('admin/content/page/index.html.twig', [
            'breadcrumb' => $breadcrumb,
            'page' => 1,
            'limit' => $this->optionUserService->getValueByKey(OptionUser::OU_NB_ELEMENT->value),
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
        $grid = $pageService->getAllFormatToGrid($page, $limit, $queryParams, $userId);
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
    #[Route('/ajax/update-disabled/{id}', name: 'update_disabled', requirements: ['id' => '\d+'], methods: 'PUT')]
    public function updateDisabled(
        #[MapEntity(id: 'id')] Page $page,
        PageService $pageService,
        TranslatorInterface $translator,
        Request $request,
    ): JsonResponse {
        $error =
            $this->checkCsrf(PageCsrfToken::DISABLED->value, $request) ??
            $this->checkContentAccess(ContentVoter::DISABLE, $page, 'page');
        if ($error !== null) {
            return $error;
        }
        if ($page->isLandingPage()) {
            return $this->jsonError($translator->trans('page.error.landing.disabled', domain: 'page'));
        }
        if ($page->getStatus() === PageStatus::ARCHIVED->value) {
            return $this->jsonError($translator->trans('page.error.archived', domain: 'page'));
        }

        $page->setDisabled(!$page->isDisabled());
        $pageService->save($page);

        $label = $pageService->getEscapedTitle($page, $request->getLocale());
        $msg = $translator->trans('page.success.no.disabled', ['label' => $label], 'page');
        if ($page->isDisabled()) {
            $msg = $translator->trans('page.success.disabled', ['label' => $label], 'page');
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
    #[Route('/ajax/delete/{id}', name: 'delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function delete(
        #[MapEntity(id: 'id')] Page $page,
        PageService $pageService,
        TranslatorInterface $translator,
        Request $request,
        ContainerBagInterface $containerBag,
    ): JsonResponse {
        $error =
            $this->checkCsrf(PageCsrfToken::DELETE->value, $request) ??
            $this->checkContentAccess(ContentVoter::DELETE, $page, 'page');
        if ($error !== null) {
            return $error;
        }
        if ($page->isLandingPage()) {
            return $this->jsonError($translator->trans('page.error.landing.delete', domain: 'page'));
        }

        $label = $pageService->getEscapedTitle($page, $request->getLocale());
        $id = $page->getId();

        $pageService->remove($page);
        $msg = $translator->trans('page.remove.success', ['label' => $label], domain: 'page');
        $response = $pageService->getResponseAjax($msg);

        if ($response['success']) {
            /** @var User $user */
            $user = $this->getUser();
            $pageHistory = new PageHistory($containerBag, $user);
            $pageHistory->removePageHistory($id);
        }
        return $this->json($response);
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
    #[
        Route(
            '/ajax/switch-landing-page/{id}',
            name: 'switch_Landing_page',
            requirements: ['id' => '\d+'],
            methods: ['PUT'],
        ),
    ]
    public function switchLandingPage(
        #[MapEntity(id: 'id')] Page $page,
        PageService $pageService,
        TranslatorInterface $translator,
        Request $request,
    ): JsonResponse {
        $error =
            $this->checkCsrf(PageCsrfToken::LANDING_PAGE->value, $request) ??
            $this->checkContentAccess(ContentVoter::EDIT, $page, 'page');
        if ($error !== null) {
            return $error;
        }
        if (!$pageService->canBeLandingPage($page)) {
            return $this->jsonError($translator->trans('page.error.landing.not_publish', domain: 'page'));
        }

        $pageService->switchLandingPage($page->getId());

        $label = $pageService->getEscapedTitle($page, $request->getLocale());
        $msg = $translator->trans('page.switch.landing.page.success', ['label' => $label], domain: 'page');
        return $this->json(['success' => true, 'msg' => $msg]);
    }

    /**
     * Création / édition d'une page
     * @param PageService $pageService
     * @param PageTranslate $pageTranslate
     * @param MarkdownEditorTranslate $markdownEditorTranslate
     * @param CommentService $commentService
     * @param OptionSystemService $optionSystemService
     * @param int|null $id
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/add/', name: 'add')]
    #[Route('/update/{id}', name: 'update', requirements: ['id' => '\d+'])]
    public function add(
        PageService $pageService,
        PageTranslate $pageTranslate,
        MarkdownEditorTranslate $markdownEditorTranslate,
        CommentService $commentService,
        OptionSystemService $optionSystemService,
        ?int $id = null,
    ): Response {
        if ($id !== null) {
            $page = $pageService->findOneById(Page::class, $id);
            if ($page !== null) {
                $this->denyAccessUnlessGranted(ContentVoter::EDIT, $page);
            }
        }

        $breadcrumbTitle = 'page.update.title';
        if ($id === null) {
            $breadcrumbTitle = 'page.add.title';
        }

        $breadcrumb = [
            Breadcrumb::DOMAIN->value => 'page',
            Breadcrumb::BREADCRUMB->value => [
                'page.index.title' => 'admin_page_index',
                $breadcrumbTitle => '#',
            ],
        ];

        $translate = $pageTranslate->getTranslate();
        $translate['page_content_form']['mediatheque'] = $markdownEditorTranslate->getTranslateMediateque();
        $locales = $pageService->getLocales();

        return $this->render('admin/content/page/add_update.html.twig', [
            'breadcrumb' => $breadcrumb,
            'translate' => $translate,
            'locales' => $locales,
            'id' => $id,
            'csrfTokenTag' => TagCsrfToken::TAG_BY_NAME->value,
            'csrfTokenSave' => PageCsrfToken::SAVE->value,
            'csrfTokenAutoSave' => PageCsrfToken::AUTO_SAVE->value,
            'csrfTokenPublishDraft' => PageCsrfToken::PUBLISH_DRAFT->value,
            'csrfTokenDiscardDraft' => PageCsrfToken::DISCARD_DRAFT->value,
            'datas' => [
                'list_status' => $pageService->getAllStatus(),
                'list_render' => $pageService->getAllRender(),
                'list_content' => $pageService->getAllContent(),
                'list_categories' => $pageService->getAllCategories(),
                'list_comments_status' => $commentService->getAllStatus(),
                'url_front' => $optionSystemService->getValueByKey(OptionSystem::OS_ADRESSE_SITE->value),
                'options_commentaire' => [
                    'open' => $optionSystemService->getValueByKey(OptionSystem::OS_OPEN_COMMENT->value),
                    'new_comment' => $optionSystemService->getValueByKey(
                        OptionSystem::OS_NEW_COMMENT_WAIT_VALIDATION->value,
                    ),
                ],
            ],
            'urls' => [
                'load_page' => $this->generateUrl('admin_page_load_page'),
                'load_tab_history' => $this->generateUrl('admin_page_load_tab_history'),
                'auto_save' => $this->generateUrl('admin_page_auto_save'),
                'reload_page_history' => $this->generateUrl('admin_page_reload_page_history'),
                'auto_complete_tag' => $this->generateUrl('admin_tag_search'),
                'tag_by_name' => $this->generateUrl('admin_tag_tag_by_name'),
                'save' => $this->generateUrl('admin_page_save'),
                'publish_draft' => $this->generateUrl('admin_page_publish_draft', ['id' => 0]),
                'discard_draft' => $this->generateUrl('admin_page_discard_draft', ['id' => 0]),
                'new_content' => $this->generateUrl('admin_page_new_content'),
                'liste_content_by_id' => $this->generateUrl('admin_page_liste_content_by_id'),
                'is_unique_url_page' => $this->generateUrl('admin_page_is_unique_url_page'),
                'info_render_block' => $this->generateUrl('admin_page_info_render_block'),
                'load_media' => $this->generateUrl('admin_media_load_medias'),
                'listing' => $this->generateUrl('admin_page_index'),
                'new_page' => $this->generateUrl('admin_page_add'),
            ],
        ]);
    }

    /**
     * Permet de charger le contenu du tab content ; une page publiée qui a un brouillon est chargée depuis celui-ci
     * @param PageService $pageService
     * @param PageDraftService $pageDraftService
     * @param MenuService $menuService
     * @param OptionSystemService $optionSystemService
     * @param int|null $id
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws ExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/load-page/{id}', name: 'load_page', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function loadTabContent(
        PageService $pageService,
        PageDraftService $pageDraftService,
        MenuService $menuService,
        OptionSystemService $optionSystemService,
        ?int $id = null,
    ): JsonResponse {
        $locales = $pageService->getLocales();
        if ($id === null) {
            $pageFactory = new PageFactory($locales['locales']);
            $page = $pageFactory->create()->getPage();
            $page->setRender(PageRender::ONE_BLOCK->value);
            $page->setStatus(PageStatus::DRAFT->value);
            $page->setCategory(PageCategory::PAGE->value);
            $page->setLandingPage(false);
            $page->getPageContents()->clear();

            foreach ($page->getPageMetas() as $meta) {
                $value = null;
                if ($meta->getName() === PageMeta::AUTHOR->value) {
                    $personalData = new PersonalData(
                        $this->getUser(),
                        $this->optionUserService->getValueByKey(OptionUser::OU_DEFAULT_PERSONAL_DATA_RENDER->value),
                    );
                    $value = $personalData->getPersonalData();
                }
                if ($meta->getName() === PageMeta::COPYRIGHT->value) {
                    $value = $optionSystemService->getValueByKey(OptionSystem::OS_SITE_NAME->value) . ' ' . date('Y');
                }

                if ($value !== null) {
                    foreach ($meta->getPageMetaTranslations() as $translation) {
                        $translation->setValue($value);
                    }
                }
            }
        } else {
            $page = $pageService->findOneById(Page::class, $id);
            if (!$page) {
                return $this->json(['page' => []]);
            }
            $error = $this->checkContentAccess(ContentVoter::EDIT, $page, 'page');
            if ($error !== null) {
                return $error;
            }
        }
        $pageArray = $pageService->convertEntityToArray($page, [
            'createdAt',
            'updateAt',
            'user',
            'menuElements',
            'menus',
            'comments',
            'tags',
        ]);

        // On lie les menus à la page
        if (!$page->getMenus()->isEmpty()) {
            foreach ($page->getMenus() as $menu) {
                $pageArray['menus'][] = $menu->getId();
            }
        } else {
            $pageArray['menus'] = [];
        }

        if (!$page->getTags()->isEmpty()) {
            $i = 0;
            foreach ($page->getTags() as $tag) {
                $pageArray['tags'][$i] = [
                    'id' => $tag->getId(),
                    'color' => $tag->getColor(),
                    'disabled' => $tag->isDisabled(),
                ];

                foreach ($tag->getTagTranslations() as $translation) {
                    $pageArray['tags'][$i]['tagTranslations'][] = [
                        'id' => $translation->getId(),
                        'tag' => $translation->getTag()->getId(),
                        'locale' => $translation->getLocale(),
                        'label' => $translation->getLabel(),
                    ];
                }
                $i++;
            }
        } else {
            $pageArray['tags'] = [];
        }

        $draft = $pageDraftService->getDraft($page);
        if ($draft !== null) {
            $pageArray = $draft->getData();
        }

        return $this->json([
            'page' => $pageArray,
            'history' => $pageService->getDiffBetweenHistoryAndPage($page, $draft?->getUpdateAt()),
            'menus' => $menuService->getListMenus(),
            'state' => $this->getPageState($page, $pageDraftService),
        ]);
    }

    /**
     * Sauvegarde à un instant T l'objet page dans un fichier plat au format json
     * @param ContainerBagInterface $containerBag
     * @param Request $request
     * @param PageService $pageService
     * @param PageDraftService $pageDraftService
     * @param TranslatorInterface $translator
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/auto-save', name: 'auto_save', methods: ['PUT'])]
    public function autoSave(
        ContainerBagInterface $containerBag,
        Request $request,
        PageService $pageService,
        PageDraftService $pageDraftService,
        TranslatorInterface $translator,
    ): JsonResponse {
        $error = $this->checkCsrf(PageCsrfToken::AUTO_SAVE->value, $request);
        if ($error !== null) {
            return $error;
        }

        $data = json_decode($request->getContent(), true);
        if (!is_array($data) || !is_array($data['page'] ?? null)) {
            return $this->jsonError('', Response::HTTP_BAD_REQUEST);
        }
        $pageId = $data['page']['id'] ?? null;
        $error = $this->checkPageEditAccess($pageService, $pageId);
        if ($error !== null) {
            return $error;
        }
        if (is_numeric($pageId)) {
            $page = $pageService->findOneById(Page::class, (int) $pageId);
            if ($page !== null && $pageDraftService->isReadOnly($page)) {
                return $this->jsonError($translator->trans('page.error.archived', domain: 'page'));
            }
        }

        /** @var User $user */
        $user = $this->getUser();

        try {
            $pageHistory = new PageHistory($containerBag, $user);
            $pageHistory->save($data['page']);
        } catch (NotFoundExceptionInterface | ContainerExceptionInterface $e) {
            return $this->json(['success' => false, 'msg' => $e->getMessage()]);
        }
        return $this->json(['success' => true]);
    }

    /**
     * Charge le tableau d'historique
     * @param ContainerBagInterface $containerBag
     * @param DateService $dateService
     * @param PageService $pageService
     * @param int|null $id
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/load-tab-history/{id}', name: 'load_tab_history', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function loadTabHistory(
        ContainerBagInterface $containerBag,
        DateService $dateService,
        PageService $pageService,
        ?int $id = null,
    ): JsonResponse {
        $error = $this->checkPageEditAccess($pageService, $id);
        if ($error !== null) {
            return $error;
        }

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
     * @param PageService $pageService
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/reload-page-history', name: 'reload_page_history', methods: ['POST'])]
    public function reloadPageHistory(
        ContainerBagInterface $containerBag,
        Request $request,
        TranslatorInterface $translator,
        PageService $pageService,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $pageId = is_numeric($data['id'] ?? null) ? (int) $data['id'] : null;
        $error = $this->checkPageEditAccess($pageService, $pageId);
        if ($error !== null) {
            return $error;
        }

        /** @var User $user */
        $user = $this->getUser();

        $pageHistory = new PageHistory($containerBag, $user);
        $page = $pageHistory->getPageHistoryById((int) ($data['row_id'] ?? -1), $pageId);

        $success = true;
        $msg = $translator->trans('page.page_history.success', ['id' => $data['row_id']], domain: 'page');
        if (empty($page)) {
            $success = false;
            $msg = $translator->trans('page.page_history.error', domain: 'page');
        }

        return $this->json([
            'success' => $success,
            'page' => $page,
            'msg' => $msg,
        ]);
    }

    /**
     * Permet de sauvegarder une page ; une page publiée est enregistrée dans son brouillon, la version en ligne
     * restant inchangée
     * @param Request $request
     * @param PageService $pageService
     * @param PageDraftService $pageDraftService
     * @param TranslatorInterface $translator
     * @param ContainerBagInterface $containerBag
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/save', name: 'save', methods: ['POST'])]
    public function save(
        Request $request,
        PageService $pageService,
        PageDraftService $pageDraftService,
        TranslatorInterface $translator,
        ContainerBagInterface $containerBag,
    ): JsonResponse {
        $error = $this->checkCsrf(PageCsrfToken::SAVE->value, $request);
        if ($error !== null) {
            return $error;
        }

        $data = $this->getPageData($request, $translator);
        if ($data instanceof JsonResponse) {
            return $data;
        }

        $pageFactory = new PageFactory($pageService->getLocales()['locales']);
        $page = $pageFactory->create()->getPage();
        $page->setUser($this->getUser());
        $redirect = true;
        $id = $data['id'] ?? null;
        if (is_numeric($id) && (int) $id > 0) {
            $page = $pageService->findOneById(Page::class, (int) $id);
            $error = $this->checkPageWriteAccess($page, $pageDraftService, $translator);
            if ($error !== null) {
                return $error;
            }
            $redirect = false;
        }

        /** @var User $user */
        $user = $this->getUser();

        if ($pageDraftService->isDraftMode($page)) {
            $error = $this->checkDraft($page, $data, $pageService, $pageDraftService, $translator);
            if ($error !== null) {
                return $error;
            }

            $success = $pageDraftService->saveDraft($page, $data, $user);
            (new PageHistory($containerBag, $user))->renamePageHistorySave($page->getId());

            return $this->json([
                'success' => $success,
                'msg' => $success
                    ? $translator->trans('page.save.draft_success', domain: 'page')
                    : $translator->trans('response.ajax.error'),
                'redirect' => false,
                'state' => $this->getPageState($page, $pageDraftService),
            ]);
        }

        $wasLandingPage = (bool) $page->isLandingPage();

        $pagePopulate = new PagePopulate($page, $data, $pageService);
        $page = $pagePopulate->populate()->getPage();

        $error = $this->checkPageBeforeSave(
            $page,
            $pagePopulate->getErrors(),
            $wasLandingPage,
            $pageService,
            $translator,
        );
        if ($error !== null) {
            return $error;
        }

        $pageService->save($page);

        if ($page->isLandingPage()) {
            $pageService->switchLandingPage($page->getId());
        }

        $pageHistory = new PageHistory($containerBag, $user);
        $pageHistory->renamePageHistorySave($page->getId());

        $returnArray = $pageService->getResponseAjax($translator->trans('page.save.success', domain: 'page'));
        $returnArray['url_redirect'] = $this->generateUrl('admin_page_update', ['id' => $page->getId()]);
        $returnArray['redirect'] = $redirect;
        $returnArray['state'] = $this->getPageState($page, $pageDraftService);

        return $this->json($returnArray);
    }

    /**
     * Publie les modifications d'une page publiée : les données de l'éditeur remplacent la version en ligne
     * et le brouillon est supprimé
     * @param Page $page
     * @param Request $request
     * @param PageService $pageService
     * @param PageDraftService $pageDraftService
     * @param TranslatorInterface $translator
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/publish-draft/{id}', name: 'publish_draft', requirements: ['id' => '\d+'], methods: ['PUT'])]
    public function publishDraft(
        #[MapEntity(id: 'id')] Page $page,
        Request $request,
        PageService $pageService,
        PageDraftService $pageDraftService,
        TranslatorInterface $translator,
    ): JsonResponse {
        $error =
            $this->checkCsrf(PageCsrfToken::PUBLISH_DRAFT->value, $request) ??
            $this->checkPageWriteAccess($page, $pageDraftService, $translator);
        if ($error !== null) {
            return $error;
        }
        if (!$pageDraftService->isDraftMode($page)) {
            return $this->jsonError($translator->trans('page.error.not_published', domain: 'page'));
        }

        $data = $this->getPageData($request, $translator);
        if ($data instanceof JsonResponse) {
            return $data;
        }

        $error = $this->checkDraft($page, $data, $pageService, $pageDraftService, $translator);
        if ($error !== null) {
            return $error;
        }

        if (!$pageDraftService->publishDraft($page, $data)) {
            return $this->jsonError($translator->trans('response.ajax.error'));
        }

        $label = $pageService->getEscapedTitle($page, $request->getLocale());
        return $this->json([
            'success' => true,
            'msg' => $translator->trans('page.draft.publish_success', ['label' => $label], 'page'),
            'state' => $this->getPageState($page, $pageDraftService),
        ]);
    }

    /**
     * Abandonne le brouillon d'une page publiée, la version en ligne n'est pas modifiée
     * @param Page $page
     * @param Request $request
     * @param PageDraftService $pageDraftService
     * @param TranslatorInterface $translator
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/discard-draft/{id}', name: 'discard_draft', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function discardDraft(
        #[MapEntity(id: 'id')] Page $page,
        Request $request,
        PageDraftService $pageDraftService,
        TranslatorInterface $translator,
    ): JsonResponse {
        $error =
            $this->checkCsrf(PageCsrfToken::DISCARD_DRAFT->value, $request) ??
            $this->checkPageWriteAccess($page, $pageDraftService, $translator);
        if ($error !== null) {
            return $error;
        }

        if (!$pageDraftService->discardDraft($page)) {
            return $this->jsonError($translator->trans('response.ajax.error'));
        }

        return $this->json([
            'success' => true,
            'msg' => $translator->trans('page.draft.discard_success', domain: 'page'),
        ]);
    }

    /**
     * Archive une page : elle est retirée du site et ne peut plus être modifiée
     * @param Page $page
     * @param Request $request
     * @param PageService $pageService
     * @param PageDraftService $pageDraftService
     * @param TranslatorInterface $translator
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/archive/{id}', name: 'archive', requirements: ['id' => '\d+'], methods: ['PUT'])]
    public function archive(
        #[MapEntity(id: 'id')] Page $page,
        Request $request,
        PageService $pageService,
        PageDraftService $pageDraftService,
        TranslatorInterface $translator,
    ): JsonResponse {
        $error =
            $this->checkCsrf(PageCsrfToken::ARCHIVE->value, $request) ??
            $this->checkPageWriteAccess($page, $pageDraftService, $translator);
        if ($error !== null) {
            return $error;
        }
        if ($page->isLandingPage()) {
            return $this->jsonError($translator->trans('page.error.landing.archive', domain: 'page'));
        }

        if (!$pageDraftService->archive($page)) {
            return $this->jsonError($translator->trans('response.ajax.error'));
        }

        $label = $pageService->getEscapedTitle($page, $request->getLocale());
        return $this->json([
            'success' => true,
            'msg' => $translator->trans('page.archive.success', ['label' => $label], 'page'),
        ]);
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
    #[Route('/ajax/new-content', name: 'new_content', methods: ['POST'])]
    public function newContent(Request $request, PageService $pageService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $pageFactory = new PageFactory($pageService->getLocales()['locales']);
        $pageContent = $pageFactory->newPageContent($data['type'], $data['type_id'], $data['renderBlock']);

        $pageContent = $pageService->convertEntityToArray($pageContent);

        return $this->json([
            'pageContent' => $pageContent,
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
    public function listeContentByIdContent(PageService $pageService, ?int $type = null): JsonResponse
    {
        $return = $pageService->getListeContentByType($type);
        return $this->json($return);
    }

    /**
     * Vérifie si l'url de la page est unique
     * @param Request $request
     * @param PageService $pageService
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/is-unique-url-page', name: 'is_unique_url_page', methods: ['POST'])]
    public function isUniqueUrlPage(Request $request, PageService $pageService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $pageId = is_numeric($data['id'] ?? null) ? (int) $data['id'] : null;

        return $this->json([
            'is_unique' => $pageService->isUniqueUrl(
                (string) ($data['url'] ?? ''),
                (string) ($data['locale'] ?? ''),
                $pageId,
            ),
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

    /**
     * Retourne une liste de page formaté pour MarkdownEditor lien externe
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/get-pages-internal-link/', name: 'liste_pages_internal_link', methods: ['GET'])]
    public function getListePageForInternalLinks(PageService $pageService): JsonResponse
    {
        $liste = $pageService->getFormatedListePageForInternalLink($pageService->getLocales()['current']);
        return $this->json(['pages' => $liste]);
    }

    /**
     * Vérifie le droit de modifier la page $pageId ; null (brouillon d'une nouvelle page) est toujours autorisé
     * @param PageService $pageService
     * @param mixed $pageId
     * @return JsonResponse|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function checkPageEditAccess(PageService $pageService, mixed $pageId): ?JsonResponse
    {
        if ($pageId === null || $pageId === '') {
            return null;
        }
        $page = is_numeric($pageId) ? $pageService->findOneById(Page::class, (int) $pageId) : null;
        if ($page === null) {
            return $this->jsonNotFound('page');
        }
        return $this->checkContentAccess(ContentVoter::EDIT, $page, 'page');
    }

    /**
     * Vérifie qu'une page existante peut être modifiée : page trouvée, droit d'édition, page non archivée
     * @param Page|null $page
     * @param PageDraftService $pageDraftService
     * @param TranslatorInterface $translator
     * @return JsonResponse|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function checkPageWriteAccess(
        ?Page $page,
        PageDraftService $pageDraftService,
        TranslatorInterface $translator,
    ): ?JsonResponse {
        if ($page === null) {
            return $this->jsonNotFound('page');
        }
        $error = $this->checkContentAccess(ContentVoter::EDIT, $page, 'page');
        if ($error !== null) {
            return $error;
        }
        if ($pageDraftService->isReadOnly($page)) {
            return $this->jsonError($translator->trans('page.error.archived', domain: 'page'));
        }
        return null;
    }

    /**
     * Retourne les données de la page envoyées par l'éditeur, ou une réponse d'erreur si elles sont absentes
     * @param Request $request
     * @param TranslatorInterface $translator
     * @return array|JsonResponse
     */
    private function getPageData(Request $request, TranslatorInterface $translator): array|JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!is_array($data) || !is_array($data['page'] ?? null)) {
            return $this->jsonError(
                $translator->trans('page.error.invalid_data', ['fields' => 'page'], 'page'),
                Response::HTTP_BAD_REQUEST,
            );
        }
        return $data['page'];
    }

    /**
     * Contrôle les données du brouillon d'une page publiée sur une copie de travail, sans modifier la page
     * @param Page $page
     * @param array $data
     * @param PageService $pageService
     * @param PageDraftService $pageDraftService
     * @param TranslatorInterface $translator
     * @return JsonResponse|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function checkDraft(
        Page $page,
        array $data,
        PageService $pageService,
        PageDraftService $pageDraftService,
        TranslatorInterface $translator,
    ): ?JsonResponse {
        $pagePopulate = $pageDraftService->createWorkingCopy($page, $data);
        $copy = $pagePopulate->getPage();
        $error = $this->checkPageBeforeSave(
            $copy,
            $pagePopulate->getErrors(),
            (bool) $page->isLandingPage(),
            $pageService,
            $translator,
        );
        $pageDraftService->releaseWorkingCopy($copy);
        return $error;
    }

    /**
     * État de la page pour l'éditeur : publiée, archivée (lecture seule), brouillon en cours, urls en ligne
     * @param Page $page
     * @param PageDraftService $pageDraftService
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getPageState(Page $page, PageDraftService $pageDraftService): array
    {
        $draft = $pageDraftService->getDraft($page);
        $publishedUrls = [];
        if ($pageDraftService->isDraftMode($page)) {
            foreach ($page->getPageTranslations() as $translation) {
                $publishedUrls[$translation->getLocale()] = $translation->getUrl();
            }
        }

        return [
            'is_published' => $pageDraftService->isDraftMode($page),
            'read_only' => $pageDraftService->isReadOnly($page),
            'has_draft' => $draft !== null,
            'draft_update_at' => $draft?->getUpdateAt()?->format('d/m/Y H:i'),
            'draft_user' => $draft?->getUser()?->getLogin(),
            'published_urls' => $publishedUrls,
        ];
    }

    /**
     * Contrôles avant l'enregistrement d'une page : valeurs valides, landing page publiée et toujours présente,
     * url unique dans chaque locale
     * @param Page $page
     * @param string[] $errors champs invalides relevés par PagePopulate
     * @param bool $wasLandingPage
     * @param PageService $pageService
     * @param TranslatorInterface $translator
     * @return JsonResponse|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function checkPageBeforeSave(
        Page $page,
        array $errors,
        bool $wasLandingPage,
        PageService $pageService,
        TranslatorInterface $translator,
    ): ?JsonResponse {
        if (!empty($errors)) {
            return $this->jsonError(
                $translator->trans('page.error.invalid_data', ['fields' => implode(', ', $errors)], 'page'),
                Response::HTTP_BAD_REQUEST,
            );
        }

        if ($wasLandingPage && !$page->isLandingPage()) {
            return $this->jsonError(
                $translator->trans('page.error.landing.remove', domain: 'page'),
                Response::HTTP_BAD_REQUEST,
            );
        }
        if ($page->isLandingPage() && !$pageService->canBeLandingPage($page)) {
            return $this->jsonError(
                $translator->trans('page.error.landing.not_publish', domain: 'page'),
                Response::HTTP_BAD_REQUEST,
            );
        }

        $duplicate = $pageService->getTranslationWithDuplicateUrl($page);
        if ($duplicate !== null) {
            return $this->jsonError(
                $translator->trans(
                    'page.error.url_not_unique',
                    [
                        'url' => htmlspecialchars((string) $duplicate->getUrl(), ENT_QUOTES),
                        'locale' => $duplicate->getLocale(),
                    ],
                    'page',
                ),
                Response::HTTP_BAD_REQUEST,
            );
        }
        return null;
    }
}
