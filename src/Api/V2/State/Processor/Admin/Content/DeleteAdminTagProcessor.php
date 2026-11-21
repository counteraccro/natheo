<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Supprime un tag qui n'est rattaché à aucune page
 */

namespace App\Api\V2\State\Processor\Admin\Content;

use ApiPlatform\Metadata\Operation;
use App\Security\Voter\ContentVoter;

/**
 * @extends AbstractAdminTagProcessor<mixed, null>
 */
final class DeleteAdminTagProcessor extends AbstractAdminTagProcessor
{
    /**
     * @param mixed $data
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return null
     */
    protected function write(mixed $data, Operation $operation, array $uriVariables, array $context): null
    {
        $tag = $this->findTag($uriVariables);
        $this->denyUnlessGranted(ContentVoter::DELETE, $tag);
        $this->tagService->delete($tag);
        return null;
    }
}
