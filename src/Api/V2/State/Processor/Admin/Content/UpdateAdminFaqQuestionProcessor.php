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
use App\Security\Voter\ContentVoter;

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
        $statusChanged = $data->disabled !== null && $data->disabled !== $question->isDisabled();
        // Un PATCH sans effet est écrit et journalisé : il exige le droit de modification
        if ($data->translations !== null || $data->position !== null || $data->category !== null || !$statusChanged) {
            $this->denyUnlessGranted(ContentVoter::EDIT, $faq);
        }
        if ($statusChanged) {
            $this->denyUnlessGranted(ContentVoter::DISABLE, $faq);
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
