<?php
/**
 * Information system
 * @author Gourdon Aymeric
 * @version 2.0
 */
namespace App\Controller\Admin\System;

use App\Service\Admin\System\InformationService;
use App\Utils\Breadcrumb;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/{_locale}/info', name: 'admin_info_', requirements: ['_locale' => '%app.supported_locales%'])]
#[IsGranted('ROLE_SUPER_ADMIN')]
class InfoController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(InformationService $informationService): Response
    {
        $breadcrumb = [
            Breadcrumb::DOMAIN => 'info',
            Breadcrumb::BREADCRUMB => [
                'info.page.title.h1' => '#',
            ],
        ];

        $tabInformation = $informationService->getAllInformation();

        return $this->render('admin/system/info/index.html.twig', [
            'breadcrumb' => $breadcrumb,
            'tabInfo' => $tabInformation,
        ]);
    }

    /**
     * Information PHP
     * @return Response
     */
    #[Route('/phpinfo', name: 'phpinfo')]
    public function phpInfo(): Response
    {
        return $this->render('admin/system/info/phpinfo.html.twig');
    }
}
