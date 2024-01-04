<?php

namespace App\Controller\Admin\Content;

use App\Controller\Admin\AppAdminController;
use App\Entity\Admin\Content\Faq\Faq;
use App\Service\Admin\Content\Faq\FaqService;
use App\Utils\Breadcrumb;
use App\Utils\System\Options\OptionUserKey;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
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
        int $id = null
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

        $translate = '';
        $locales = '';//$pageService->getLocales();

        return $this->render('admin/content/faq/add_update.html.twig', [
            'breadcrumb' => $breadcrumb,
            'translate' => $translate,
            'locales' => $locales,
            'id' => $id,
            'datas' => [
            ],
            'urls' => [
                //'load_tab_content' => $this->generateUrl('admin_page_load_tab_content'),
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
        FaqService $faqService,
        TranslatorInterface $translator,
        Request             $request): JsonResponse
    {
        return $this->json(['type' => 'success', 'msg' => '']);
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
        FaqService $faqService,
        TranslatorInterface $translator,
        Request             $request): JsonResponse
    {
        return $this->json(['type' => 'success', 'msg' => '']);
    }
}
