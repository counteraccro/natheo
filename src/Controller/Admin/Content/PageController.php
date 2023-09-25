<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Controller pour la gestion des pages
 */
namespace App\Controller\Admin\Content;

use App\Controller\Admin\AppAdminController;
use App\Utils\Breadcrumb;
use App\Utils\System\Options\OptionUserKey;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/{_locale}/page', name: 'admin_page_', requirements: ['_locale' => '%app.supported_locales%'])]
#[IsGranted('ROLE_CONTRIBUTEUR')]
class PageController extends AppAdminController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {

        $breadcrumb = [
            Breadcrumb::DOMAIN => 'page',
            Breadcrumb::BREADCRUMB => [
                'page.index.page_title_h1' => '#'
            ]
        ];

        return $this->render('admin/content/page/index.html.twig', [
            'breadcrumb' => $breadcrumb,
            'page' => 1,
            'limit' => $this->optionUserService->getValueByKey(OptionUserKey::OU_NB_ELEMENT)
        ]);
    }

    /**
     * Contenu Page
     * Page
     *  - id
     *  - user_id
     *  - render
     *  - created_at
     *  - update_at
     *
     * PageTranslation
     * - id
     * - page_id
     * - locale
     * - titre
     * - url
     * - created_at
     * - update_at
     *
     * PageContent
     * - id
     * - page_id
     * - order
     * - type
     * -
     *
     * PageContentTranslate
     * - page_content_id
     * - locale
     * - text
     *
     *
     * PageTag
     * - id
     * - page_id
     * - tag_id
     */
}
