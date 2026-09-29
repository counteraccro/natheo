<?php

declare(strict_types=1);
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
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

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
    const CSRF_TOKEN_UPDATE = 'option_system_update';

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
            'csrfTokenId' => self::CSRF_TOKEN_UPDATE,
        ]);
    }

    /**
     * Met à jour une option
     * @param Request $request
     * @param OptionSystemService $optionSystemService
     * @param TranslatorInterface $translator
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/update', name: 'ajax_update', methods: ['POST'])]
    public function update(
        Request $request,
        OptionSystemService $optionSystemService,
        TranslatorInterface $translator,
    ): JsonResponse {
        if (!$this->isCsrfTokenValid(self::CSRF_TOKEN_UPDATE, $request->headers->get('X-CSRF-TOKEN'))) {
            return $this->json(
                ['success' => false, 'msg' => $translator->trans('option_system.error.csrf', domain: 'option_system')],
                Response::HTTP_FORBIDDEN,
            );
        }

        $data = json_decode($request->getContent(), true);
        if (!is_array($data) || !is_string($data['key'] ?? null) || !is_scalar($data['value'] ?? null)) {
            return $this->json($optionSystemService->getResponseAjax(), Response::HTTP_BAD_REQUEST);
        }

        $optionSystemService->updateValueFromAdmin($data['key'], strval($data['value']));
        return $this->json($optionSystemService->getResponseAjax());
    }
}
