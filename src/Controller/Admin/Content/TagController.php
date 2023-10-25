<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Controller pour la gestion des tags
 */

namespace App\Controller\Admin\Content;

use App\Controller\Admin\AppAdminController;
use App\Entity\Admin\Content\Tag\Tag;
use App\Entity\Admin\Content\Tag\TagTranslation;
use App\Service\Admin\Content\Tag\TagComponentService;
use App\Service\Admin\Content\Tag\TagService;
use App\Utils\Breadcrumb;
use App\Utils\Flash\FlashKey;
use App\Utils\System\Options\OptionUserKey;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
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

    /**
     * Permet d'ajouter / éditer un tag
     * @param TagService $tagService
     * @param Request $request
     * @param Tag|null $tag
     * @return Response
     * @throws ExceptionInterface
     */
    #[Route('/add/', name: 'add')]
    #[Route('/update/{id}', name: 'update')]
    public function add(
        TagService $tagService,
        Request    $request,
        Tag        $tag = null
    ): Response
    {
        $breadcrumbTitle = 'tag.update.page_title_h1';
        if ($tag === null) {
            $breadcrumbTitle = 'tag.add.page_title_h1';
        }

        $breadcrumb = [
            Breadcrumb::DOMAIN => 'tag',
            Breadcrumb::BREADCRUMB => [
                'tag.index.page_title' => 'admin_tag_index',
                $breadcrumbTitle => '#'
            ]
        ];

        $translate = $tagService->getTranslateTagForm();
        $locales = $tagService->getLocales(false);
        $locales['current'] = $request->getLocale();


        if ($tag === null) {
            $tag = new Tag();
            foreach ($locales['locales'] as $locale) {
                $tagTranslation = new TagTranslation();
                $tagTranslation->setLocale($locale)->setTag($tag);
                $tag->addTagTranslation($tagTranslation);

            }
        }
        $tag = $tagService->convertEntityToArray($tag, ['createdAt', 'updateAt']);


        return $this->render('admin/content/tag/add_update.html.twig', [
            'breadcrumb' => $breadcrumb,
            'translate' => $translate,
            'locales' => $locales,
            'tag' => $tag
        ]);
    }

    /**
     * Sauvegarde les données du tags
     * @param TagService $tagService
     * @param Request $request
     * @param TranslatorInterface $translator
     * @return JsonResponse
     */
    #[Route('/ajax/save/', name: 'save', methods: ['POST'])]
    public function save(
        TagService          $tagService,
        Request             $request,
        TranslatorInterface $translator
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $tag = new tag();
        $status = 'new';
        if ($data['tag']['id'] !== null) {
            $tag = $tagService->findOneBy(Tag::class, 'id', $data['tag']['id']);
            $status = 'edit';
        }

        /** @var Tag $tag */
        $tag = $tagService->convertArrayToEntity($data['tag'], Tag::class, $tag);
        $tag->setUpdateAt(new \DateTime());
        $tagService->save($tag);

        $msg = $translator->trans(
            'tag.save.' . $status,
            ['label' => $tag->getTagTranslationByLocale($request->getLocale())->getLabel()],
            domain: 'tag'
        );
        $this->addFlash(FlashKey::FLASH_SUCCESS, $msg);

        return $this->json(['etat' => $status]);
    }

    /**
     * Affiche les statistiques d'un tag
     * @param Tag|null $tag
     * @return Response
     */
    #[Route('/ajax/stats/{id}', name: 'stats', methods: ['GET'])]
    public function statistique(Tag $tag = null): Response
    {
        return $this->render('admin/content/tag/date_update.html.twig', [
            'tag' => $tag
        ]);
    }

    /**
     * Permet de rechercher un ou plusieurs tags pour l'auto-complete
     * @param Request $request
     * @param TagService $tagService
     * @return Response
     */
    #[Route('/ajax/search/', name: 'search')]
    public function search(Request $request, TagService $tagService): Response
    {
        $data = json_decode($request->getContent(), true);
        $result = $tagService->searchByLocale($data['locale'], $data['search']);
        return $this->json(['result' => $result]);
    }

    /**
     * Retourne un objet tag depuis un label et la locale
     * Si le tag n'existe pas, il est créé. (utilisé pour l'auto-complete)
     * @param Request $request
     * @param TagService $tagService
     * @return Response
     */
    #[Route('/ajax/tag-by-name/', name: 'tag_by_name')]
    public function getTagByName(Request $request, TagService $tagService): Response
    {
        $data = json_decode($request->getContent(), true);

        $tag = $tagService->newTagByNameAndLocale($data['locale'], $data['label']);
        $tag = $tagService->convertEntityToArray($tag, ['createdAt', 'updateAt']);

        return $this->json(['tag' => $tag]);
    }
}
