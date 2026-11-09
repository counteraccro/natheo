<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Provider d'une page publiée par son id
 */

namespace App\Api\V2\State\Provider\Content;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Api\V2\Exception\NotFoundException;
use App\Api\V2\Filter\LocaleFilter;
use App\Api\V2\Resource\Content\Page;
use App\Api\V2\Mapper\Content\MenuMapper;
use App\Api\V2\Mapper\Content\PageMapper;
use App\Service\Api\Content\Page\ApiPageService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @implements ProviderInterface<Page>
 */
final readonly class PageProvider implements ProviderInterface
{
    public function __construct(
        private ApiPageService $apiPageService,
        private TranslatorInterface $translator,
    ) {}

    /**
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return Page
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Page
    {
        $locale = $this->apiPageService->resolveLocale(...LocaleFilter::getValues($operation));

        $entity = $this->apiPageService->findPublishedPage((int) $uriVariables['id']);
        if ($entity === null) {
            throw new NotFoundException(
                $this->translator->trans('api_errors.find.page.not.found', domain: 'api_errors'),
            );
        }

        $menuMapper = new MenuMapper($locale);
        $menus = array_map($menuMapper->map(...), $this->apiPageService->getMenusForPage($entity));

        return (new PageMapper($locale))->map($entity, $menus);
    }
}
