<?php
/**
 * Sidebar
 * @author Gourdon Aymeric
 * @version 1.0
 */
namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/{_locale}/sidebar', name: 'admin_sidebar_', requirements: ['_locale' => '%app.supported_locales%'])]
#[IsGranted('ROLE_SUPER_ADMIN')]
class SidebarController extends AbstractController
{
    /**
     * Point d'entrée
     * @return Response
     */
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        $breadcrumb = [
            'sidebar.page_title_h1' => '#'
        ];

        return $this->render('admin/sidebar/index.html.twig', [
            'breadcrumb' => $breadcrumb,
        ]);
    }
}
