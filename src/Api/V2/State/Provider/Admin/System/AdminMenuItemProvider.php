<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Provider d'une entrée du menu du back-office visible par l'utilisateur de la session
 */

namespace App\Api\V2\State\Provider\Admin\System;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Api\V2\Exception\NotFoundException;
use App\Api\V2\Mapper\Admin\System\AdminMenuItemMapper;
use App\Api\V2\Resource\Admin\System\AdminMenuItem;
use App\Service\Admin\Module\AdminMenuBuilder;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @implements ProviderInterface<AdminMenuItem>
 */
final readonly class AdminMenuItemProvider implements ProviderInterface
{
    public function __construct(private AdminMenuBuilder $menuBuilder, private TranslatorInterface $translator) {}

    /**
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return AdminMenuItem
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): AdminMenuItem
    {
        $id = (string) ($uriVariables['id'] ?? '');
        foreach ($this->menuBuilder->getListedNodes() as $node) {
            if ($node->item->id === $id) {
                return (new AdminMenuItemMapper())->map($node);
            }
        }
        throw new NotFoundException(
            $this->translator->trans('api_errors.find.admin_menu_item.not_found', domain: 'api_errors'),
        );
    }
}
