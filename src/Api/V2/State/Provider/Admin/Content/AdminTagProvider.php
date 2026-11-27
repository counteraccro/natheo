<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Provider d'un tag de l'administration
 */

namespace App\Api\V2\State\Provider\Admin\Content;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Api\V2\Exception\NotFoundException;
use App\Api\V2\Mapper\Admin\Content\AdminTagMapper;
use App\Api\V2\Resource\Admin\Content\AdminTag;
use App\Api\V2\Security\AdminPermissionResolver;
use App\Entity\Admin\Content\Tag\Tag;
use App\Service\Api\Admin\Content\ApiAdminTagService;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @implements ProviderInterface<AdminTag>
 */
final readonly class AdminTagProvider implements ProviderInterface
{
    public function __construct(
        private ApiAdminTagService $tagService,
        private AdminPermissionResolver $permissionResolver,
        private TranslatorInterface $translator,
    ) {}

    /**
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return AdminTag
     * @throws NotFoundException
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): AdminTag
    {
        $tag =
            $this->tagService->find((int) ($uriVariables['id'] ?? 0)) ??
            throw new NotFoundException($this->translator->trans('tag.error.not_found', domain: 'tag'));
        return $this->map($tag);
    }

    /**
     * Ressource complète d'un tag, partagée avec les processors
     * @param Tag $tag
     * @return AdminTag
     */
    public function map(Tag $tag): AdminTag
    {
        $mapper = new AdminTagMapper(
            $this->tagService->getLocales(),
            $this->tagService->getContentLocale(),
            $this->tagService->getDefaultLocale(),
        );
        return $mapper->map($tag, $this->tagService->countPages($tag), $this->permissionResolver->forContent($tag));
    }
}
