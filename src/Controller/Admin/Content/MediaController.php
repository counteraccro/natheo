<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Controller pour la gestion des médias
 */

namespace App\Controller\Admin\Content;

use App\Controller\Admin\AppAdminController;
use App\Entity\Admin\Content\Media\Media;
use App\Entity\Admin\Content\Media\MediaFolder;
use App\Enum\Admin\Global\Breadcrumb;
use App\Service\Admin\Content\Media\MediaFolderService;
use App\Service\Admin\Content\Media\MediaService;
use App\Service\Admin\System\OptionSystemService;
use App\Utils\Translate\Content\MediaTranslate;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/{_locale}/media', name: 'admin_media_', requirements: ['_locale' => '%app.supported_locales%'])]
#[IsGranted('ROLE_CONTRIBUTEUR')]
class MediaController extends AppAdminController
{
    /**
     * Point d'entrée pour la médiathèque
     * @param MediaTranslate $mediaTranslate
     * @return Response
     */
    #[Route('/', name: 'index')]
    public function index(MediaTranslate $mediaTranslate): Response
    {
        $breadcrumb = [
            Breadcrumb::DOMAIN->value => 'media',
            Breadcrumb::BREADCRUMB->value => [
                'media.index.page_title_h1' => '#',
            ],
        ];

        return $this->render('admin/content/media/index.html.twig', [
            'breadcrumb' => $breadcrumb,
            'translate' => $mediaTranslate->getTranslate(),
        ]);
    }

    /**
     * Charge une liste de média en fonction d'un dossier ainsi que les données nécessaires au
     * bon fonctionnement de la médiathèque
     * @param MediaService $mediaService
     * @param OptionSystemService $optionSystemService
     * @param int $folder
     * @param string $order
     * @param string $filter
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/load-medias/{folder}/{order}/{filter}', name: 'load_medias', methods: ['GET'])]
    public function loadMedias(
        MediaService $mediaService,
        OptionSystemService $optionSystemService,
        int $folder = 0,
        string $order = 'asc',
        string $filter = 'created_at',
    ): JsonResponse {
        /** @var MediaFolder $mediaFolder */

        $mediaFolder = $mediaService->findOneById(MediaFolder::class, $folder);

        $medias = $mediaService->getALlMediaAndMediaFolderByMediaFolder($mediaFolder, $filter, $order);
        $currentFolder = $mediaService->getMediaFolderInfo($mediaFolder);

        return $this->json([
            'medias' => $medias,
            'currentFolder' => $currentFolder,
            'canDelete' => $optionSystemService->canDelete(),
            'url' => [
                'saveFolder' => $this->generateUrl('admin_media_save_folder'),
                'upload' => $this->generateUrl('admin_media_upload'),
                'saveMediaEdit' => $this->generateUrl('admin_media_save_media_edit'),
                'listeMove' => $this->generateUrl('admin_media_liste_move'),
                'move' => $this->generateUrl('admin_media_move'),
                'updateTrash' => $this->generateUrl('admin_media_update_trash'),
                'nbTrash' => $this->generateUrl('admin_media_nb_trash'),
                'listTrash' => $this->generateUrl('admin_media_list_trash'),
                'remove' => $this->generateUrl('admin_media_remove'),
            ],
        ]);
    }

    /**
     * Permet de créer ou modifier un mediaFolder
     * @param Request $request
     * @param MediaFolderService $mediaFolderService
     * @param MediaTranslate $mediaTranslate
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/save-folder', name: 'save_folder', methods: ['POST'])]
    public function updateFolder(
        Request $request,
        MediaFolderService $mediaFolderService,
        MediaTranslate $mediaTranslate,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        /** @var MediaFolder $editFolder */
        $editFolder = $mediaFolderService->findOneById(MediaFolder::class, $data['editFolder']);
        $currentFolder = $mediaFolderService->findOneById(MediaFolder::class, $data['currentFolder']);

        $exist = $mediaFolderService->folderNameExistsInParent($data['name'], $currentFolder, $editFolder?->getId());
        if ($exist) {
            return $this->json([
                'result' => 'error',
                'msg' => $mediaTranslate->getFolderExistNameError(),
            ]);
        }

        $result = 'success';
        if ($editFolder === null) {
            $msg = $mediaTranslate->getFolderCreateSuccessMsg($data['name']);
            $mediaFolderService->createMediaFolder($data['name'], $currentFolder);
        } else {
            $msg = $mediaTranslate->getFolderEditSuccessMsg($data['name'], $editFolder->getName());
            $mediaFolderService->updateMediaFolder($data['name'], $editFolder);
        }

        return $this->json([
            'result' => $result,
            'msg' => $msg,
        ]);
    }

    /**
     * Upload de média
     * @param Request $request
     * @param MediaService $mediaService
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/upload-media', name: 'upload', methods: ['POST'])]
    public function upload(Request $request, MediaService $mediaService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $mediaService->uploadMediaFile($data['folder'], $data['file']);

        return $this->json([]);
    }

    /**
     * Sauvegarde le nom et la description d'un media
     * @param Request $request
     * @param MediaService $mediaService
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
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
     * @param MediaFolderService $mediaFolderService
     * @param int $id
     * @param string $type
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/liste-move/{id}/{type}', name: 'liste_move', methods: ['GET'])]
    public function listeFolderToMove(
        MediaFolderService $mediaFolderService,
        int $id = 0,
        string $type = 'folder',
    ): JsonResponse {
        $dataMove = $mediaFolderService->getAllDataForModalMove($id, $type);
        return $this->json([
            'dataMove' => [
                'id' => $id,
                'parentIid' => $dataMove['parentId'],
                'label' => $dataMove['label'],
                'type' => $type,
                'listeFolder' => $dataMove['liste'],
            ],
        ]);
    }

    /**
     * Permet de déplacer un dossier ou un média
     * @param Request $request
     * @param MediaService $mediaService
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/move', name: 'move', methods: ['POST'])]
    public function move(Request $request, MediaService $mediaService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $mediaService->move($data['id'], $data['type'], $data['idToMove']);
        return $this->json(['success' => true]);
    }

    /**
     * Met à jour la valeur trash d'un média ou d'un média folder
     * @param Request $request
     * @param MediaService $mediaService
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/update-trash', name: 'update_trash', methods: ['POST'])]
    public function updateTrash(Request $request, MediaService $mediaService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $mediaService->updateTrash($data['type'], $data['id'], $data['trash']);
        return $this->json(['success' => true]);
    }

    /**
     * Retourne le nombre d'éléments dans la corbeille
     * @param MediaService $mediaService
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/nb-trash', name: 'nb_trash', methods: ['GET'])]
    public function nbTrash(MediaService $mediaService): JsonResponse
    {
        $tab = $mediaService->getNbInTrash();
        return $this->json(['nb' => $tab['medias'] + $tab['folders']]);
    }

    /**
     * Retourne l'ensemble des éléments dans la corbeille
     * @param MediaService $mediaService
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/list-trash', name: 'list_trash', methods: ['GET'])]
    public function listTrash(MediaService $mediaService): JsonResponse
    {
        return $this->json(['mediasTrash' => $mediaService->getAllMediaAndMediaFolderInTrash()]);
    }

    /**
     * Supprime un élément de la corbeille de façon définitive
     * @param Request $request
     * @param MediaService $mediaService
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/remove', name: 'remove', methods: ['POST'])]
    public function removeTrash(Request $request, MediaService $mediaService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $mediaService->confirmTrash($data['type'], $data['id']);
        return $this->json(['success' => 'remove']);
    }
}
