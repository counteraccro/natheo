<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Controller pour les options avancées
 */

namespace App\Controller\Admin\Tools;

use App\Utils\Breadcrumb;
use App\Utils\Translate\Tools\AdvancedOptionsTranslate;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[\Symfony\Component\Routing\Annotation\Route('/admin/{_locale}/advanced-options', name: 'admin_advanced_options_',
    requirements: ['_locale' => '%app.supported_locales%'])]
#[IsGranted('ROLE_SUPER_ADMIN')]
class AdvancedOptionsController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(AdvancedOptionsTranslate $advancedOptionsTranslate): Response
    {
        $breadcrumb = [
            Breadcrumb::DOMAIN => 'advanced_options',
            Breadcrumb::BREADCRUMB => [
                'advanced_options.index.page_title_h1' => '#'
            ]
        ];

        return $this->render('admin/tools/advanced_options/index.html.twig', [
            'breadcrumb' => $breadcrumb,
            'translate' => $advancedOptionsTranslate->getTranslate(),
            'urls' => [
                //'load_schema_database' => $this->generateUrl('admin_database_manager_load_schema_database'),
            ]
        ]);
    }
}
