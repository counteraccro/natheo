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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

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
    public function dataSelect(LoggerService $loggerService): JsonResponse
    {
        $date = '2023-03-23';
        $files = $loggerService->getAllFiles($date);
        return $this->json(['files' => $files]);
    }
}
