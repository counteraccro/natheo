<?php
/**
 * Option système
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Controller\Admin;

use App\Service\Admin\Breadcrumb;
use App\Service\Admin\OptionSystemService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/{_locale}/options-system', name: 'admin_option-system_',
    requirements: ['_locale' => '%app.supported_locales%'])]
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
            Breadcrumb::DOMAIN => 'option_system',
            Breadcrumb::BREADCRUMB => [
                'option_system.page_title_h1' => '#'
            ]
        ];

        return $this->render('admin/option_system/index.html.twig', [
            'breadcrumb' => $breadcrumb,
        ]);
    }

    /**
     * Met à jour une option
     * @param Request $request
     * @param OptionSystemService $optionSystemService
     * @return JsonResponse
     */
    #[Route('/ajax/update', name: 'ajax_update', methods: ['POST'])]
    public function update(Request $request, OptionSystemService $optionSystemService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $optionSystemService->saveValueByKee($data['key'], $data['value']);
        return $this->json(['success' => 'true']);
    }
}
