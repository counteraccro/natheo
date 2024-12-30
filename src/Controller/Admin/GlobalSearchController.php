<?php

namespace App\Controller\Admin;

use App\Utils\Breadcrumb;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[\Symfony\Component\Routing\Annotation\Route('/admin/{_locale}/search', name: 'admin_search_', requirements: ['_locale' => '%app.supported_locales%'])]
#[IsGranted('ROLE_USER')]
class GlobalSearchController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['POST'])]
    public function index(Request $request): Response
    {
        $breadcrumb = [
            Breadcrumb::DOMAIN => 'global_search',
            Breadcrumb::BREADCRUMB => [
                'global_search.index.page_title_h1' => '#'
            ]
        ];

        $search = $request->get('global-search-input', '');
        return $this->render('admin/global_search/index.html.twig', [
            'search' => $search,
            'breadcrumb' => $breadcrumb,
        ]);
    }
}
