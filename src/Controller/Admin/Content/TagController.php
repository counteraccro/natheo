<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Controller pour la gestion des tags
 */

namespace App\Controller\Admin\Content;

use App\Controller\Admin\AppAdminController;
use App\Entity\Admin\Content\Tag\Tag;
use App\Entity\Admin\Content\Tag\TagTranslation;
use App\Enum\Admin\Content\Tag\TagCsrfToken;
use App\Enum\Admin\Global\Breadcrumb;
use App\Enum\Admin\System\Options\OptionUser;
use App\Security\Voter\ContentVoter;
use App\Service\Admin\Content\Tag\TagService;
use App\Translate\Admin\Content\TagTranslate;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Enum\Admin\Global\FlashKey;

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
            'limit' => $this->optionUserService->getValueByKey(OptionUser::OU_NB_ELEMENT->value),
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

        $userId = null;
        if ($request->query->get('filter') === self::FILTER_ME) {
            $userId = $this->getUser()->getId();
        }

        $grid = $tagService->getAllFormatToGrid($page, $limit, $queryParams, $userId);
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
    #[Route('/ajax/update-disabled/{id}', name: 'update_disabled', requirements: ['id' => '\d+'], methods: ['PUT'])]
    public function updateDisabled(
        #[MapEntity(id: 'id')] Tag $tag,
        TagService $tagService,
        TranslatorInterface $translator,
        Request $request,
    ): JsonResponse {
        $error =
            $this->checkCsrf(TagCsrfToken::DISABLED->value, $request) ??
            $this->checkContentAccess(ContentVoter::DISABLE, $tag, 'tag');
        if ($error !== null) {
            return $error;
        }

        $tag->setDisabled(!$tag->isDisabled());
        $tagService->save($tag);

        $label = $tagService->getEscapedLabel($tag, $request->getLocale());
        $msg = $translator->trans('tag.success.no.disabled', ['label' => $label], 'tag');
        if ($tag->isDisabled()) {
            $msg = $translator->trans('tag.success.disabled', ['label' => $label], 'tag');
        }

        return $this->json($tagService->getResponseAjax($msg));
    }

    /**
     * Permet de supprimer un tag, refusé s'il est rattaché à au moins une page
     * @param Tag $tag
     * @param TagService $tagService
     * @param TranslatorInterface $translator
     * @param Request $request
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/delete/{id}', name: 'delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function delete(
        #[MapEntity(id: 'id')] Tag $tag,
        TagService $tagService,
        TranslatorInterface $translator,
        Request $request,
    ): JsonResponse {
        $error =
            $this->checkCsrf(TagCsrfToken::DELETE->value, $request) ??
            $this->checkContentAccess(ContentVoter::DELETE, $tag, 'tag');
        if ($error !== null) {
            return $error;
        }

        $nbPages = $tagService->countPages($tag);
        if ($nbPages > 0) {
            return $this->jsonError($translator->trans('tag.error.delete.used', ['nb' => $nbPages], 'tag'));
        }

        $label = $tagService->getEscapedLabel($tag, $request->getLocale());
        $tagService->remove($tag);

        $msg = $translator->trans('tag.remove.success', ['label' => $label], domain: 'tag');
        return $this->json($tagService->getResponseAjax($msg));
    }

    /**
     * Permet d'ajouter / éditer un tag
     * @param TagService $tagService
     * @param TagTranslate $tagTranslate
     * @param Request $request
     * @param int|null $id
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws ExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/add/', name: 'add')]
    #[Route('/update/{id}', name: 'update', requirements: ['id' => '\d+'])]
    public function add(
        TagService $tagService,
        TagTranslate $tagTranslate,
        Request $request,
        ?int $id = null,
    ): Response {
        $tag = null;
        if ($id !== null) {
            $tag = $tagService->findOneById(Tag::class, $id);
            if ($tag !== null) {
                $this->denyAccessUnlessGranted(ContentVoter::EDIT, $tag);
            }
        }

        $breadcrumbTitle = 'tag.update.page_title_h1';
        if ($id === null) {
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

        if ($id === null) {
            $tag = new Tag();
            foreach ($locales['locales'] as $locale) {
                $tagTranslation = new TagTranslation();
                $tagTranslation->setLocale($locale)->setTag($tag);
                $tag->addTagTranslation($tagTranslation);
            }
        }

        $canDelete = $tag !== null && $tag->getId() !== null && $this->isGranted(ContentVoter::DELETE, $tag);
        if ($tag !== null) {
            $tag = $tagService->convertEntityToArray($tag, ['createdAt', 'updateAt', 'pages', 'user']);
        }

        return $this->render('admin/content/tag/add_update.html.twig', [
            'breadcrumb' => $breadcrumb,
            'translate' => $translate,
            'locales' => $locales,
            'tag' => $tag,
            'canDelete' => $canDelete,
            'csrfTokenSave' => TagCsrfToken::SAVE->value,
            'csrfTokenDelete' => TagCsrfToken::DELETE->value,
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
        $error = $this->checkCsrf(TagCsrfToken::SAVE->value, $request);
        if ($error !== null) {
            return $error;
        }

        $data = json_decode($request->getContent(), true);
        if (!is_array($data) || !isset($data['tag']) || !is_array($data['tag'])) {
            return $this->jsonError(
                $translator->trans('tag.form.error.save', domain: 'tag'),
                Response::HTTP_BAD_REQUEST,
            );
        }

        $tag = new Tag();
        $tag->setUser($this->getUser());
        $status = 'new';
        $id = $data['tag']['id'] ?? null;
        if ($id !== null) {
            $tag = is_numeric($id) ? $tagService->findOneById(Tag::class, (int) $id) : null;
            if ($tag === null) {
                return $this->jsonNotFound('tag');
            }
            $error = $this->checkContentAccess(ContentVoter::EDIT, $tag, 'tag');
            if ($error !== null) {
                return $error;
            }
            $status = 'edit';
        }

        $tagService->populate($tag, $data['tag']);
        $errors = $tagService->validate($tag);
        if (!empty($errors)) {
            return $this->jsonError(implode('<br />', $errors), Response::HTTP_BAD_REQUEST);
        }

        $tag->setUpdateAt(new \DateTime());
        $tagService->save($tag);

        $msg = $translator->trans(
            'tag.save.' . $status,
            ['label' => $tagService->getEscapedLabel($tag, $request->getLocale())],
            domain: 'tag',
        );
        $tab = $tagService->getResponseAjax($msg);

        if ($tab['success']) {
            $this->addFlash(FlashKey::SUCCESS->value, $msg);
        } else {
            $this->addFlash(FlashKey::DANGER->value, $tab['msg']);
        }
        return $this->json(['etat' => $status, 'success' => $tab['success'], 'msg' => $tab['msg']]);
    }

    /**
     * Affiche les statistiques d'un tag
     * @param Tag|null $tag
     * @return Response
     */
    #[Route('/ajax/stats/{id}', name: 'stats', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function statistique(#[MapEntity(id: 'id')] ?Tag $tag = null): Response
    {
        if ($tag === null) {
            throw $this->createNotFoundException();
        }

        return $this->render('admin/content/tag/date_update.html.twig', [
            'tag' => $tag,
        ]);
    }

    /**
     * Permet de rechercher un ou plusieurs tags pour l'auto-complete
     * Les valeurs par défaut servent uniquement à générer l'URL de base transmise à Vue
     * @param TagService $tagService
     * @param string $search
     * @param string $locale
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[
        Route(
            '/ajax/search/{search}/{locale}',
            name: 'search',
            requirements: ['locale' => '%app.supported_locales%'],
            methods: ['GET'],
        ),
    ]
    public function search(TagService $tagService, string $search = '', string $locale = ''): Response
    {
        $search = trim($search);
        if ($search === '' || $locale === '') {
            throw $this->createNotFoundException();
        }

        $result = $tagService->searchByLocale($locale, $search);
        return $this->json(['result' => $result]);
    }

    /**
     * Retourne un objet tag depuis un label et la locale (corps JSON {label, locale})
     * Si le tag n'existe pas, il est créé. (utilisé pour l'auto-complete)
     * @param TagService $tagService
     * @param TranslatorInterface $translator
     * @param Request $request
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws ExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/tag-by-name', name: 'tag_by_name', methods: ['POST'])]
    public function getTagByName(TagService $tagService, TranslatorInterface $translator, Request $request): Response
    {
        $error = $this->checkCsrf(TagCsrfToken::TAG_BY_NAME->value, $request);
        if ($error !== null) {
            return $error;
        }

        $data = json_decode($request->getContent(), true);
        $label = trim((string) ($data['label'] ?? ''));
        $locale = (string) ($data['locale'] ?? '');
        if ($label === '' || mb_strlen($label) > 255 || !in_array($locale, $tagService->getLocales()['locales'], true)) {
            return $this->jsonError(
                $translator->trans('tag.error.tag_by_name', domain: 'tag'),
                Response::HTTP_BAD_REQUEST,
            );
        }

        $tag = $tagService->newTagByNameAndLocale($locale, $label, $this->getUser());

        $msg = '';
        $success = false;
        if ($tag === null) {
            $msg = $translator->trans('tag.page.error.tag.disabled', domain: 'tag');
        } else {
            $tag = $tagService->convertEntityToArray($tag, ['createdAt', 'updateAt', 'pages', 'user']);
            $success = true;
        }
        return $this->json(['tag' => $tag, 'msg' => $msg, 'success' => $success]);
    }
}
