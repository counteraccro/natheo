<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Provider d'un menu actif
 */

namespace App\Api\V2\State\Provider\Content;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Api\V2\Exception\NotFoundException;
use App\Api\V2\Resource\Content\Menu;
use App\Api\V2\Mapper\Content\MenuMapper;
use App\Service\Api\Content\ApiMenuService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @implements ProviderInterface<Menu>
 */
final readonly class MenuProvider implements ProviderInterface
{
    public function __construct(private ApiMenuService $apiMenuService, private TranslatorInterface $translator) {}

    /**
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return Menu
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Menu
    {
        $locale = $this->apiMenuService->getContentLocale();
        $menu = $this->apiMenuService->findActiveMenu((int) $uriVariables['id']);
        if ($menu === null) {
            throw new NotFoundException(
                $this->translator->trans('api_errors.find.menu.not.found', domain: 'api_errors'),
            );
        }
        return (new MenuMapper($locale))->map($menu);
    }
}
