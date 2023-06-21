<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Controller pour la gestion des tags
 */

namespace App\Controller\Admin\Content;

use App\Controller\Admin\AppAdminController;
use App\Entity\Admin\Content\Tag;
use App\Entity\Admin\Content\TagTranslation;
use App\Service\Admin\Breadcrumb;
use App\Service\Admin\Content\TagService;
use App\Service\Admin\TranslateService;
use App\Utils\Options\OptionUserKey;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/admin/{_locale}/tag', name: 'admin_tag_', requirements: ['_locale' => '%app.supported_locales%'])]
#[IsGranted('ROLE_CONTRIBUTEUR')]
class TagController extends AppAdminController
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

        return $this->render('admin/content/tag/index.html.twig', [
            'breadcrumb' => $breadcrumb,
            'page' => 1,
            'limit' => $this->optionUserService->getValueByKey(OptionUserKey::OU_NB_ELEMENT)
        ]);
    }

    /**
     * Charge le tableau grid de tag en ajax
     * @param Request $request
     * @param TagService $tagService
     * @return JsonResponse
     */
    #[Route('/ajax/load-grid-data', name: 'load_grid_data', methods: ['POST'])]
    public function loadGridData(Request $request, TagService $tagService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $grid = $tagService->getAllFormatToGrid($data['page'], $data['limit']);
        return $this->json($grid);
    }

    /**
     * Active ou désactive un tag
     * @param Tag $tag
     * @param TagService $tagService
     * @param TranslatorInterface $translator
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/ajax/update-disabled/{id}', name: 'update_disabled')]
    public function updateDisabled(
        Tag                 $tag,
        TagService          $tagService,
        TranslatorInterface $translator,
        Request             $request
    ): JsonResponse
    {
        $tag->setDisabled(!$tag->isDisabled());
        $tagService->save($tag);

        $tagTranslation = $tag->getTagTranslationByLocale($request->getLocale());

        $msg = $translator->trans('tag.success.no.disabled', ['label' => $tagTranslation->getLabel()], 'tag');
        if ($tag->isDisabled()) {
            $msg = $translator->trans('tag.success.disabled', ['label' => $tagTranslation->getLabel()], 'tag');
        }

        return $this->json(['type' => 'success', 'msg' => $msg]);
    }

    /**
     * Permet de supprimer un tag
     * @param Tag $tag
     * @param TagService $tagService
     * @param TranslatorInterface $translator
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/ajax/delete/{id}', name: 'delete')]
    public function delete(
        Tag                 $tag,
        TagService          $tagService,
        TranslatorInterface $translator,
        Request             $request
    ): JsonResponse
    {
        $label = $tag->getTagTranslationByLocale($request->getLocale())->getLabel();

        $tagService->remove($tag);
        return $this->json(['type' => 'success', 'msg' => $translator->trans(
            'tag.remove.success',
            ['label' => $label],
            domain: 'tag'
        )]);
    }

    #[Route('/ajax/add/', name: 'add')]
    #[Route('/ajax/update/{id}', name: 'update')]
    public function add(
        TagService          $tagService,
        TranslatorInterface $translator,
        Tag                 $tag = null,
    ): Response
    {
        $breadcrumb = [
            Breadcrumb::DOMAIN => 'tag',
            Breadcrumb::BREADCRUMB => [
                'tag.index.page_title' => 'admin_tag_index',
                'tag.add.page_title_h1' => '#'
            ]
        ];

        return $this->render('admin/content/tag/add.html.twig', [
            'breadcrumb' => $breadcrumb,
        ]);
    }
}
