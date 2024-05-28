<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Controller pour la gestion de la base de données
 */
namespace App\Controller\Admin\Tools;

use App\Utils\Breadcrumb;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[\Symfony\Component\Routing\Annotation\Route('/admin/{_locale}/database-manager', name: 'admin_database_manager_',
    requirements: ['_locale' => '%app.supported_locales%'])]
#[IsGranted('ROLE_SUPER_ADMIN')]
class DatabaseManagerController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        $breadcrumb = [
            Breadcrumb::DOMAIN => 'database_manager',
            Breadcrumb::BREADCRUMB => [
                'database_manager.index.page_title_h1' => '#'
            ]
        ];

        return $this->render('admin/tools/database_manager/index.html.twig', [
            'breadcrumb' => $breadcrumb
        ]);
    }
}
