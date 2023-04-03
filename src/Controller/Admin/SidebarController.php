<?php
/**
 * Sidebar
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Controller\Admin;

use App\Entity\Admin\SidebarElement;
use App\Service\Admin\OptionUserService;
use App\Service\Admin\SidebarElementService;
use App\Utils\Debug;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/admin/{_locale}/sidebar', name: 'admin_sidebar_', requirements: ['_locale' => '%app.supported_locales%'])]
#[IsGranted('ROLE_SUPER_ADMIN')]
class SidebarController extends AppAdminController
{
    /**
     * Point d'entrée
     * @return Response
     */
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        $breadcrumb = [
            'sidebar.page_title_h1' => '#'
        ];

        return $this->render('admin/sidebar/index.html.twig', [
            'breadcrumb' => $breadcrumb,
            'page' => 1,
            'limit' => $this->optionUserService->getValueByKey(OptionUserService::OU_NB_ELEMENT)
        ]);
    }

    /**
     * Charge le tableau grid de sidebar en ajax
     * @param Request $request
     * @param SidebarElementService $sidebarElementService
     * @return JsonResponse
     */
    #[Route('/ajax/load-grid-data', name: 'load_grid_data', methods: ['POST'])]
    public function loadGridData(Request $request, SidebarElementService $sidebarElementService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $grid = $sidebarElementService->getAllFormatToGrid($data['page'], $data['limit']);
        return $this->json($grid);
    }

    /**
     * Disabled ou non un sidebarElement
     * @param SidebarElement $sidebarElement
     * @param SidebarElementService $sidebarElementService
     * @param TranslatorInterface $translator
     * @return JsonResponse
     */
    #[Route('/ajax/update-disabled/{id}', name: 'update_disabled')]
    public function updateDisabled(SidebarElement      $sidebarElement, SidebarElementService $sidebarElementService,
                                   TranslatorInterface $translator
    ): JsonResponse
    {
        $sidebarElement->setDisabled(!$sidebarElement->isDisabled());
        $sidebarElementService->save($sidebarElement);

        $msg = $translator->trans('sidebar.success.no.disabled', ['label' => '<i class="bi ' .
            $sidebarElement->getIcon() . '"></i> ' . $translator->trans($sidebarElement->getLabel())], 'sidebar'
        );
        if ($sidebarElement->isDisabled()) {
            $msg = $translator->trans('sidebar.success.disabled', ['label' => '<i class="bi ' .
                $sidebarElement->getIcon() . '"></i> ' . $translator->trans($sidebarElement->getLabel())], 'sidebar'
            );
        }

        return $this->json(['type' => 'success', 'msg' => $msg]);
    }
}
