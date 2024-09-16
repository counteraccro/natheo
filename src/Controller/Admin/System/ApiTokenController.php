<?php

namespace App\Controller\Admin\System;

use App\Controller\Admin\AppAdminController;
use App\Utils\Breadcrumb;
use App\Utils\System\Options\OptionUserKey;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/{_locale}/api-token', name: 'admin_api_token_', requirements: ['_locale' => '%app.supported_locales%'])]
#[IsGranted('ROLE_SUPER_ADMIN')]
class ApiTokenController extends AppAdminController
{
    /**
     * Point d'entrée pour la gestion des tokens
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        $breadcrumb = [
            Breadcrumb::DOMAIN => 'mail',
            Breadcrumb::BREADCRUMB => [
                'mail.page_title_h1' => '#'
            ]
        ];

        return $this->render('admin/system/api_token/index.html.twig', [
            'breadcrumb' => $breadcrumb,
            'page' => 1,
            'limit' => $this->optionUserService->getValueByKey(OptionUserKey::OU_NB_ELEMENT),
        ]);
    }
}
