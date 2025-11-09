<?php
/**
 * Log
 * @author Gourdon Aymeric
 * @version 2.0
 */

namespace App\Controller\Admin\System;

use App\Controller\Admin\AppAdminController;
use App\Enum\Admin\Global\Breadcrumb;
use App\Service\LoggerService;
use App\Utils\System\Options\OptionUserKey;
use App\Utils\Translate\System\LogTranslate;
use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/admin/{_locale}/log', name: 'admin_log_', requirements: ['_locale' => '%app.supported_locales%'])]
#[IsGranted('ROLE_SUPER_ADMIN')]
class LogController extends AppAdminController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        $breadcrumb = [
            Breadcrumb::DOMAIN->value => 'log',
            Breadcrumb::BREADCRUMB->value => [
                'log.page_title_h1' => '#',
            ],
        ];

        return $this->render('admin/system/log/index.html.twig', [
            'breadcrumb' => $breadcrumb,
            'limit' => $this->optionUserService->getValueByKey(OptionUserKey::OU_NB_ELEMENT),
        ]);
    }

    /**
     * Retourne les données des listes déroulantes des filtres pour les logs
     * @param LoggerService $loggerService
     * @param LogTranslate $logTranslate
     * @param string $time
     * @return JsonResponse
     * @throws Exception
     */
    #[Route('/ajax/data-select-log/{time}', name: 'ajax_data_select_log', methods: ['GET'])]
    public function dataSelect(
        LoggerService $loggerService,
        LogTranslate $logTranslate,
        string $time = 'all',
    ): JsonResponse {
        try {
            $files = $loggerService->getAllFiles($time);
        } catch (NotFoundExceptionInterface | ContainerExceptionInterface $e) {
            die($e->getMessage());
        }
        return $this->json(['files' => $files, 'trans' => $logTranslate->getTranslate()]);
    }

    /**
     * Retourne le contenu d'un fichier de log
     * @param LoggerService $loggerService
     * @param TranslatorInterface $translator
     * @param string $file
     * @param int $page
     * @param int $limit
     * @return JsonResponse
     */
    #[Route('/ajax/load-log-file', name: 'ajax_load_log_file_empty', methods: ['GET'])]
    #[Route('/ajax/load-log-file/{file}/{page}/{limit}', name: 'ajax_load_log_file', methods: ['GET'])]
    public function loadLogFile(
        LoggerService $loggerService,
        TranslatorInterface $translator,
        string $file = '',
        int $page = 1,
        int $limit = 20,
    ): JsonResponse {
        try {
            $grid = $loggerService->loadLogFile($file, $page, $limit);
            $success = true;
            $msg = $translator->trans('log.load.success.file', ['file' => $file], domain: 'log');
        } catch (NotFoundExceptionInterface | ContainerExceptionInterface $e) {
            $success = false;
            $msg = $e->getMessage();
        } catch (Exception $e) {
            $success = false;
            $msg = $e->getMessage();
        }

        return $this->json(['success' => $success, 'msg' => $msg, 'grid' => $grid]);
    }

    /**
     * Permet de supprimer un ou plusieurs fichiers
     * @param LoggerService $loggerService
     * @param TranslatorInterface $translator
     * @param string $file
     * @return JsonResponse
     */
    #[Route('/ajax/delete-file/{file}', name: 'ajax_delete_file', methods: ['DELETE'])]
    public function deleteFile(
        LoggerService $loggerService,
        TranslatorInterface $translator,
        string $file = '',
    ): JsonResponse {
        try {
            $success = $loggerService->deleteLog($file);
            $msg = $translator->trans('log.delete.file.success', domain: 'log');
        } catch (NotFoundExceptionInterface | ContainerExceptionInterface $e) {
            $success = false;
            $msg = $e->getMessage();
        }

        return $this->json(['success' => $success, 'msg' => $msg]);
    }

    /**
     * Permet de télécharger un fichier de log
     * @param LoggerService $loggerService
     * @param string $file
     * @return BinaryFileResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/download/{file}', name: 'download_log', methods: ['GET'])]
    public function downloadFile(LoggerService $loggerService, string $file = ''): BinaryFileResponse
    {
        $path = $loggerService->getPathFile($file);
        return $this->file($path, $file);
    }
}
