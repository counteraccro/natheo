<?php
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Controller pour la gestion des tags
 */

namespace App\Controller\Admin\Content;

use App\Controller\Admin\AppAdminController;
use App\Entity\Admin\Content\Tag\Tag;
use App\Entity\Admin\Content\Tag\TagTranslation;
use App\Enum\Admin\Global\Breadcrumb;
use App\Service\Admin\Content\Tag\TagService;
use App\Utils\Flash\FlashKey;
use App\Utils\System\Options\OptionUserKey;
use App\Utils\Translate\Content\TagTranslate;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
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
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        $breadcrumb = [
            Breadcrumb::DOMAIN->value => 'tag',
            Breadcrumb::BREADCRUMB->value => [
                'tag.index.page_title_h1' => '#',
            ],
        ];

        return $this->render('admin/content/tag/index.html.twig', [
            'breadcrumb' => $breadcrumb,
            'page' => 1,
            'limit' => $this->optionUserService->getValueByKey(OptionUserKey::OU_NB_ELEMENT),
        ]);
    }

    /**
     * Charge le tableau grid de tag en ajax
     * @param TagService $tagService
     * @param Request $request
     * @param int $page
     * @param int $limit
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/load-grid-data/{page}/{limit}', name: 'load_grid_data', methods: ['GET'])]
    public function loadGridData(TagService $tagService, Request $request, int $page = 1, int $limit = 20): JsonResponse
    {
        $queryParams = [
            'search' => $request->query->get('search'),
            'orderField' => $request->query->get('orderField'),
            'order' => $request->query->get('order'),
            'locale' => $request->getLocale(),
        ];

        $grid = $tagService->getAllFormatToGrid($page, $limit, $queryParams);
        return $this->json($grid);
    }

    /**
     * Active ou désactive un tag
     * @param Tag $tag
     * @param TagService $tagService
     * @param TranslatorInterface $translator
     * @param Request $request
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/update-disabled/{id}', name: 'update_disabled', methods: ['PUT'])]
    public function updateDisabled(
        #[MapEntity(id: 'id')] Tag $tag,
        TagService $tagService,
        TranslatorInterface $translator,
        Request $request,
    ): JsonResponse {
        $tag->setDisabled(!$tag->isDisabled());
        $tagService->save($tag);

        $tagTranslation = $tag->getTagTranslationByLocale($request->getLocale());

        $msg = $translator->trans('tag.success.no.disabled', ['label' => $tagTranslation->getLabel()], 'tag');
        if ($tag->isDisabled()) {
            $msg = $translator->trans('tag.success.disabled', ['label' => $tagTranslation->getLabel()], 'tag');
        }

        return $this->json($tagService->getResponseAjax($msg));
    }

    /**
     * Permet de supprimer un tag
     * @param Tag $tag
     * @param TagService $tagService
     * @param TranslatorInterface $translator
     * @param Request $request
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/delete/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(
        #[MapEntity(id: 'id')] Tag $tag,
        TagService $tagService,
        TranslatorInterface $translator,
        Request $request,
    ): JsonResponse {
        $label = $tag->getTagTranslationByLocale($request->getLocale())->getLabel();
        $msg = $translator->trans('tag.remove.success', ['label' => $label], domain: 'tag');
        $tagService->remove($tag);
        return $this->json($tagService->getResponseAjax($msg));
    }

    /**
     * Permet d'ajouter / éditer un tag
     * @param TagService $tagService
     * @param TagTranslate $tagTranslate
     * @param Request $request
     * @param Tag|null $tag
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws ExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/add/', name: 'add')]
    #[Route('/update/{id}', name: 'update')]
    public function add(
        TagService $tagService,
        TagTranslate $tagTranslate,
        Request $request,
        #[MapEntity(id: 'id')] ?Tag $tag = null,
    ): Response {
        $breadcrumbTitle = 'tag.update.page_title_h1';
        if ($tag === null) {
            $breadcrumbTitle = 'tag.add.page_title_h1';
        }

        $breadcrumb = [
            Breadcrumb::DOMAIN->value => 'tag',
            Breadcrumb::BREADCRUMB->value => [
                'tag.index.page_title' => 'admin_tag_index',
                $breadcrumbTitle => '#',
            ],
        ];

        $translate = $tagTranslate->getTranslate();
        $locales = $tagService->getLocales();
        $locales['current'] = $request->getLocale();

        if ($tag === null) {
            $tag = new Tag();
            foreach ($locales['locales'] as $locale) {
                $tagTranslation = new TagTranslation();
                $tagTranslation->setLocale($locale)->setTag($tag);
                $tag->addTagTranslation($tagTranslation);
            }
        }
        $tag = $tagService->convertEntityToArray($tag, ['createdAt', 'updateAt', 'pages']);

        return $this->render('admin/content/tag/add_update.html.twig', [
            'breadcrumb' => $breadcrumb,
            'translate' => $translate,
            'locales' => $locales,
            'tag' => $tag,
        ]);
    }

    /**
     * Sauvegarde les données du tags
     * @param TagService $tagService
     * @param Request $request
     * @param TranslatorInterface $translator
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/save/', name: 'save', methods: ['POST'])]
    public function save(TagService $tagService, Request $request, TranslatorInterface $translator): JsonResponse
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
            domain: 'tag',
        );
        $tab = $tagService->getResponseAjax($msg);

        if ($tab['success']) {
            $this->addFlash(FlashKey::FLASH_SUCCESS, $msg);
        } else {
            $this->addFlash(FlashKey::FLASH_DANGER, $tab['msg']);
        }
        return $this->json(['etat' => $status]);
    }

    /**
     * Affiche les statistiques d'un tag
     * @param Tag|null $tag
     * @return Response
     */
    #[Route('/ajax/stats/{id}', name: 'stats', methods: ['GET'])]
    public function statistique(#[MapEntity(id: 'id')] ?Tag $tag = null): Response
    {
        return $this->render('admin/content/tag/date_update.html.twig', [
            'tag' => $tag,
        ]);
    }

    /**
     * Permet de rechercher un ou plusieurs tags pour l'auto-complete
     * @param TagService $tagService
     * @param string $search
     * @param string $locale
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/search/{search}/{locale}', name: 'search', methods: ['GET'])]
    public function search(TagService $tagService, string $search = '', string $locale = ''): Response
    {
        $result = $tagService->searchByLocale($locale, $search);
        return $this->json(['result' => $result]);
    }

    /**
     * Retourne un objet tag depuis un label et la locale
     * Si le tag n'existe pas, il est créé. (utilisé pour l'auto-complete)
     * @param TagService $tagService
     * @param TranslatorInterface $translator
     * @param string $search
     * @param string $locale
     * @return Response
     * @throws ExceptionInterface
     */
    #[Route('/ajax/tag-by-name/{search}/{locale}', name: 'tag_by_name', methods: ['GET'])]
    public function getTagByName(
        TagService $tagService,
        TranslatorInterface $translator,
        string $search = '',
        string $locale = '',
    ): Response {
        $tag = $tagService->newTagByNameAndLocale($locale, $search);

        $msg = '';
        $success = false;
        if ($tag === null) {
            $msg = $translator->trans('tag.page.error.tag.disabled', domain: 'tag');
        } else {
            $tag = $tagService->convertEntityToArray($tag, ['createdAt', 'updateAt', 'pages']);
            $success = true;
        }
        return $this->json(['tag' => $tag, 'msg' => $msg, 'success' => $success]);
    }
}
