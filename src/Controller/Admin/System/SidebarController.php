<?php
/**
 * Sidebar
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Controller\Admin\System;

use App\Controller\Admin\AppAdminController;
use App\Entity\Admin\System\SidebarElement;
use App\Enum\Admin\Global\Breadcrumb;
use App\Service\Admin\System\SidebarElementService;
use App\Utils\System\Options\OptionUserKey;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/admin/{_locale}/sidebar', name: 'admin_sidebar_', requirements: ['_locale' => '%app.supported_locales%'])]
#[IsGranted('ROLE_SUPER_ADMIN')]
class SidebarController extends AppAdminController
{
    /**
     * Point d'entrée
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        $breadcrumb = [
            Breadcrumb::DOMAIN->value => 'sidebar',
            Breadcrumb::BREADCRUMB->value => [
                'sidebar.page_title_h1' => '#',
            ],
        ];

        return $this->render('admin/system/sidebar/index.html.twig', [
            'breadcrumb' => $breadcrumb,
            'page' => 1,
            'limit' => $this->optionUserService->getValueByKey(OptionUserKey::OU_NB_ELEMENT),
        ]);
    }

    /**
     * Charge le tableau grid de sidebar en ajax
     * @param SidebarElementService $sidebarElementService
     * @param int $page
     * @param int $limit
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/load-grid-data/{page}/{limit}', name: 'load_grid_data', methods: ['GET'])]
    public function loadGridData(
        SidebarElementService $sidebarElementService,
        int $page = 1,
        int $limit = 20,
    ): JsonResponse {
        $grid = $sidebarElementService->getAllFormatToGrid($page, $limit);
        return $this->json($grid);
    }

    /**
     * Disabled ou non un sidebarElement
     * @param SidebarElement $sidebarElement
     * @param SidebarElementService $sidebarElementService
     * @param TranslatorInterface $translator
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/update-disabled/{id}', name: 'update_disabled', methods: ['PUT'])]
    public function updateDisabled(
        #[MapEntity(id: 'id')] SidebarElement $sidebarElement,
        SidebarElementService $sidebarElementService,
        TranslatorInterface $translator,
    ): JsonResponse {
        $sidebarElement->setDisabled(!$sidebarElement->isDisabled());
        $sidebarElementService->save($sidebarElement);

        $msg = $translator->trans(
            'sidebar.success.no.disabled',
            [
                'label' =>
                    '<i class="bi ' .
                    $sidebarElement->getIcon() .
                    '"></i> ' .
                    $translator->trans($sidebarElement->getLabel()),
            ],
            'sidebar',
        );
        if ($sidebarElement->isDisabled()) {
            $msg = $translator->trans(
                'sidebar.success.disabled',
                [
                    'label' =>
                        '<i class="bi ' .
                        $sidebarElement->getIcon() .
                        '"></i> ' .
                        $translator->trans($sidebarElement->getLabel()),
                ],
                'sidebar',
            );
        }

        return $this->json($sidebarElementService->getResponseAjax($msg));
    }
}
