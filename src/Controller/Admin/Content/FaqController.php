<?php

namespace App\Controller\Admin\Content;

use App\Controller\Admin\AppAdminController;
use App\Entity\Admin\Content\Faq\Faq;
use App\Service\Admin\Content\Faq\FaqService;
use App\Service\Admin\MarkdownEditorService;
use App\Utils\Breadcrumb;
use App\Utils\Content\Faq\FaqFactory;
use App\Utils\Content\Faq\FaqPopulate;
use App\Utils\System\Options\OptionUserKey;
use Doctrine\DBAL\Exception;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/admin/{_locale}/faq', name: 'admin_faq_', requirements: ['_locale' => '%app.supported_locales%'])]
#[IsGranted('ROLE_CONTRIBUTEUR')]
class FaqController extends AppAdminController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        $breadcrumb = [
            Breadcrumb::DOMAIN => 'faq',
            Breadcrumb::BREADCRUMB => [
                'faq.index.page_title_h1' => '#'
            ]
        ];

        return $this->render('admin/content/faq/index.html.twig', [
            'breadcrumb' => $breadcrumb,
            'page' => 1,
            'limit' => $this->optionUserService->getValueByKey(OptionUserKey::OU_NB_ELEMENT)
        ]);
    }

    /**
     * Charge le tableau grid de tag en ajax
     * @param FaqService $faqService
     * @param int $page
     * @param int $limit
     * @return JsonResponse
     */
    #[Route('/ajax/load-grid-data/{page}/{limit}', name: 'load_grid_data', methods: ['GET'])]
    public function loadGridData(FaqService $faqService, int $page = 1, int $limit = 20): JsonResponse
    {
        $grid = $faqService->getAllFormatToGrid($page, $limit);
        return $this->json($grid);
    }

    /**
     * Création / édition d'une faq
     * @param int|null $id
     * @return Response
     */
    #[Route('/add/', name: 'add')]
    #[Route('/update/{id}', name: 'update')]
    public function add(
        FaqService            $faqService,
        MarkdownEditorService $markdownEditorService,
        int                   $id = null,
    ): Response
    {
        $breadcrumbTitle = 'faq.update.page_title_h1';
        if ($id === null) {
            $breadcrumbTitle = 'faq.add.page_title_h1';
        }

        $breadcrumb = [
            Breadcrumb::DOMAIN => 'faq',
            Breadcrumb::BREADCRUMB => [
                'faq.index.page_title' => 'admin_faq_index',
                $breadcrumbTitle => '#'
            ]
        ];

        $translate = $faqService->getFaqTranslation();
        $translate['markdown'] = $markdownEditorService->getTranslate();
        $locales = $faqService->getLocales();

        return $this->render('admin/content/faq/add_update.html.twig', [
            'breadcrumb' => $breadcrumb,
            'translate' => $translate,
            'locales' => $locales,
            'id' => $id,
            'datas' => [
            ],
            'urls' => [
                'load_faq' => $this->generateUrl('admin_faq_load_faq'),
                'save' => $this->generateUrl('admin_faq_save'),
                'new_faq' => $this->generateUrl('admin_faq_new_faq'),
                'update_disabled' =>  $this->generateUrl('admin_faq_update_disabled'),
            ]
        ]);
    }

    /**
     * Active ou désactive une FAQ
     * @param Faq $faq
     * @param FaqService $faqService
     * @param TranslatorInterface $translator
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/ajax/disabled/{id}', name: 'disabled', methods: ['PUT'])]
    public function updateDisabled(
        Faq                 $faq,
        FaqService          $faqService,
        TranslatorInterface $translator,
        Request             $request): JsonResponse
    {

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
     */
    #[Route('/ajax/delete/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(
        Faq                 $faq,
        FaqService          $faqService,
        TranslatorInterface $translator,
        Request             $request): JsonResponse
    {
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
     * @throws ExceptionInterface
     */
    #[Route('/ajax/load-faq/{id}', name: 'load_faq', methods: 'GET')]
    public function loadFaq(FaqService $faqService, int $id = null): JsonResponse
    {
        $locales = $faqService->getLocales();

        if ($id === null) {
            $faqFactory = new FaqFactory($locales['locales']);
            $faq = $faqFactory->create()->getFaq();
        } else {
            $faq = $faqService->findOneById(Faq::class, $id);
        }
        $faqArray = $faqService->convertEntityToArray($faq, [
            'createdAt', 'updateAt', 'user', 'maxRenderOrderQuestion', 'maxRenderOrderCategory', 'allMaxRender']);

        return $this->json([
            'faq' => $faqArray,
            'max_render_order' => $faq->getAllMaxRender()
        ]);
    }

    /**
     * Permet de sauvegarder une FAQ
     * @param Request $request
     * @param FaqService $faqService
     * @param TranslatorInterface $translator
     * @return JsonResponse
     */
    #[Route('/ajax/save', name: 'save', methods: 'PUT')]
    public function save(Request $request, FaqService $faqService, TranslatorInterface $translator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        /** @var Faq $faq */
        $faq = $faqService->findOneById(Faq::class, $data['faq']['id']);

        $faqPopulate = new FaqPopulate($faq, $data['faq']);
        $faq = $faqPopulate->populate()->getFaq();

        $msg = $translator->trans('faq.save.success', domain: 'faq');
        $faqService->save($faq);
        return $this->json($faqService->getResponseAjax($msg));
    }

    /** Permet de créer une nouvelle FAQ
     * @param Request $request
     * @param FaqService $faqService
     * @param TranslatorInterface $translator
     * @return JsonResponse
     */
    #[Route('/ajax/new-faq', name: 'new_faq', methods: 'POST')]
    public function newFaq(Request             $request,
                           FaqService          $faqService,
                           TranslatorInterface $translator): JsonResponse
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

    #[Route('/ajax/update-disabled', name: 'update_disabled', methods: 'PUT')]
    public function updateDisabledCatQuestion(
        Request             $request,
        FaqService          $faqService,
        TranslatorInterface $translator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        return $this->json([]);
    }
}
