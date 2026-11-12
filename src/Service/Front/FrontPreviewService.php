<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Prévisualisation d'une page sur le site public : la page et ses blocs au format de l'API v2, servis en props
 * car un brouillon ou une page non publiée ne sont pas exposés par l'API
 */

namespace App\Service\Front;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Api\V2\Mapper\Content\MenuMapper;
use App\Api\V2\Mapper\Content\PageMapper;
use App\Api\V2\Resource\Content\Page as PageResource;
use App\Api\V2\Resource\Content\PageContent;
use App\Api\V2\State\Provider\Content\PageContentResourceBuilder;
use App\Entity\Admin\Content\Page\Page;
use App\Service\Api\Content\Page\ApiPageContentService;
use App\Service\Api\Content\Page\ApiPageService;
use League\CommonMark\Exception\CommonMarkException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final readonly class FrontPreviewService
{
    public function __construct(
        private ApiPageService $apiPageService,
        private ApiPageContentService $apiPageContentService,
        private PageContentResourceBuilder $pageContentResourceBuilder,
        private NormalizerInterface $normalizer,
        private IriConverterInterface $iriConverter,
    ) {}

    /**
     * Page à prévisualiser (brouillon si demandé, page non publiée sinon) et ses blocs complets ;
     * null quand la page publiée s'affiche normalement
     * @param Page $page page que l'utilisateur peut modifier
     * @param string $locale
     * @param bool $withDraft
     * @return array{page: array, contents: array}|null
     * @throws CommonMarkException
     * @throws ContainerExceptionInterface
     * @throws ExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getPreview(Page $page, string $locale, bool $withDraft): ?array
    {
        $preview = $this->apiPageService->getPreviewPageOf($page, $withDraft);
        if ($preview === null) {
            return null;
        }

        try {
            $contents = $this->buildContents($preview, $locale);
            $menuMapper = new MenuMapper($locale);
            $menus = array_map($menuMapper->map(...), $this->apiPageService->getMenusForPage($preview));
            $resource = (new PageMapper($locale))->map($preview, menus: $menus);
        } finally {
            $this->apiPageService->releasePreviewPage($preview, $page);
        }

        $resource->id = (int) $page->getId();
        $pageData = $this->normalizer->normalize($resource, 'jsonld', [
            'groups' => ['page:read'],
            'resource_class' => PageResource::class,
        ]);
        unset($pageData['@context']);

        // Blocs décrits à la main : ceux d'un brouillon n'ont pas d'IRI que le serializer sache générer
        $pageData['contents'] = [];
        $blocks = [];
        foreach ($contents as $content) {
            $summary = [
                '@id' => $this->contentIri($content),
                '@type' => 'PageContent',
                'id' => $content->id,
                'type' => $content->type,
                'position' => $content->position,
            ];
            $pageData['contents'][] = $summary;
            $blocks[] = $summary + [
                'content' => $this->normalizer->normalize($content->content, 'json', [
                    'groups' => ['page_content:read'],
                ]),
            ];
        }
        return ['page' => $pageData, 'contents' => $blocks];
    }

    /**
     * Blocs de la page ; ceux d'un brouillon, pas encore enregistrés, reçoivent un identifiant négatif
     * @param Page $preview
     * @param string $locale
     * @return PageContent[]
     * @throws CommonMarkException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function buildContents(Page $preview, string $locale): array
    {
        $contents = [];
        $newId = 0;
        foreach ($this->apiPageContentService->getAvailablePageContents($preview, $locale) as $entity) {
            $id = $entity->getId() ?? --$newId;
            $content = $this->pageContentResourceBuilder->build($entity, $locale, $id);
            if ($content !== null) {
                $contents[] = $content;
            }
        }
        return $contents;
    }

    /**
     * IRI d'un bloc ; celui d'un bloc de brouillon suit le même format, sa route n'acceptant pas d'identifiant négatif
     * @param PageContent $content
     * @return string
     */
    private function contentIri(PageContent $content): string
    {
        if ($content->id > 0) {
            return $this->iriConverter->getIriFromResource($content);
        }
        $model = clone $content;
        $model->id = 1;
        return (string) preg_replace('#/1$#', '/' . $content->id, $this->iriConverter->getIriFromResource($model));
    }
}
