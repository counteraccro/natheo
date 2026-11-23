<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.2
 * Controller pour le markdown
 */
namespace App\Controller\Admin\Global;

use App\Enum\Admin\Module\CoreModuleId;
use App\Service\Admin\Module\AdminMenuBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/{_locale}/markdown', name: 'admin_markdown_', requirements: ['_locale' => '%app.supported_locales%'])]
#[IsGranted('ROLE_CONTRIBUTEUR')]
class MarkdownController extends AbstractController
{
    /**
     * Chargement des données nécessaires au markdown, null pour un module masqué
     * @param AdminMenuBuilder $menuBuilder
     * @return Response
     */
    #[Route('/ajax/load-datas', name: 'load-datas', methods: ['GET'])]
    public function loadDatas(AdminMenuBuilder $menuBuilder): Response
    {
        return $this->json([
            'media' => $menuBuilder->isModuleVisible(CoreModuleId::MEDIA->value)
                ? $this->generateUrl('admin_media_load_medias')
                : null,
            'internalLinks' => $menuBuilder->isModuleVisible(CoreModuleId::PAGE->value)
                ? $this->generateUrl('admin_page_liste_pages_internal_link')
                : null,
        ]);
    }
}
