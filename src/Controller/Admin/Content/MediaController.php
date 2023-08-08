<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Controller pour la gestion des médias
 */

namespace App\Controller\Admin\Content;

use App\Controller\Admin\AppAdminController;
use App\Entity\Admin\Content\MediaFolder;
use App\Service\Admin\Content\Media\MediaFolderService;
use App\Service\Admin\Content\Media\MediaService;
use App\Utils\Breadcrumb;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

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
     * Charge une liste de média en fonction d'un dossier ainsi que les données nécessaires au
     * bon fonctionnement de la médiathèque
     * @param Request $request
     * @param MediaService $mediaService
     * @return JsonResponse
     */
    #[Route('/ajax/load-medias', name: 'load_medias', methods: ['POST'])]
    public function loadMedias(Request $request, MediaService $mediaService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        /** @var MediaFolder $mediaFolder */
        $mediaFolder = $mediaService->findOneById(MediaFolder::class, $data['folder']);

        $medias = $mediaService->getALlMediaAndMediaFolderByMediaFolder($mediaFolder, $data['filter'], $data['order']);
        $currentFolder = $mediaService->getMediaFolderInfo($mediaFolder);

        return $this->json([
            'medias' => $medias,
            'currentFolder' => $currentFolder,
            'url' => [
                'loadFolder' => $this->generateUrl('admin_media_load_folder'),
                'saveFolder' => $this->generateUrl('admin_media_save_folder')
            ]
        ]);
    }

    /**
     * Charger un mediaFolder en fonction de son id
     * @param Request $request
     * @param MediaService $mediaService
     * @return JsonResponse
     * @throws ExceptionInterface
     */
    #[Route('/ajax/load-folder', name: 'load_folder', methods: ['POST'])]
    public function loadFolder(Request $request, MediaService $mediaService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        /** @var MediaFolder $mediaFolder */
        $mediaFolder = $mediaService->findOneById(MediaFolder::class, $data['id']);

        $attributes = [];
        if (isset($data['action']) && $data['action'] === 'edit') {
            $attributes = ['medias', 'parent', 'children'];
        }

        return $this->json([
            'folder' => $mediaService->convertEntityToArray($mediaFolder, $attributes)
        ]);
    }

    /**
     * Permet de créer ou modifier un mediaFolder
     * @param Request $request
     * @param MediaFolderService $mediaFolderService
     * @param TranslatorInterface $translator
     * @return JsonResponse
     */
    #[Route('/ajax/save-folder', name: 'save_folder', methods: ['POST'])]
    public function updateFolder(
        Request             $request,
        MediaFolderService  $mediaFolderService,
        TranslatorInterface $translator
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $editFolder = $mediaFolderService->findOneById(MediaFolder::class, $data['editFolder']);
        $currentFolder = $mediaFolderService->findOneById(MediaFolder::class, $data['currentFolder']);

        $exist = $mediaFolderService->findOneBy(MediaFolder::class, 'name', $data['name']);
        if ($exist !== null) {
            return $this->json([
                    'result' => 'error',
                    'msg' => $translator->trans('media.mediatheque.folder.error.exist_name', domain: 'media')]
            );
        }

        if ($editFolder === null) {
            $mediaFolderService->createMediaFolder($data['name'], $currentFolder);
        }
        else {
            $mediaFolderService->updateMediaFolder($data['name'], $editFolder);
        }


        return $this->json([

        ]);
    }
}
