<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Controller pour les options avancées
 */

namespace App\Controller\Admin\Tools;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[\Symfony\Component\Routing\Annotation\Route('/admin/{_locale}/database-manager', name: 'admin_database_manager_',
    requirements: ['_locale' => '%app.supported_locales%'])]
#[IsGranted('ROLE_SUPER_ADMIN')]
class AdvancedOptionsController extends AbstractController
{
    #[Route('/admin/tools/advanced/options', name: 'app_admin_tools_advanced_options')]
    public function index(): Response
    {
        return $this->render('admin/tools/advanced_options/index.html.twig', [
            'controller_name' => 'AdvancedOptionsController',
        ]);
    }
}
