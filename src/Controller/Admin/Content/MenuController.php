<?php

namespace App\Controller\Admin\Content;

use App\Controller\Admin\AppAdminController;
use App\Utils\Breadcrumb;
use App\Utils\System\Options\OptionUserKey;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/{_locale}/menu', name: 'admin_menu_', requirements: ['_locale' => '%app.supported_locales%'])]
#[IsGranted('ROLE_CONTRIBUTEUR')]
class MenuController extends AppAdminController
{
    /**
     * Index listing des tags
     * @return Response
     */
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        $breadcrumb = [
            Breadcrumb::DOMAIN => 'tag',
            Breadcrumb::BREADCRUMB => [
                'tag.index.page_title_h1' => '#'
            ]
        ];

        return $this->render('admin/content/menu/index.html.twig', [
            'breadcrumb' => $breadcrumb,
            'page' => 1,
            'limit' => $this->optionUserService->getValueByKey(OptionUserKey::OU_NB_ELEMENT)
        ]);
    }
}
