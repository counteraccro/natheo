<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Modifie une catégorie de FAQ : droit de modification de la FAQ pour les titres et la position, de désactivation
 * pour le statut ; les questions désactivées avec la catégorie ont chacune leur ligne de journal
 */

namespace App\Api\V2\State\Processor\Admin\Content;

use ApiPlatform\Metadata\Operation;
use App\Api\V2\Input\Admin\Content\UpdateFaqCategoryInput;
use App\Api\V2\Resource\Admin\Content\AdminFaqCategory;
use App\Api\V2\Resource\Admin\Content\AdminFaqQuestion;
use App\Enum\Admin\System\ActionLog\ActionLogAction;

/**
 * @extends AbstractAdminFaqProcessor<UpdateFaqCategoryInput, AdminFaqCategory>
 */
final class UpdateAdminFaqCategoryProcessor extends AbstractAdminFaqProcessor
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
        $category = $this->findCategory((int) ($uriVariables['id'] ?? 0));
        $faq = $category->getFaq();
        foreach ($this->getUpdateAttributes($data, $category->isDisabled()) as $attribute) {
            $this->denyUnlessGranted($attribute, $faq);
        }

        $disabledQuestions = $this->faqService->updateCategory(
            $category,
            $this->toTitles($data->translations),
            $data->disabled,
            $data->position,
        );
        $defaultLocale = $this->faqService->getDefaultLocale();
        foreach ($disabledQuestions as $question) {
            $this->log(
                $operation,
                ActionLogAction::DISABLE->value,
                (new \ReflectionClass(AdminFaqQuestion::class))->getShortName(),
                (string) $question->getId(),
                $question->getFaqQuestionTranslationByLocale($defaultLocale)?->getTitle(),
            );
        }
        return $this->categoryProvider->map($category);
    }
}
