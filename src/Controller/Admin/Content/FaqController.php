<?php

namespace App\Controller\Admin\Content;

use App\Controller\Admin\AppAdminController;
use App\Entity\Admin\Content\Faq\Faq;
use App\Service\Admin\Content\Faq\FaqService;
use App\Utils\Breadcrumb;
use App\Utils\Content\Faq\FaqFactory;
use App\Utils\System\Options\OptionUserKey;
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
     * @param Request $request
     * @param FaqService $faqService
     * @return JsonResponse
     */
    #[Route('/ajax/load-grid-data', name: 'load_grid_data', methods: ['POST'])]
    public function loadGridData(Request $request, FaqService $faqService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $grid = $faqService->getAllFormatToGrid($data['page'], $data['limit']);
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
        FaqService $faqService,
        int $id = null,
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
    #[Route('/ajax/disabled/{id}', name: 'disabled')]
    public function updateDisabled(
        Faq                 $faq,
        FaqService          $faqService,
        TranslatorInterface $translator,
        Request             $request): JsonResponse
    {

        $faq->setDisabled(!$faq->isDisabled());
        $faqService->save($faq);

        $faqTranslate = $faq->getFaqTranslationByLocale($request->getLocale());

        $msg = $translator->trans('faq.success.no.disabled', ['label' => $faqTranslate->getTitle()], 'faq');
        if ($faq->isDisabled()) {
            $msg = $translator->trans('faq.success.disabled', ['label' => $faqTranslate->getTitle()], 'faq');
        }

        return $this->json(['type' => 'success', 'msg' => $msg]);
    }

    /**
     * Supprime une FAQ
     * @param Faq $faq
     * @param FaqService $faqService
     * @param TranslatorInterface $translator
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/ajax/delete/{id}', name: 'delete')]
    public function delete(
        Faq                 $faq,
        FaqService          $faqService,
        TranslatorInterface $translator,
        Request             $request): JsonResponse
    {
        $titre = $faq->getFaqTranslationByLocale($request->getLocale())->getTitle();
        $faqService->remove($faq);

        return $this->json(['type' => 'success', 'msg' => $translator->trans(
            'faq.remove.success',
            ['label' => $titre],
            domain: 'faq'
        )]);
    }

    /**
     * Charge une FAQ en fonction de son id
     * Si pas d'Id, renvoi une nouvelle FAQ
     * @param Request $request
     * @param FaqService $faqService
     * @return JsonResponse
     * @throws ExceptionInterface
     */
    #[Route('/ajax/load-faq', name: 'load_faq')]
    public function loadFaq(Request $request, FaqService $faqService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $locales = $faqService->getLocales();

        if ($data['id'] === null) {
            $faqFactory = new FaqFactory($locales['locales']);
            $faq = $faqFactory->create()->getFaq();
        } else {
            $faq = $faqService->findOneById(Faq::class, $data['id']);
        }
        $faqArray = $faqService->convertEntityToArray($faq, ['createdAt', 'updateAt', 'user']);

        $faq = [];
        return $this->json([
            'faq' => $faqArray
        ]);
    }
}
