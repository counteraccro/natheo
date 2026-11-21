<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Routes Twig des tags encore utilisées par l'éditeur de pages et l'aperçu du formulaire
 */

namespace App\Controller\Admin\Content;

use App\Controller\Admin\AppAdminController;
use App\Enum\Admin\Content\Tag\TagCsrfToken;
use App\Service\Admin\Content\Tag\TagService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Service\Front\FrontThemeService;
use App\Translation\Front\FrontTranslate;

#[Route('/admin/{_locale}/tag', name: 'admin_tag_', requirements: ['_locale' => '%app.supported_locales%'])]
#[IsGranted('ROLE_CONTRIBUTEUR')]
class TagController extends AppAdminController
{
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
     * Aperçu d'un tag au rendu du thème du site public, affiché dans une iframe du formulaire qui lui envoie le tag
     * @param FrontThemeService $frontThemeService
     * @param FrontTranslate $frontTranslate
     * @param string $locale langue du libellé
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[
        Route(
            '/preview/{locale}',
            name: 'preview',
            requirements: ['locale' => '%app.supported_locales%'],
            methods: ['GET'],
        ),
    ]
    public function preview(
        FrontThemeService $frontThemeService,
        FrontTranslate $frontTranslate,
        string $locale,
    ): Response {
        $theme = $frontThemeService->getPreviewTemplate(FrontThemeService::TAG_PREVIEW);

        $response = $this->render($theme['template'], [
            'urls' => $frontThemeService->getUrls(),
            'datas' => $frontThemeService->getDatas($locale),
            'translate' => $frontTranslate->getTranslate($locale),
            'scriptsTag' => $theme['scriptsTag'],
            'locale' => $locale,
        ]);
        $response->headers->set('X-Robots-Tag', 'noindex');
        return $response;
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
