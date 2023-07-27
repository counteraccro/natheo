<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Controller pour la gestion des médias
 */

namespace App\Controller\Admin\Content;

use App\Controller\Admin\AppAdminController;
use App\Entity\Admin\Content\MediaFolder;
use App\Service\Admin\Content\Media\MediaService;
use App\Utils\Breadcrumb;
use App\Utils\Utils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/{_locale}/media', name: 'admin_media_',
    requirements: ['_locale' => '%app.supported_locales%'])]
#[IsGranted('ROLE_CONTRIBUTEUR')]
class MediaController extends AppAdminController
{
    /**
     * Point d'entrée pour la médiathèque
     * @param MediaService $mediaService
     * @return Response
     */
    #[Route('/', name: 'index')]
    public function index(MediaService $mediaService): Response
    {
        $breadcrumb = [
            Breadcrumb::DOMAIN => 'media',
            Breadcrumb::BREADCRUMB => [
                'media.index.page_title_h1' => '#'
            ]
        ];

        return $this->render('admin/content/media/index.html.twig', [
            'breadcrumb' => $breadcrumb,
            'translate' => $mediaService->getMediatequeTranslation()
        ]);
    }

    /**
     * Charge une liste de média en fonction d'un dossier
     * @param Request $request
     * @param MediaService $mediaService
     * @return JsonResponse
     */
    #[Route('/ajax/load', name: 'load', methods: ['POST'])]
    public function loadMedia(Request $request, MediaService $mediaService): JsonResponse
    {
        $idFolder = $request->get('folder');

        echo $idFolder;

        $medias = $mediaService->getALlMediaAndMediaFolderByMediaFolder();
        $size = $mediaService->getFolderSize();

        return $this->json([
            'medias' => $medias,
            'currentFolder' => [
                'size' => Utils::getSizeName($size),
                'folder' => null
            ]
        ]);
    }
}
