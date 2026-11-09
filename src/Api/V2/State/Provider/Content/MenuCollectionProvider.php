<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Provider des menus affichés sur une page publiée
 */

namespace App\Api\V2\State\Provider\Content;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\QueryParameter;
use ApiPlatform\State\ProviderInterface;
use App\Api\V2\Parameter\LocaleParameter;
use App\Api\V2\Parameter\MenuPositionParameter;
use App\Api\V2\Resource\Content\Menu;
use App\Api\V2\Mapper\Content\MenuMapper;
use App\Service\Api\Content\Page\ApiPageService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @implements ProviderInterface<Menu>
 */
final readonly class MenuCollectionProvider implements ProviderInterface
{
    public function __construct(
        private ApiPageService $apiPageService,
        private TranslatorInterface $translator,
    ) {}

    /**
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return Menu[]
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $locale = $this->apiPageService->resolveLocale(LocaleParameter::getValueFrom($operation));
        $parameters = $operation->getParameters();
        $slug = (string) $parameters?->get('page', QueryParameter::class)?->getValue('');
        $position = MenuPositionParameter::getValueFrom($operation);

        $page = $slug === '' ? null : $this->apiPageService->findPublishedPageBySlug($slug, $locale);
        if ($page === null) {
            throw new NotFoundHttpException(
                $this->translator->trans('api_errors.find.page.not.found', domain: 'api_errors'),
            );
        }

        $mapper = new MenuMapper($locale);
        $menus = [];
        foreach ($this->apiPageService->getMenusForPage($page) as $menu) {
            if ($position === null || $menu->getPosition() === $position->value) {
                $menus[] = $mapper->map($menu);
            }
        }
        return $menus;
    }
}
