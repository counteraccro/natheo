<?php
/**
 * Dashboard
 * @author Gourdon Aymeric
 * @version 1.0
 */
namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/{_locale}/dashboard', name: 'dashboard_', requirements: ['_locale' => 'en|es|fr'])]
class DashboardController extends AbstractController
{
    #[Route('/index', name: 'index')]
    public function index(): Response
    {
        return $this->render('admin/dashboard/index.html.twig', [
            'controller_name' => 'IndexController',
        ]);
    }

    /**
     * Page Démo pour les elements html
     * @return Response
     */
    #[Route('/page-demo', name: 'page_demo')]
    public function pageDemo(): Response
    {
        return $this->render('admin/dashboard/page-demo.html.twig', []);
    }
}
