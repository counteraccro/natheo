<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Controller pour la gestion des faqs
 */

namespace App\Controller\Admin\Content;

use App\Controller\Admin\AppAdminController;
use App\Entity\Admin\Content\Faq\Faq;
use App\Enum\Admin\Global\Breadcrumb;
use App\Service\Admin\Content\Faq\FaqService;
use App\Utils\Content\Faq\FaqConst;
use App\Utils\Content\Faq\FaqFactory;
use App\Utils\Content\Faq\FaqPopulate;
use App\Utils\Content\Faq\FaqStatistiqueKey;
use App\Utils\System\Options\OptionUserKey;
use App\Utils\Translate\Content\FaqTranslate;
use App\Utils\Translate\MarkdownEditorTranslate;
use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
            'limit' => $this->optionUserService->getValueByKey(OptionUserKey::OU_NB_ELEMENT),
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
    #[Route('/update/{id}', name: 'update')]
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
                'load_faq' => $this->generateUrl('admin_faq_load_faq'),
                'save' => $this->generateUrl('admin_faq_save'),
                'new_faq' => $this->generateUrl('admin_faq_new_faq'),
            ],
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
    #[Route('/ajax/disabled/{id}', name: 'disabled', methods: ['PUT'])]
    public function updateDisabled(
        #[MapEntity(id: 'id')] Faq $faq,
        FaqService $faqService,
        TranslatorInterface $translator,
        Request $request,
    ): JsonResponse {
        $faq->setDisabled(!$faq->isDisabled());

        $faqTranslate = $faq->getFaqTranslationByLocale($request->getLocale());
        $msg = $translator->trans('faq.success.no.disabled', ['label' => $faqTranslate->getTitle()], 'faq');
        if ($faq->isDisabled()) {
            $msg = $translator->trans('faq.success.disabled', ['label' => $faqTranslate->getTitle()], 'faq');
        }
        $faqService->save($faq);
        return $this->json($faqService->getResponseAjax($msg));
    }

    /**
     * Supprime une FAQ
     * @param Faq $faq
     * @param FaqService $faqService
     * @param TranslatorInterface $translator
     * @param Request $request
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/delete/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(
        #[MapEntity(id: 'id')] Faq $faq,
        FaqService $faqService,
        TranslatorInterface $translator,
        Request $request,
    ): JsonResponse {
        $titre = $faq->getFaqTranslationByLocale($request->getLocale())->getTitle();
        $msg = $translator->trans('faq.remove.success', ['label' => $titre], domain: 'faq');

        $faqService->remove($faq);
        return $this->json($faqService->getResponseAjax($msg));
    }

    /**
     * Charge une FAQ en fonction de son id
     * Si pas d'Id, renvoi une nouvelle FAQ
     * @param FaqService $faqService
     * @param int|null $id
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws ExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/load-faq/{id}', name: 'load_faq', methods: 'GET')]
    public function loadFaq(FaqService $faqService, ?int $id = null): JsonResponse
    {
        $locales = $faqService->getLocales();

        if ($id === null) {
            $faqFactory = new FaqFactory($locales['locales']);
            $faq = $faqFactory->create()->getFaq();
        } else {
            $faq = $faqService->findOneById(Faq::class, $id);
            if ($faq === null) {
                throw new NotFoundHttpException('FAQ not found');
            }
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
        $data = json_decode($request->getContent(), true);
        /** @var Faq $faq */

        $faq = $faqService->findOneById(Faq::class, $data['faq']['id']);

        $faqPopulate = new FaqPopulate($faq, $data['faq']);
        $faq = $faqPopulate->populate()->getFaq();
        $faqService->updateFaqStatistique(
            $faq,
            FaqStatistiqueKey::KEY_STAT_NB_CATEGORIES,
            FaqConst::STATISTIQUE_ACTION_OVERWRITE,
            $faq->getFaqCategories()->count(),
        );

        $nbQuestions = 0;
        foreach ($faq->getFaqCategories() as $faqCategory) {
            $nbQuestions += $faqCategory->getFaqQuestions()->count();
        }
        $faqService->updateFaqStatistique(
            $faq,
            FaqStatistiqueKey::KEY_STAT_NB_QUESTIONS,
            FaqConst::STATISTIQUE_ACTION_OVERWRITE,
            $nbQuestions,
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
        $data = json_decode($request->getContent(), true);
        $faqFactory = new FaqFactory($faqService->getLocales()['locales']);

        $faq = $faqFactory->create()->getFaq();
        $faq->setUser($this->getUser());

        foreach ($faq->getFaqTranslations() as &$faqTranslation) {
            if ($faqTranslation->getLocale() === $faqService->getLocales()['current']) {
                $faqTranslation->setTitle($data['title']);
            } else {
                $faqTranslation->setTitle($faqTranslation->getLocale() . '-' . $data['title']);
            }
        }

        $faqService->save($faq);
        $msg = $translator->trans('faq.create.success', domain: 'faq');

        $jsonTab = $faqService->getResponseAjax($msg);
        $jsonTab['url_redirect'] = $this->generateUrl('admin_faq_update', ['id' => $faq->getId()]);
        return $this->json($jsonTab);
    }
}
