<?php
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Controller pour le markdown
 */
namespace App\Controller\Admin\Global;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/{_locale}/markdown', name: 'admin_markdown_', requirements: ['_locale' => '%app.supported_locales%'])]
#[IsGranted('ROLE_CONTRIBUTEUR')]
class MarkdownController extends AbstractController
{

    private const SESSION_NAME_PREVIEW = 'natheo-markdown-preview';

    /**
     * Chargement des données nécessaires au markdown
     * @return Response
     */
    #[Route('/ajax/load-datas', name: 'load-datas', methods: ['GET'])]
    public function loadDatas(): Response
    {
        return $this->json(
            [
                'media' => $this->generateUrl('admin_media_load_medias'),
                'internalLinks' => $this->generateUrl('admin_page_liste_pages_internal_link'),
                'preview' => $this->generateUrl('admin_markdown_preview'),
                'initPreview' => $this->generateUrl('admin_markdown_init_preview'),
            ]
        );
    }

    /**
     * Initialisation de la session de stockage de la preview
     * @param Request $request
     * @return Response
     */
    #[Route('/ajax/init-preview', name: 'init_preview', methods: ['POST'])]
    public function setPreviewSession(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $session = $request->getSession();
        $session->set(self::SESSION_NAME_PREVIEW, $data['value']);
        return $this->json(['success' => true]);
    }

    /**
     * Affichage de la preview
     * @param Request $request
     * @return Response
     */
    #[Route('/preview', name: 'preview', methods: ['GET'])]
    public function preview(Request $request): Response
    {
        $session = $request->getSession();
        $preview = $session->get(self::SESSION_NAME_PREVIEW);

        return $this->render('admin/global/markdown/index.html.twig', [
            'preview' => $preview,
        ]);
    }
}
