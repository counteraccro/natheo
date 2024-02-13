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
     * @param TranslatorInterface $translator
     * @param TranslateService $translateService
     * @return JsonResponse
     */
    #[Route('/ajax/languages', name: 'list_languages', methods: ['GET'])]
    public function loadLanguages(TranslatorInterface $translator, TranslateService $translateService): JsonResponse
    {
        $tabTranslate = [
            'translate_select_language' => $translator->trans('translate.select.language', domain: 'translate'),
            'translate_select_file' => $translator->trans('translate.select.file', domain: 'translate'),
            'translate_empty_file' => $translator->trans('translate.empty.file', domain: 'translate'),
            'translate_btn_save' => $translator->trans('translate.btn.save', domain: 'translate'),
            'translate_btn_cache' => $translator->trans('translate.btn.cache', domain: 'translate'),
            'translate_info_edit' => $translator->trans('translate.info.edit', domain: 'translate'),
            'translate_link_revert' => $translator->trans('translate.link.revert', domain: 'translate'),
            'translate_nb_edit' => $translator->trans('translate.nb.edit', domain: 'translate'),
            'translate_loading' => $translator->trans('translate.loading', domain: 'translate'),
            'translate_cache_titre' => $translator->trans('translate.cache.titre', domain: 'translate'),
            'translate_cache_info' => $translator->trans('translate.cache.info', domain: 'translate'),
            'translate_cache_wait' => $translator->trans('translate.cache.wait', domain: 'translate'),
            'translate_cache_btn_close' => $translator->trans('translate.cache.btn.close', domain: 'translate'),
            'translate_cache_btn_accept' => $translator->trans('translate.cache.btn.accept', domain: 'translate'),
            'translate_cache_success' => $translator->trans('translate.cache.success', domain: 'translate'),
            'translate_confirm_leave' => $translator->trans('translate.confirm.leave', domain: 'translate'),
        ];

        try {
            $languages = $translateService->getListLanguages();
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            die($e->getMessage());
        }

        return $this->json(['trans' => $tabTranslate, 'languages' => $languages]);
    }

    /**
     * Récupère la liste des fichiers de traduction en fonction de la langue
     * @param Request $request
     * @param TranslateService $translateService
     * @return JsonResponse
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
     * @param Request $request
     * @param TranslateService $translateService
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
     * @param TranslateService $translateService
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/save-translate', name: 'save_translate', methods: ['POST'])]
    public function saveTranslate(Request $request, TranslateService $translateService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $translateService->updateTranslateFile($data['file'], $data['translates']);
        return $this->json(['success' => true]);
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
