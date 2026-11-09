<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Provider d'une page publiée : par id, par slug ou page d'accueil
 */

namespace App\Api\V2\State\Provider\Content;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Api\V2\Parameter\LocaleParameter;
use App\Api\V2\Resource\Content\Page;
use App\Formatter\Api\V2\Content\MenuFormatter;
use App\Formatter\Api\V2\Content\PageFormatter;
use App\Service\Api\Content\ApiMenuService;
use App\Service\Api\Content\Page\ApiPageService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @implements ProviderInterface<Page>
 */
final readonly class PageProvider implements ProviderInterface
{
    public function __construct(
        private ApiPageService $apiPageService,
        private ApiMenuService $apiMenuService,
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
        $locale = $this->apiPageService->resolveLocale(LocaleParameter::getValueFrom($operation));

        if (isset($uriVariables['id'])) {
            $entity = $this->apiPageService->findPublishedPage((int) $uriVariables['id']);
        } else {
            // Slug vide : page d'accueil
            $entity = $this->apiPageService->findPublishedPageBySlug((string) ($uriVariables['slug'] ?? ''), $locale);
        }

        if ($entity === null) {
            throw new NotFoundHttpException(
                $this->translator->trans('api_errors.find.page.not.found', domain: 'api_errors'),
            );
        }

        $menuFormatter = new MenuFormatter($locale);
        $menus = [];
        foreach ($this->apiPageService->getMenuIdsForPage($entity) as $menu) {
            $data = $this->apiMenuService->findMenuForApi($menu['id']);
            if (!empty($data)) {
                $menus[] = $menuFormatter->format($data);
            }
        }

        return (new PageFormatter($locale))->format($entity, $menus);
    }
}
