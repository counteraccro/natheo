<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Provider du rendu d'un bloc de page publiée
 */

namespace App\Api\V2\State\Provider\Content;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Api\V2\Parameter\LocaleParameter;
use App\Api\V2\Resource\Content\Page;
use App\Api\V2\Resource\Content\PageContent;
use App\Enum\Admin\Content\Page\PageContentType;
use App\Service\Api\Content\Page\ApiPageContentService;
use League\CommonMark\Exception\CommonMarkException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @implements ProviderInterface<PageContent>
 */
final readonly class PageContentProvider implements ProviderInterface
{
    public function __construct(
        private ApiPageContentService $apiPageContentService,
        private IriConverterInterface $iriConverter,
        private TranslatorInterface $translator,
    ) {}

    /**
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return PageContent
     * @throws CommonMarkException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): PageContent
    {
        $locale = $this->apiPageContentService->resolveLocale(LocaleParameter::getValueFrom($operation));
        $entity = $this->apiPageContentService->findPublishedPageContent((int) $uriVariables['id']);
        $rendered = $entity === null ? null : $this->apiPageContentService->getRenderedContent($entity, $locale);
        if ($rendered === null) {
            throw new NotFoundHttpException(
                $this->translator->trans('api_errors.find.page.content.not.found', domain: 'api_errors'),
            );
        }

        $type = PageContentType::from((int) $entity->getType());
        if ($type === PageContentType::LISTING) {
            $rendered['pages'] =
                $this->iriConverter->getIriFromResource(Page::class, operation: new GetCollection()) .
                '?' .
                http_build_query(['category' => $rendered['category'], 'locale' => $locale]);
        }

        $pageContent = new PageContent();
        $pageContent->id = (int) $entity->getId();
        $pageContent->type = strtolower($type->name);
        $pageContent->position = (int) $entity->getRenderBlock();
        $pageContent->content = $rendered;
        return $pageContent;
    }
}
