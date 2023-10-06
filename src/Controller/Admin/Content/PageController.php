<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Controller pour la gestion des pages
 */

namespace App\Controller\Admin\Content;

use App\Controller\Admin\AppAdminController;
use App\Entity\Admin\Content\Page\Page;
use App\Entity\Admin\Content\Page\PageTranslation;
use App\Service\Admin\Content\Page\PageService;
use App\Utils\Breadcrumb;
use App\Utils\Content\Page\PageFactory;
use App\Utils\System\Options\OptionUserKey;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
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
     * @param Request $request
     * @param Page|null $page
     * @return Response
     */
    #[Route('/add/', name: 'add')]
    #[Route('/update/{id}', name: 'update')]
    public function add(
        PageService $pageService,
        Request     $request,
        Page        $page = null
    ): Response
    {
        $breadcrumbTitle = 'page.update.page_title_h1';
        if ($page === null) {
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
        if ($page === null) {

            $pageFactory = new PageFactory($locales['locales']);
            $page = $pageFactory->create();
        }
        $page = $pageService->convertEntityToArray($page, ['createdAt', 'updateAt']);


        return $this->render('admin/content/page/add_update.html.twig', [
            'breadcrumb' => $breadcrumb,
            'translate' => $translate,
            'locales' => $locales,
            'page' => $page
        ]);
    }
}
