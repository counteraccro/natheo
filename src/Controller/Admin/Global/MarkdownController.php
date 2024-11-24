<?php

namespace App\Controller\Admin\Global;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/{_locale}/markdown', name: 'admin_markdown_', requirements: ['_locale' => '%app.supported_locales%'])]
#[IsGranted('ROLE_CONTRIBUTEUR')]
class MarkdownController extends AbstractController
{
    #[Route('/ajax/load-datas', name: 'load-datas', methods: ['GET'])]
    public function loadDatas(): Response
    {
        return $this->json(
            [
                'media' => $this->generateUrl('admin_media_load_medias'),
                'preview' => $this->generateUrl('admin_markdown_preview'),
            ]
        );
    }

    #[Route('/preview', name: 'preview', methods: ['GET'])]
    public function preview(): Response
    {
        return $this->render('admin/global/markdown/index.html.twig', [
            'controller_name' => 'MarkdownController',
        ]);
    }
}
