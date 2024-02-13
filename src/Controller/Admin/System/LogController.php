<?php
/**
 * Log
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Controller\Admin\System;

use App\Controller\Admin\AppAdminController;
use App\Service\LoggerService;
use App\Utils\Breadcrumb;
use App\Utils\System\Options\OptionUserKey;
use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
            Breadcrumb::DOMAIN => 'log',
            Breadcrumb::BREADCRUMB => [
                'log.page_title_h1' => '#'
            ]
        ];

        return $this->render('admin/system/log/index.html.twig', [
            'breadcrumb' => $breadcrumb,
            'limit' => $this->optionUserService->getValueByKey(OptionUserKey::OU_NB_ELEMENT),
        ]);
    }

    /**
     * Retourne les données des listes déroulantes des filtres pour les logs
     * @param Request $request
     * @param LoggerService $loggerService
     * @param TranslatorInterface $translator
     * @return JsonResponse
     * @throws Exception
     */
    #[Route('/ajax/data-select-log/{time}', name: 'ajax_data_select_log', methods: ['GET'])]
    public function dataSelect(
        LoggerService       $loggerService,
        TranslatorInterface $translator,
        string              $time = 'all',
    ):
    JsonResponse
    {
        $tabTranslate = [
            'log_select_file' => $translator->trans('log.select-file', domain: 'log'),
            'log_select_time_all' => $translator->trans('log.select-time.all', domain: 'log'),
            'log_select_time_now' => $translator->trans('log.select-time.now', domain: 'log'),
            'log_select_time_yesterday' => $translator->trans('log.select-time.yesterday', domain: 'log'),
            'log_file' => $translator->trans('log.file', domain: 'log'),
            'log_file_size' => $translator->trans('log.file.size', domain: 'log'),
            'log_file_ligne' => $translator->trans('log.file.ligne', domain: 'log'),
            'log_btn_delete_file' => $translator->trans('log.btn.delete.file', domain: 'log'),
            'log_empty_file' => $translator->trans('log.empty.file', domain: 'log'),
            'log_delete_file_confirm' => $translator->trans('log.delete.file.confirm', domain: 'log'),
            'log_delete_file_confirm_2' => $translator->trans('log.delete.file.confirm_2', domain: 'log'),
            'log_delete_file_loading' => $translator->trans('log.delete.file.loading', domain: 'log'),
            'log_delete_file_success' => $translator->trans('log.delete.file.success', domain: 'log'),
            'log_delete_file_btn_close' => $translator->trans('log.delete.file.btn_close', domain: 'log'),
            'log_btn_reload' => $translator->trans('log.btn.reload', domain: 'log'),
        ];

        try {
            $files = $loggerService->getAllFiles($time);
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            die($e->getMessage());
        }
        return $this->json(['files' => $files, 'trans' => $tabTranslate]);
    }

    /**
     * Retourne le contenu d'un fichier de log
     * @param Request $request
     * @param LoggerService $loggerService
     * @return JsonResponse
     */
    #[Route('/ajax/load-log-file', name: 'ajax_load_log_file_empty', methods: ['GET'])]
    #[Route('/ajax/load-log-file/{file}/{page}/{limit}', name: 'ajax_load_log_file', methods: ['GET'])]
    public function loadLogFile(
        LoggerService $loggerService,
        string        $file = '',
        int           $page = 1,
        int           $limit = 20
    ): JsonResponse
    {

        try {
            $grid = $loggerService->loadLogFile($file, $page, $limit);
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            die($e->getMessage());
        } catch (Exception $e) {
            die($e->getMessage());
        }

        return $this->json($grid);
    }

    /**
     * Permet de supprimer un ou plusieurs fichiers
     * @param Request $request
     * @param LoggerService $loggerService
     * @return JsonResponse
     */
    #[Route('/ajax/delete-file', name: 'ajax_delete_file', methods: ['POST'])]
    public function deleteFile(Request $request, LoggerService $loggerService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            $success = $loggerService->deleteLog($data['file']);
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            die($e->getMessage());
        }

        return $this->json(['success' => $success]);
    }
}
