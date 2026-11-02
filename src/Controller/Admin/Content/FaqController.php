<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Controller pour la gestion des faqs
 */

namespace App\Controller\Admin\Content;

use App\Controller\Admin\AppAdminController;
use App\Entity\Admin\Content\Faq\Faq;
use App\Enum\Admin\Content\Faq\FaqCsrfToken;
use App\Enum\Admin\Content\Faq\FaqStatistiqueAction;
use App\Enum\Admin\Content\Faq\FaqStatistiqueKey;
use App\Enum\Admin\Global\Breadcrumb;
use App\Enum\Admin\System\Options\OptionUser;
use App\Service\Admin\Content\Faq\FaqService;
use App\Factory\Admin\Content\Faq\FaqFactory;
use App\Populate\Admin\Content\Faq\FaqPopulate;
use App\Security\Voter\ContentVoter;
use App\Translate\Admin\Content\FaqTranslate;
use App\Translate\Admin\Global\MarkdownEditorTranslate;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/admin/{_locale}/faq', name: 'admin_faq_', requirements: ['_locale' => '%app.supported_locales%'])]
#[IsGranted('ROLE_CONTRIBUTEUR')]
class FaqController extends AppAdminController
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
            Breadcrumb::DOMAIN->value => 'faq',
            Breadcrumb::BREADCRUMB->value => [
                'faq.index.page_title_h1' => '#',
            ],
        ];

        return $this->render('admin/content/faq/index.html.twig', [
            'breadcrumb' => $breadcrumb,
            'page' => 1,
            'limit' => $this->optionUserService->getValueByKey(OptionUser::OU_NB_ELEMENT->value),
        ]);
    }

    /**
     * Charge le tableau grid de faq en ajax
     * @param FaqService $faqService
     * @param Request $request
     * @param int $page
     * @param int $limit
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/load-grid-data/{page}/{limit}', name: 'load_grid_data', methods: ['GET'])]
    public function loadGridData(FaqService $faqService, Request $request, int $page = 1, int $limit = 20): JsonResponse
    {
        $filter = $request->query->get('filter');

        $queryParams = [
            'search' => $request->query->get('search'),
            'orderField' => $request->query->get('orderField'),
            'order' => $request->query->get('order'),
            'locale' => $request->getLocale(),
        ];

        if ($filter === self::FILTER_ME) {
            $queryParams['userId'] = $this->getUser()->getId();
        }

        $grid = $faqService->getAllFormatToGrid($page, $limit, $queryParams);
        return $this->json($grid);
    }

    /**
     * Création / édition d'une faq
     * @param FaqService $faqService
     * @param FaqTranslate $faqTranslate
     * @param MarkdownEditorTranslate $markdownEditorTranslate
     * @param int|null $id
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/add/', name: 'add')]
    #[Route('/update/{id}', name: 'update', requirements: ['id' => '\d+'])]
    public function add(
        FaqService $faqService,
        FaqTranslate $faqTranslate,
        MarkdownEditorTranslate $markdownEditorTranslate,
        ?int $id = null,
    ): Response {
        $breadcrumbTitle = 'faq.update.page_title_h1';
        if ($id === null) {
            $breadcrumbTitle = 'faq.add.page_title_h1';
        }

        $breadcrumb = [
            Breadcrumb::DOMAIN->value => 'faq',
            Breadcrumb::BREADCRUMB->value => [
                'faq.index.page_title' => 'admin_faq_index',
                $breadcrumbTitle => '#',
            ],
        ];

        $translate = $faqTranslate->getTranslate();
        $translate['editFaq']['markdown'] = $markdownEditorTranslate->getTranslate();
        $locales = $faqService->getLocales();

        $notFound = false;
        if ($id !== null) {
            $faq = $faqService->findOneById(Faq::class, $id);
            if ($faq === null) {
                $notFound = true;
            } else {
                $this->denyAccessUnlessGranted(ContentVoter::EDIT, $faq);
            }
        }

        return $this->render('admin/content/faq/add_update.html.twig', [
            'breadcrumb' => $breadcrumb,
            'translate' => $translate,
            'locales' => $locales,
            'id' => $id,
            'notFound' => $notFound,
            'datas' => [],
            'urls' => [
                'load_faq' => $id !== null ? $this->generateUrl('admin_faq_load_faq', ['id' => $id]) : '',
                'save' => $this->generateUrl('admin_faq_save'),
                'new_faq' => $this->generateUrl('admin_faq_new_faq'),
            ],
            'csrfTokenSave' => FaqCsrfToken::SAVE->value,
            'csrfTokenNew' => FaqCsrfToken::NEW->value,
        ]);
    }

    /**
     * Active ou désactive une FAQ
     * @param Faq $faq
     * @param FaqService $faqService
     * @param TranslatorInterface $translator
     * @param Request $request
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/disabled/{id}', name: 'disabled', requirements: ['id' => '\d+'], methods: ['PUT'])]
    public function updateDisabled(
        #[MapEntity(id: 'id')] Faq $faq,
        FaqService $faqService,
        TranslatorInterface $translator,
        Request $request,
    ): JsonResponse {
        $error =
            $this->checkCsrf(FaqCsrfToken::DISABLED->value, $request) ??
            $this->checkContentAccess(ContentVoter::DISABLE, $faq, 'faq');
        if ($error !== null) {
            return $error;
        }

        $faq->setDisabled(!$faq->isDisabled());
        $faqService->save($faq);

        $label = $faqService->getEscapedTitle($faq, $request->getLocale());
        $msg = $translator->trans('faq.success.no.disabled', ['label' => $label], 'faq');
        if ($faq->isDisabled()) {
            $msg = $translator->trans('faq.success.disabled', ['label' => $label], 'faq');
        }
        return $this->json($faqService->getResponseAjax($msg));
    }

    /**
     * Supprime une FAQ, refusé si elle est utilisée dans au moins une page
     * @param Faq $faq
     * @param FaqService $faqService
     * @param TranslatorInterface $translator
     * @param Request $request
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/delete/{id}', name: 'delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function delete(
        #[MapEntity(id: 'id')] Faq $faq,
        FaqService $faqService,
        TranslatorInterface $translator,
        Request $request,
    ): JsonResponse {
        $error =
            $this->checkCsrf(FaqCsrfToken::DELETE->value, $request) ??
            $this->checkContentAccess(ContentVoter::DELETE, $faq, 'faq');
        if ($error !== null) {
            return $error;
        }

        $nbPages = $faqService->countPages($faq);
        if ($nbPages > 0) {
            return $this->jsonError($translator->trans('faq.error.delete.used', ['nb' => $nbPages], 'faq'));
        }

        $label = $faqService->getEscapedTitle($faq, $request->getLocale());
        $faqService->remove($faq);

        $msg = $translator->trans('faq.remove.success', ['label' => $label], domain: 'faq');
        return $this->json($faqService->getResponseAjax($msg));
    }

    /**
     * Charge une FAQ en fonction de son id
     * @param FaqService $faqService
     * @param int $id
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws ExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/load-faq/{id}', name: 'load_faq', requirements: ['id' => '\d+'], methods: 'GET')]
    public function loadFaq(FaqService $faqService, int $id): JsonResponse
    {
        $faq = $faqService->findOneById(Faq::class, $id);
        if ($faq === null) {
            return $this->jsonNotFound('faq');
        }
        $error = $this->checkContentAccess(ContentVoter::EDIT, $faq, 'faq');
        if ($error !== null) {
            return $error;
        }
        $faqArray = $faqService->convertEntityToArray($faq, [
            'createdAt',
            'updateAt',
            'user',
            'maxRenderOrderQuestion',
            'maxRenderOrderCategory',
            'allMaxRender',
            'sortedFaqQuestion',
            'sortedFaqCategories',
        ]);

        return $this->json([
            'faq' => $faqArray,
            'max_render_order' => $faq->getAllMaxRender(),
        ]);
    }

    /**
     * Permet de sauvegarder une FAQ
     * @param Request $request
     * @param FaqService $faqService
     * @param TranslatorInterface $translator
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/save', name: 'save', methods: 'PUT')]
    public function save(Request $request, FaqService $faqService, TranslatorInterface $translator): JsonResponse
    {
        $error = $this->checkCsrf(FaqCsrfToken::SAVE->value, $request);
        if ($error !== null) {
            return $error;
        }

        $data = json_decode($request->getContent(), true);
        $id = $data['faq']['id'] ?? null;

        /** @var Faq|null $faq */
        $faq = is_numeric($id) ? $faqService->findOneById(Faq::class, (int) $id) : null;
        if ($faq === null) {
            return $this->jsonNotFound('faq');
        }
        $error = $this->checkContentAccess(ContentVoter::EDIT, $faq, 'faq');
        if ($error !== null) {
            return $error;
        }

        $faqPopulate = new FaqPopulate($faq, $data['faq']);
        $faq = $faqPopulate->populate()->getFaq();
        $faqService->updateFaqStatistique(
            $faq,
            FaqStatistiqueKey::NB_CATEGORIES->value,
            FaqStatistiqueAction::OVERWRITE,
            $faq->getFaqCategories()->count(),
            false,
        );

        $nbQuestions = 0;
        foreach ($faq->getFaqCategories() as $faqCategory) {
            $nbQuestions += $faqCategory->getFaqQuestions()->count();
        }
        $faqService->updateFaqStatistique(
            $faq,
            FaqStatistiqueKey::NB_QUESTIONS->value,
            FaqStatistiqueAction::OVERWRITE,
            $nbQuestions,
            false,
        );

        $msg = $translator->trans('faq.save.success', domain: 'faq');
        $faqService->save($faq);
        return $this->json($faqService->getResponseAjax($msg));
    }

    /** Permet de créer une nouvelle FAQ
     * @param Request $request
     * @param FaqService $faqService
     * @param TranslatorInterface $translator
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/new-faq', name: 'new_faq', methods: 'POST')]
    public function newFaq(Request $request, FaqService $faqService, TranslatorInterface $translator): JsonResponse
    {
        $error = $this->checkCsrf(FaqCsrfToken::NEW->value, $request);
        if ($error !== null) {
            return $error;
        }

        $data = json_decode($request->getContent(), true);
        $title = is_array($data) && is_string($data['title'] ?? null) ? trim($data['title']) : '';
        if ($title === '') {
            return $this->jsonError(
                $translator->trans('faq.new.input.error.empty', domain: 'faq'),
                Response::HTTP_BAD_REQUEST,
            );
        }

        $faqFactory = new FaqFactory($faqService->getLocales()['locales']);

        $faq = $faqFactory->create()->getFaq();
        $faq->setUser($this->getUser());

        foreach ($faq->getFaqTranslations() as $faqTranslation) {
            if ($faqTranslation->getLocale() === $faqService->getLocales()['current']) {
                $faqTranslation->setTitle($title);
            } else {
                $faqTranslation->setTitle($faqTranslation->getLocale() . '-' . $title);
            }
        }

        $faqService->save($faq);
        $msg = $translator->trans('faq.create.success', domain: 'faq');

        $jsonTab = $faqService->getResponseAjax($msg);
        $jsonTab['url_redirect'] = $this->generateUrl('admin_faq_update', ['id' => $faq->getId()]);
        return $this->json($jsonTab);
    }
}
