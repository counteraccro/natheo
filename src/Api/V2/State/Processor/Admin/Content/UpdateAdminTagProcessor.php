<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Modifie un tag : droit de modification pour la couleur et les libellés, de désactivation pour le statut
 */

namespace App\Api\V2\State\Processor\Admin\Content;

use ApiPlatform\Metadata\Operation;
use App\Api\V2\Input\Admin\Content\UpdateTagInput;
use App\Api\V2\Resource\Admin\Content\AdminTag;
use App\Enum\Admin\System\ActionLog\ActionLogAction;
use App\Security\Voter\ContentVoter;

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
        if ($data->color !== null || $data->translations !== null) {
            $this->denyUnlessGranted(ContentVoter::EDIT, $tag);
        }
        if ($data->disabled !== null && $data->disabled !== $tag->isDisabled()) {
            $this->denyUnlessGranted(ContentVoter::DISABLE, $tag);
        }
        $tag = $this->tagService->update($tag, $this->toLabels($data->translations), $data->color, $data->disabled);
        return $this->tagProvider->map($tag);
    }

    /**
     * disable ou enable quand seul le statut change
     * @param Operation $operation
     * @param mixed $data
     * @return string
     */
    protected function getLogAction(Operation $operation, mixed $data): string
    {
        if ($data instanceof UpdateTagInput && $data->isStatusOnly()) {
            return ($data->disabled ? ActionLogAction::DISABLE : ActionLogAction::ENABLE)->value;
        }
        return parent::getLogAction($operation, $data);
    }
}
