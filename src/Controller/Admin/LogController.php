<?php
/**
 * Log
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Controller\Admin;

use App\Service\LoggerService;
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
     * @return JsonResponse
     */
    #[Route('/ajax/data-select-log', name: 'ajax_data_select_log')]
    public function dataSelect(Request $request, LoggerService $loggerService, TranslatorInterface $translator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $tabTranslate = [
            'log_select_file' => $translator->trans('log.select-file'),
            'log_select_time_all' => $translator->trans('log.select-time.all'),
            'log_select_time_now' => $translator->trans('log.select-time.now'),
            'log_select_time_yesterday' => $translator->trans('log.select-time.yesterday'),
        ];

        $files = $loggerService->getAllFiles($data['time']);
        return $this->json(['files' => $files, 'trans' => $tabTranslate]);
    }
}
