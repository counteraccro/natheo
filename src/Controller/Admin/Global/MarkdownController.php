<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.2
 * Controller pour le markdown
 */
namespace App\Controller\Admin\Global;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/{_locale}/markdown', name: 'admin_markdown_', requirements: ['_locale' => '%app.supported_locales%'])]
#[IsGranted('ROLE_CONTRIBUTEUR')]
class MarkdownController extends AbstractController
{
    /**
     * Chargement des données nécessaires au markdown
     * @return Response
     */
    #[Route('/ajax/load-datas', name: 'load-datas', methods: ['GET'])]
    public function loadDatas(): Response
    {
        return $this->json([
            'media' => $this->generateUrl('admin_media_load_medias'),
            'internalLinks' => $this->generateUrl('admin_page_liste_pages_internal_link'),
        ]);
    }
}
