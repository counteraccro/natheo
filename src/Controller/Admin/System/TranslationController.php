<?php

declare(strict_types=1);
/**
 * Translation controller, gestion des traductions
 * @author Gourdon Aymeric
 * @version 2.1
 */

namespace App\Controller\Admin\System;

use App\Controller\Admin\AppAdminController;
use App\Enum\Admin\Global\Breadcrumb;
use App\Service\Admin\System\TranslateService;
use App\Utils\Translate\System\TranslationTranslate;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[
    Route(
        '/admin/{_locale}/translation',
        name: 'admin_translation_',
        requirements: ['_locale' => '%app.supported_locales%'],
    ),
]
#[IsGranted('ROLE_SUPER_ADMIN')]
class TranslationController extends AppAdminController
{
    /**
     * Point d'entrée pour le module de traduction
     * @param TranslationTranslate $translationTranslate
     * @return Response
     */
    #[Route('/', name: 'index')]
    public function index(TranslationTranslate $translationTranslate): Response
    {
        $breadcrumb = [
            Breadcrumb::DOMAIN->value => 'translate',
            Breadcrumb::BREADCRUMB->value => [
                'translate.page_title_h1' => '#',
            ],
        ];

        return $this->render('admin/system/translation/index.html.twig', [
            'breadcrumb' => $breadcrumb,
            'translate' => $translationTranslate->getTranslate(),
        ]);
    }

    /**
     * Récupère la liste de langues
     * @param TranslateService $translateService
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/languages', name: 'list_languages', methods: ['GET'])]
    public function loadLanguages(TranslateService $translateService): JsonResponse
    {
        return $this->json(['languages' => $translateService->getListLanguages()]);
    }

    /**
     * Récupère la liste des fichiers de traduction en fonction de la langue
     * @param TranslateService $translateService
     * @param string $language
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[
        Route(
            '/ajax/files-translates/{language}',
            name: 'files_translate',
            requirements: ['language' => '%app.supported_locales%'],
            methods: ['GET'],
        ),
    ]
    public function loadFilesTranslates(TranslateService $translateService, string $language = 'fr'): JsonResponse
    {
        $files = $translateService->getTranslationFilesByLanguage($language);
        return $this->json(['files' => $files]);
    }

    /**
     * Récupère le fichier sélectionné
     * @param TranslateService $translateService
     * @param TranslatorInterface $translator
     * @param string $file
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/file-translate/{file}', name: 'file_translate', methods: ['GET'])]
    public function loadFileTranslate(
        TranslateService $translateService,
        TranslatorInterface $translator,
        string $file = '',
    ): JsonResponse {
        try {
            $content = $translateService->getTranslationFile($file);
        } catch (\RuntimeException) {
            return $this->json(
                ['success' => false, 'msg' => $translator->trans('translate.error.file', domain: 'translate')],
                Response::HTTP_NOT_FOUND,
            );
        }
        return $this->json(['success' => true, 'file' => $content]);
    }

    /**
     * Sauvegarde les traductions
     * @param Request $request
     * @param TranslatorInterface $translator
     * @param TranslateService $translateService
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/save-translate', name: 'save_translate', methods: ['PUT'])]
    public function saveTranslate(
        Request $request,
        TranslatorInterface $translator,
        TranslateService $translateService,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        if (!is_array($data) || !is_string($data['file'] ?? null) || !is_array($data['translates'] ?? null)) {
            return $this->json([
                'success' => false,
                'msg' => $translator->trans('translate.save.error.invalid_payload', domain: 'translate'),
                'errors' => [],
            ]);
        }

        try {
            $errors = $translateService->updateTranslateFile($data['file'], $data['translates']);
        } catch (\RuntimeException) {
            return $this->json([
                'success' => false,
                'msg' => $translator->trans('translate.error.file', domain: 'translate'),
                'errors' => [],
            ]);
        }

        if (!empty($errors)) {
            return $this->json([
                'success' => false,
                'msg' => $translator->trans('translate.save.error', ['nb' => count($errors)], domain: 'translate'),
                'errors' => $errors,
            ]);
        }

        return $this->json([
            'success' => true,
            'msg' => $translator->trans('translate.save.success', domain: 'translate'),
            'errors' => [],
        ]);
    }
}
