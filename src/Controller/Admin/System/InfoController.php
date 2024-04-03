<?php

namespace App\Controller\Admin\System;

use App\Utils\Breadcrumb;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/{_locale}/info', name: 'admin_info_', requirements: ['_locale' => '%app.supported_locales%'])]
#[IsGranted('ROLE_SUPER_ADMIN')]
class InfoController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        $breadcrumb = [
            Breadcrumb::DOMAIN => 'info',
            Breadcrumb::BREADCRUMB => [
                'info.page.title.h1' => '#'
            ]
        ];

        return $this->render('admin/system/info/index.html.twig', [
            'breadcrumb' => $breadcrumb,
        ]);
    }
}
