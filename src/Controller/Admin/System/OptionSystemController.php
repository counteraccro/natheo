<?php
/**
 * Option système
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Controller\Admin\System;

use App\Controller\Admin\AppAdminController;
use App\Enum\Admin\Global\Breadcrumb;
use App\Service\Admin\System\OptionSystemService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[
    Route(
        '/admin/{_locale}/options-system',
        name: 'admin_option-system_',
        requirements: ['_locale' => '%app.supported_locales%'],
    ),
]
#[IsGranted('ROLE_SUPER_ADMIN')]
class OptionSystemController extends AppAdminController
{
    /**
     * Point d'entrée pour les options systèmes
     * @return Response
     */
    #[Route('/change', name: 'change')]
    public function index(): Response
    {
        $breadcrumb = [
            Breadcrumb::DOMAIN->value => 'option_system',
            Breadcrumb::BREADCRUMB->value => [
                'option_system.page_title_h1' => '#',
            ],
        ];

        return $this->render('admin/system/option_system/index.html.twig', [
            'breadcrumb' => $breadcrumb,
        ]);
    }

    /**
     * Met à jour une option
     * @param Request $request
     * @param OptionSystemService $optionSystemService
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/update', name: 'ajax_update', methods: ['POST'])]
    public function update(Request $request, OptionSystemService $optionSystemService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $optionSystemService->saveValueByKee($data['key'], $data['value']);
        return $this->json($optionSystemService->getResponseAjax());
    }
}
