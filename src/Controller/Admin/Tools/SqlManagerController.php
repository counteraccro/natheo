<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Controller pour la gestion des requêtes SQL
 */

namespace App\Controller\Admin\Tools;

use App\Controller\Admin\AppAdminController;
use App\Entity\Admin\Tools\SqlManager;
use App\Service\Admin\Tools\SqlManagerService;
use App\Utils\Breadcrumb;
use App\Utils\System\Options\OptionUserKey;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/admin/{_locale}/sql-manager', name: 'admin_sql_manager_',
    requirements: ['_locale' => '%app.supported_locales%'])]
#[IsGranted('ROLE_CONTRIBUTEUR')]
class SqlManagerController extends AppAdminController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        $breadcrumb = [
            Breadcrumb::DOMAIN => 'sql_manager',
            Breadcrumb::BREADCRUMB => [
                'sql_manager.index.page_title_h1' => '#'
            ]
        ];

        return $this->render('admin/tools/sql_manager/index.html.twig', [
            'breadcrumb' => $breadcrumb,
            'page' => 1,
            'limit' => $this->optionUserService->getValueByKey(OptionUserKey::OU_NB_ELEMENT)
        ]);
    }

    /**
     * Charge le tableau grid de faq en ajax
     * @param SqlManagerService $sqlManagerService
     * @param Request $request
     * @param int $page
     * @param int $limit
     * @return JsonResponse
     */
    #[Route('/ajax/load-grid-data/{page}/{limit}', name: 'load_grid_data', methods: ['GET'])]
    public function loadGridData(
        SqlManagerService $sqlManagerService,
        Request           $request,
        int               $page = 1,
        int               $limit = 20
    ): JsonResponse
    {
        $search = $request->query->get('search');
        $grid = $sqlManagerService->getAllFormatToGrid($page, $limit, $search);
        return $this->json($grid);
    }

    /**
     * Active ou désactive une requête SQL
     * @param SqlManager $sqlManager
     * @param SqlManagerService $sqlManagerService
     * @param TranslatorInterface $translator
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/ajax/disabled/{id}', name: 'disabled', methods: ['PUT'])]
    public function updateDisabled(
        SqlManager          $sqlManager,
        SqlManagerService   $sqlManagerService,
        TranslatorInterface $translator): JsonResponse
    {

        $sqlManager->setDisabled(!$sqlManager->isDisabled());

        $msg = $translator->trans('sql_manager.success.no.disabled',
            ['label' => $sqlManager->getName()], 'sql_manager');
        if ($sqlManager->isDisabled()) {
            $msg = $translator->trans('sql_manager.success.disabled',
                ['label' => $sqlManager->getName()], 'sql_manager');
        }
        $sqlManagerService->save($sqlManager);
        return $this->json($sqlManagerService->getResponseAjax($msg));
    }

    /**
     * Supprime une FAQ
     * @param SqlManager $sqlManager
     * @param SqlManagerService $sqlManagerService
     * @param TranslatorInterface $translator
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/ajax/delete/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(
        SqlManager          $sqlManager,
        SqlManagerService   $sqlManagerService,
        TranslatorInterface $translator): JsonResponse
    {
        $msg = $translator->trans('sql_manager.remove.success', ['label' => $sqlManager->getName()],
            domain: 'sql_manager');
        $sqlManagerService->remove($sqlManager);
        return $this->json($sqlManagerService->getResponseAjax($msg));
    }

    /**
     * Création / édition d'une faq
     * @param SqlManager $sqlManager
     * @param int|null $id
     * @return Response
     */
    #[Route('/add/', name: 'add')]
    #[Route('/update/{id}', name: 'update')]
    public function add(
        SqlManager $sqlManager,
        int        $id = null,
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

        return $this->render('admin/content/faq/add_update.html.twig', [
            'breadcrumb' => $breadcrumb,
            'translate' => [],
            'locales' => [],
            'id' => $id,
            'datas' => [
            ],
            'urls' => [
                'load_faq' => $this->generateUrl('admin_faq_load_faq'),
                'save' => $this->generateUrl('admin_faq_save'),
                'new_faq' => $this->generateUrl('admin_faq_new_faq'),
                'update_disabled' => $this->generateUrl('admin_faq_update_disabled'),
                'order_by_type' => $this->generateUrl('admin_faq_order_by_type'),
                'new_cat_question' => $this->generateUrl('admin_faq_new_cat_question'),
                'update_order' => $this->generateUrl('admin_faq_update_order'),
                'delete_category_question' => $this->generateUrl('admin_faq_delete_category_question')
            ]

        ]);
    }
}
