<?php
/**
 * Translation controller, gestion des traductions
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Controller\Admin\System;

use App\Controller\Admin\AppAdminController;
use App\Service\Admin\CommandService;
use App\Service\Admin\System\TranslateService;
use App\Utils\Breadcrumb;
use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/admin/{_locale}/translation', name: 'admin_translation_',
    requirements: ['_locale' => '%app.supported_locales%'])]
#[IsGranted('ROLE_SUPER_ADMIN')]
class TranslationController extends AppAdminController
{
    /**
     * Point d'entrée pour le module de traduction
     * @return Response
     */
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        $breadcrumb = [
            Breadcrumb::DOMAIN => 'translate',
            Breadcrumb::BREADCRUMB => [
                'translate.page_title_h1' => '#'
            ]
        ];

        return $this->render('admin/system/translation/index.html.twig', [
            'breadcrumb' => $breadcrumb,
        ]);
    }

    /**
     * Récupère la liste de langues
     * @param TranslateService $translateService
     * @return JsonResponse
     */
    #[Route('/ajax/languages', name: 'list_languages', methods: ['GET'])]
    public function loadLanguages(TranslateService $translateService): JsonResponse
    {

        try {
            $languages = $translateService->getListLanguages();
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            die($e->getMessage());
        }

        return $this->json(['trans' => $translateService->getTranslate(), 'languages' => $languages]);
    }

    /**
     * Récupère la liste des fichiers de traduction en fonction de la langue
     * @param TranslateService $translateService
     * @param string $language
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/files-translates/{language}', name: 'files_translate', methods: ['GET'])]
    public function loadFilesTranslates(
        TranslateService $translateService,
        string $language = 'fr'
    ): JsonResponse
    {
        $files = $translateService->getTranslationFilesByLanguage($language);
        return $this->json(['files' => $files]);
    }

    /**
     * Récupère le fichier sélectionné
     * @param TranslateService $translateService
     * @param string $file
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/file-translate/{file}', name: 'file_translate', methods: ['GET'])]
    public function loadFileTranslate(
        TranslateService $translateService,
        string $file = ''
    ): JsonResponse
    {
        $file = $translateService->getTranslationFile($file);
        return $this->json(['file' => $file]);
    }

    /**
     * Sauvegarde les traductions
     * @param Request $request
     * @param TranslatorInterface $translator
     * @param TranslateService $translateService
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     */
    #[Route('/ajax/save-translate', name: 'save_translate', methods: ['PUT'])]
    public function saveTranslate(
        Request $request,
        TranslatorInterface $translator,
        TranslateService $translateService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            $translateService->updateTranslateFile($data['file'], $data['translates']);
            $msg = $translator->trans('translate.save.success', domain: 'translate');
            $success = true;
        } catch (NotFoundExceptionInterface $e) {
            $msg = $e->getMessage();
            $success = false;
        }
        return $this->json(['success' => $success, 'msg' => $msg]);
    }

    /**
     * Permet de régénérer le cache applicatif
     * @param Request $request
     * @param CommandService $commandService
     * @return JsonResponse
     * @throws Exception
     */
    #[Route('/ajax/reload-cache', name: 'reload_cache', methods: ['POST'])]
    public function reloadCache(Request $request, CommandService $commandService): JsonResponse
    {
        $commandService->reloadCache();
        return $this->json(['success' => true]);
    }
}
