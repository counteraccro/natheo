<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Supprime une catégorie de FAQ et ses questions, avec le droit de suppression de la FAQ
 */

namespace App\Api\V2\State\Processor\Admin\Content;

use ApiPlatform\Metadata\Operation;
use App\Security\Voter\ContentVoter;

/**
 * @extends AbstractAdminFaqProcessor<mixed, null>
 */
final class DeleteAdminFaqCategoryProcessor extends AbstractAdminFaqProcessor
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
        $category = $this->findCategory((int) ($uriVariables['id'] ?? 0));
        $this->denyUnlessGranted(ContentVoter::DELETE, $category->getFaq());
        $this->faqService->deleteCategory($category);
        return null;
    }
}
