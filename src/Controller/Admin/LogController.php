<?php
/**
 * Log
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Controller\Admin;

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
class LogController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        $breadcrumb = [
            'log.page_title_h1' => '#'
        ];

        return $this->render('admin/log/index.html.twig', [
            'breadcrumb' => $breadcrumb,
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
    public function dataSelect(Request $request, LoggerService $loggerService, TranslatorInterface $translator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $tabTranslate = [
            'log_select_file' => $translator->trans('log.select-file'),
            'log_select_time_all' => $translator->trans('log.select-time.all'),
            'log_select_time_now' => $translator->trans('log.select-time.now'),
            'log_select_time_yesterday' => $translator->trans('log.select-time.yesterday'),
        ];

        try {
            $files = $loggerService->getAllFiles($data['time']);
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {

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

        $grid = $loggerService->loadLogFile($data['file'], $data['page'],  $data['limit']);

        return $this->json($grid);
    }
}
