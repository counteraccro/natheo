<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Modifie une question de FAQ : droit de modification de la FAQ pour les textes, la catégorie et la position, de
 * désactivation pour le statut
 */

namespace App\Api\V2\State\Processor\Admin\Content;

use ApiPlatform\Metadata\Operation;
use App\Api\V2\Input\Admin\Content\UpdateFaqQuestionInput;
use App\Api\V2\Resource\Admin\Content\AdminFaqQuestion;

/**
 * @extends AbstractAdminFaqProcessor<UpdateFaqQuestionInput, AdminFaqQuestion>
 */
final class UpdateAdminFaqQuestionProcessor extends AbstractAdminFaqProcessor
{
    /**
     * @param mixed $data
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return AdminFaqQuestion
     */
    protected function write(mixed $data, Operation $operation, array $uriVariables, array $context): AdminFaqQuestion
    {
        $question = $this->findQuestion((int) ($uriVariables['id'] ?? 0));
        $faq = $question->getFaqCategory()?->getFaq();
        foreach ($this->getUpdateAttributes($data, $question->isDisabled()) as $attribute) {
            $this->denyUnlessGranted($attribute, $faq);
        }

        $category = $data->category === null ? null : $this->findCategory($data->category->id);
        $question = $this->faqService->updateQuestion(
            $question,
            $this->toQuestionTexts($data->translations),
            $data->disabled,
            $category,
            $data->position,
        );
        return $this->questionProvider->map($question);
    }
}
