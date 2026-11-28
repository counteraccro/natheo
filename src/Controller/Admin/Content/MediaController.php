<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 3.0
 * Contenu de la médiathèque lu par les écrans Twig qui choisissent un média
 */

namespace App\Controller\Admin\Content;

use App\Controller\Admin\AppAdminController;
use App\Service\Admin\Content\Media\MediaPickerListService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/{_locale}/media', name: 'admin_media_', requirements: ['_locale' => '%app.supported_locales%'])]
#[IsGranted('ROLE_CONTRIBUTEUR')]
class MediaController extends AppAdminController
{
    /**
     * Sous-dossiers et médias d'un dossier, 0 pour la racine
     * @param Request $request
     * @param MediaPickerListService $mediaPickerListService
     * @param int $folder
     * @param string $order asc ou desc
     * @param string $filter name ou created_at
     * @return JsonResponse
     */
    #[Route('/ajax/load-medias/{folder}/{order}/{filter}', name: 'load_medias', methods: ['GET'])]
    public function loadMedias(
        Request $request,
        MediaPickerListService $mediaPickerListService,
        int $folder = 0,
        string $order = 'asc',
        string $filter = 'created_at',
    ): JsonResponse {
        return $this->json([
            'medias' => $mediaPickerListService->list(
                $folder,
                $request->getLocale(),
                $filter === 'name' ? 'name' : 'createdAt',
                strtoupper($order),
            ),
        ]);
    }
}
