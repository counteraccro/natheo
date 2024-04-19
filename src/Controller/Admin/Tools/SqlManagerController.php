<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Controller pour la gestion des faqs
 */
namespace App\Controller\Admin\Tools;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SqlManagerController extends AbstractController
{
    #[Route('/admin/tools/sql/manager', name: 'app_admin_tools_sql_manager')]
    public function index(): Response
    {
        return $this->render('admin/tools/sql_manager/index.html.twig', [
            'controller_name' => 'SqlManagerController',
        ]);
    }
}
