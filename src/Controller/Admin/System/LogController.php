<?php

declare(strict_types=1);
/**
 * Log
 * @author Gourdon Aymeric
 * @version 3.0
 */

namespace App\Controller\Admin\System;

use App\Controller\Admin\AppAdminController;
use App\Enum\Admin\Global\Breadcrumb;
use App\Enum\Admin\System\Options\OptionUser;
use App\Service\LoggerService;
use App\Utils\Translate\System\LogTranslate;
use InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/admin/{_locale}/log', name: 'admin_log_', requirements: ['_locale' => '%app.supported_locales%'])]
#[IsGranted('ROLE_SUPER_ADMIN')]
class LogController extends AppAdminController
{
    /**
     * Identifiant du jeton CSRF de suppression d'un fichier de log
     * @var string
     */
    const CSRF_DELETE_FILE = 'log_delete_file';

    /**
     * Point d'entrée de la gestion des logs
     * @param LogTranslate $logTranslate
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/', name: 'index')]
    public function index(LogTranslate $logTranslate): Response
    {
        $breadcrumb = [
            Breadcrumb::DOMAIN->value => 'log',
            Breadcrumb::BREADCRUMB->value => [
                'log.page_title_h1' => '#',
            ],
        ];

        return $this->render('admin/system/log/index.html.twig', [
            'breadcrumb' => $breadcrumb,
            'limit' => (int) $this->optionUserService->getValueByKey(OptionUser::OU_NB_ELEMENT->value),
            'translate' => $logTranslate->getTranslate(),
        ]);
    }

    /**
     * Retourne la liste des fichiers de logs pour la temporalité demandée
     * @param LoggerService $loggerService
     * @param TranslatorInterface $translator
     * @param string $time all, now, yesterday ou une date Y-m-d
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[
        Route(
            '/ajax/data-select-log/{time}',
            name: 'ajax_data_select_log',
            requirements: ['time' => 'all|now|yesterday|\d{4}-\d{2}-\d{2}'],
            methods: ['GET'],
        ),
    ]
    public function dataSelect(
        LoggerService $loggerService,
        TranslatorInterface $translator,
        string $time = 'all',
    ): JsonResponse {
        try {
            $files = $loggerService->getAllFiles($time);
        } catch (InvalidArgumentException) {
            return $this->json(
                ['success' => false, 'msg' => $translator->trans('log.error.time', domain: 'log')],
                Response::HTTP_BAD_REQUEST,
            );
        }
        return $this->json(['success' => true, 'files' => $files]);
    }

    /**
     * Retourne le contenu d'un fichier de log
     * @param LoggerService $loggerService
     * @param TranslatorInterface $translator
     * @param int $page
     * @param int $limit
     * @param string $file chemin relatif au dossier des logs
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[
        Route(
            '/ajax/load-log-file/{page}/{limit}/{file}',
            name: 'ajax_load_log_file',
            requirements: ['page' => '[1-9]\d*', 'limit' => '[1-9]\d*', 'file' => '.+'],
            methods: ['GET'],
        ),
    ]
    public function loadLogFile(
        LoggerService $loggerService,
        TranslatorInterface $translator,
        int $page = 1,
        int $limit = 20,
        string $file = '',
    ): JsonResponse {
        try {
            $grid = $loggerService->loadLogFile($file, $page, $limit);
        } catch (RuntimeException) {
            return $this->json(
                ['success' => false, 'msg' => $translator->trans('log.error.file', domain: 'log')],
                Response::HTTP_NOT_FOUND,
            );
        }

        return $this->json([
            'success' => true,
            'msg' => $translator->trans('log.load.success.file', ['file' => $file], domain: 'log'),
            'grid' => $grid,
        ]);
    }

    /**
     * Permet de supprimer un fichier de log
     * @param LoggerService $loggerService
     * @param TranslatorInterface $translator
     * @param Request $request
     * @param string $file chemin relatif au dossier des logs
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/delete-file/{file}', name: 'ajax_delete_file', requirements: ['file' => '.+'], methods: ['DELETE'])]
    public function deleteFile(
        LoggerService $loggerService,
        TranslatorInterface $translator,
        Request $request,
        string $file = '',
    ): JsonResponse {
        if (!$this->isCsrfTokenValid(self::CSRF_DELETE_FILE, $request->headers->get('X-CSRF-TOKEN'))) {
            return $this->json(
                ['success' => false, 'msg' => $translator->trans('log.error.csrf', domain: 'log')],
                Response::HTTP_FORBIDDEN,
            );
        }

        if (!$loggerService->deleteLog($file)) {
            return $this->json(
                ['success' => false, 'msg' => $translator->trans('log.error.file', domain: 'log')],
                Response::HTTP_NOT_FOUND,
            );
        }

        return $this->json([
            'success' => true,
            'msg' => $translator->trans('log.delete.file.success', domain: 'log'),
        ]);
    }

    /**
     * Permet de télécharger un fichier de log
     * @param LoggerService $loggerService
     * @param string $file chemin relatif au dossier des logs
     * @return BinaryFileResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/download/{file}', name: 'download_log', requirements: ['file' => '.+'], methods: ['GET'])]
    public function downloadFile(LoggerService $loggerService, string $file = ''): BinaryFileResponse
    {
        $path = $loggerService->getPathFile($file);
        if ($path === null) {
            throw $this->createNotFoundException();
        }

        return $this->file($path, basename($path), ResponseHeaderBag::DISPOSITION_ATTACHMENT);
    }
}
