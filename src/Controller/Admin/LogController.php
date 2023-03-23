<?php
/**
 * Log
 * @author Gourdon Aymeric
 * @version 1.0
 */
namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
}
