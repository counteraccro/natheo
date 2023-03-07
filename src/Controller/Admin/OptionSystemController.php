<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/{_locale}/options-system', name: 'option-system_', requirements: ['_locale' => '%app.supported_locales%'])]
class OptionSystemController extends AbstractController
{
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
}
