<?php
/**
 * Log
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Controller\Admin;

use App\Service\Admin\OptionUserService;
use App\Service\LoggerService;
use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Translation\Translator;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/admin/{_locale}/log', name: 'admin_log_', requirements: ['_locale' => '%app.supported_locales%'])]
#[IsGranted('ROLE_SUPER_ADMIN')]
class LogController extends AppAdminController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        $breadcrumb = [
            'log.page_title_h1' => '#'
        ];

        return $this->render('admin/log/index.html.twig', [
            'breadcrumb' => $breadcrumb,
            'limit' => $this->optionUserService->getValueByKey(OptionUserService::OU_NB_ELEMENT),
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
    #[Route('/ajax/data-select-log', name: 'ajax_data_select_log', methods: ['POST'])]
    public function dataSelect(Request $request, LoggerService $loggerService, TranslatorInterface $translator):
    JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $tabTranslate = [
            'log_select_file' => $translator->trans('log.select-file'),
            'log_select_time_all' => $translator->trans('log.select-time.all'),
            'log_select_time_now' => $translator->trans('log.select-time.now'),
            'log_select_time_yesterday' => $translator->trans('log.select-time.yesterday'),
            'log_file' => $translator->trans('log.file'),
            'log_file_size' => $translator->trans('log.file.size'),
            'log_file_ligne' => $translator->trans('log.file.ligne'),
            'log_btn_delete_file' => $translator->trans('log.btn.delete.file'),
            'log_empty_file' => $translator->trans('log.empty.file'),
            'log_delete_file_confirm' => $translator->trans('log.delete.file.confirm'),
            'log_delete_file_confirm_2' => $translator->trans('log.delete.file.confirm_2'),
            'log_delete_file_loading' => $translator->trans('log.delete.file.loading'),
            'log_delete_file_success' => $translator->trans('log.delete.file.success'),
            'log_delete_file_btn_close' => $translator->trans('log.delete.file.btn_close'),
        ];

        try {
            $files = $loggerService->getAllFiles($data['time']);
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
    #[Route('/ajax/load-log-file', name: 'ajax_load_log_file', methods: ['POST'])]
    public function loadLogFile(Request $request, LoggerService $loggerService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $grid = [];
        try {
            $grid = $loggerService->loadLogFile($data['file'], $data['page'], $data['limit']);
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
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

        $success = $loggerService->deleteLog($data['file']);

        return $this->json(['success' => $success]);
    }
}
