<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Controller pour la gestion des médias
 */

namespace App\Controller\Admin\Content;

use App\Controller\Admin\AppAdminController;
use App\Entity\Admin\Content\Media;
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
                'saveFolder' => $this->generateUrl('admin_media_save_folder'),
                'loadInfo' => $this->generateUrl('admin_media_load_info'),
                'upload' => $this->generateUrl('admin_media_upload'),
                'loadMediaEdit' => $this->generateUrl('admin_media_load_media_edit'),
                'saveMediaEdit' => $this->generateUrl('admin_media_save_media_edit'),
                'listeMove' => $this->generateUrl('admin_media_liste_move'),
                'move' => $this->generateUrl('admin_media_move'),
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
                'msg' => $translator->trans('media.mediatheque.folder.error.exist_name', domain: 'media')
            ]);
        }

        $result = 'success';
        if ($editFolder === null) {
            $msg = $translator->trans('media.mediatheque.folder.success', ['name' => $data['name']], domain: 'media');
            $mediaFolderService->createMediaFolder($data['name'], $currentFolder);
        } else {
            $msg = $translator->trans('media.mediatheque.folder.edit.success',
                ['new_name' => $data['name'], 'name' => $editFolder->getName()], domain: 'media');
            $mediaFolderService->updateMediaFolder($data['name'], $editFolder);
        }

        return $this->json([
            'result' => $result,
            'msg' => $msg
        ]);
    }

    /**
     * Charge les informations pour le média ou le dossier sélectionné
     * @param Request $request
     * @param MediaService $mediaService
     * @param TranslatorInterface $translator
     * @return JsonResponse
     */
    #[Route('/ajax/load-info', name: 'load_info', methods: ['POST'])]
    public function loadInfo(
        Request             $request,
        MediaService        $mediaService,
        TranslatorInterface $translator
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if ($data['type'] === 'folder') {
            $json['data'] = $mediaService->getInfoFolder($data['id']);
        } else {
            $json = $mediaService->getInfoMedia($data['id']);
        }
        $json['type'] = $data['type'];

        return $this->json($json);
    }

    /**
     * Upload de média
     * @param Request $request
     * @param MediaService $mediaService
     * @return JsonResponse
     */
    #[Route('/ajax/upload-media', name: 'upload', methods: ['POST'])]
    public function upload(Request $request, MediaService $mediaService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $mediaService->uploadMediaFile($data['folder'], $data['file']);

        return $this->json([]);
    }

    /**
     * Charge le nom et la description d'un média en fonction de son id
     * @param Request $request
     * @param MediaService $mediaService
     * @return JsonResponse
     */
    #[Route('/ajax/load-media', name: 'load_media_edit', methods: ['POST'])]
    public function loadMedia(Request $request, MediaService $mediaService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        /** @var Media $media */
        $media = $mediaService->findOneById(Media::class, $data['id']);
        return $this->json([
            'media' => [
                'id' => $media->getId(),
                'name' => $media->getTitle(),
                'description' => $media->getDescription(),
                'thumbnail' => $mediaService->getThumbnail($media)
            ]
        ]);
    }

    /**
     * Sauvegarde le nom et la description d'un media
     * @param Request $request
     * @param MediaService $mediaService
     * @return JsonResponse
     */
    #[Route('/ajax/save-media', name: 'save_media_edit', methods: ['POST'])]
    public function saveMedia(Request $request, MediaService $mediaService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        /** @var Media $media */
        $media = $mediaService->findOneById(Media::class, $data['media']['id']);

        if (empty($data['media']['name'])) {
            $data['media']['name'] = $media->getName();
        }
        $media->setTitle($data['media']['name']);
        $media->setDescription($data['media']['description']);
        $mediaService->save($media);
        return $this->json(['success' => true]);
    }

    /**
     * Retourne la liste des dossiers pour le déplacement
     * @param Request $request
     * @param MediaFolderService $mediaFolderService
     * @return JsonResponse
     */
    #[Route('/ajax/liste-move', name: 'liste_move', methods: ['POST'])]
    public function listeFolderToMove(Request $request, MediaFolderService $mediaFolderService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $dataMove = $mediaFolderService->getAllDataForModalMove($data['id'], $data['type']);

        return $this->json(['dataMove' => [
            'id' => $data['id'],
            'parentIid' => $dataMove['parentId'],
            'label' => $dataMove['label'],
            'type' => $data['type'],
            'listeFolder' => $dataMove['liste']
        ]]);
    }

    /**
     * Permet de déplacer un dossier ou un média
     * @param Request $request
     * @param MediaService $mediaService
     * @return JsonResponse
     */
    #[Route('/ajax/move', name: 'move', methods: ['POST'])]
    public function move(Request $request, MediaService $mediaService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        var_dump($data);
        return $this->json(['success' => true]);
    }
}
