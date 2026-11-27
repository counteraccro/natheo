<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Modifie un tag : droit de modification pour la couleur, les libellés ou un PATCH sans effet, de désactivation pour le statut
 */

namespace App\Api\V2\State\Processor\Admin\Content;

use ApiPlatform\Metadata\Operation;
use App\Api\V2\Input\Admin\Content\UpdateTagInput;
use App\Api\V2\Resource\Admin\Content\AdminTag;

/**
 * @extends AbstractAdminTagProcessor<UpdateTagInput, AdminTag>
 */
final class UpdateAdminTagProcessor extends AbstractAdminTagProcessor
{
    /**
     * @param mixed $data
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return AdminTag
     */
    protected function write(mixed $data, Operation $operation, array $uriVariables, array $context): AdminTag
    {
        $tag = $this->findTag($uriVariables);
        foreach ($this->getUpdateAttributes($data, $tag->isDisabled()) as $attribute) {
            $this->denyUnlessGranted($attribute, $tag);
        }
        $tag = $this->tagService->update($tag, $this->toLabels($data->translations), $data->color, $data->disabled);
        return $this->tagProvider->map($tag);
    }
}
