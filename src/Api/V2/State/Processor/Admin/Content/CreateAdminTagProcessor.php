<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Crée un tag, l'utilisateur de la session en étant l'auteur
 */

namespace App\Api\V2\State\Processor\Admin\Content;

use ApiPlatform\Metadata\Operation;
use App\Api\V2\Input\Admin\Content\CreateTagInput;
use App\Api\V2\Resource\Admin\Content\AdminTag;
use App\Api\V2\Security\CurrentUserResolver;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * @extends AbstractAdminTagProcessor<CreateTagInput, AdminTag>
 */
final class CreateAdminTagProcessor extends AbstractAdminTagProcessor
{
    private CurrentUserResolver $currentUserResolver;

    /**
     * @param CurrentUserResolver $currentUserResolver
     * @return void
     */
    #[Required]
    public function setCurrentUserResolver(CurrentUserResolver $currentUserResolver): void
    {
        $this->currentUserResolver = $currentUserResolver;
    }

    /**
     * @param mixed $data
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return AdminTag
     */
    protected function write(mixed $data, Operation $operation, array $uriVariables, array $context): AdminTag
    {
        $tag = $this->tagService->create(
            $this->currentUserResolver->getUser(),
            $this->toLabels($data->translations),
            $data->color,
            $data->disabled,
        );
        return $this->tagProvider->map($tag);
    }
}
