<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Crée une catégorie en dernière position de sa FAQ, avec le droit de modification de la FAQ
 */

namespace App\Api\V2\State\Processor\Admin\Content;

use ApiPlatform\Metadata\Operation;
use App\Api\V2\Input\Admin\Content\CreateFaqCategoryInput;
use App\Api\V2\Resource\Admin\Content\AdminFaqCategory;
use App\Security\Voter\ContentVoter;

/**
 * @extends AbstractAdminFaqProcessor<CreateFaqCategoryInput, AdminFaqCategory>
 */
final class CreateAdminFaqCategoryProcessor extends AbstractAdminFaqProcessor
{
    /**
     * @param mixed $data
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return AdminFaqCategory
     */
    protected function write(mixed $data, Operation $operation, array $uriVariables, array $context): AdminFaqCategory
    {
        $faq = $this->findFaq((int) $data->faq?->id);
        $this->denyUnlessGranted(ContentVoter::EDIT, $faq);
        $category = $this->faqService->createCategory($faq, $this->toTitles($data->translations), $data->disabled);
        return $this->categoryProvider->map($category);
    }
}
