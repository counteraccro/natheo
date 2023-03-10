<?php

namespace App\Controller\Admin;

use App\Service\Admin\OptionSystemService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/{_locale}/options-system', name: 'admin_option-system_', requirements: ['_locale' => '%app.supported_locales%'])]
class OptionSystemController extends AbstractController
{
    /**
     * Point d'entrée pour les options systèmes
     * @return Response
     */
    #[Route('/change', name: 'change')]
    public function index(): Response
    {
        $breadcrumb = [
            'option_system.page_title_h1' => '#'
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
