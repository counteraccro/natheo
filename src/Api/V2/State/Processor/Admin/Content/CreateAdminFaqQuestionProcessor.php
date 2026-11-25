<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Crée une question en dernière position de sa catégorie, avec le droit de modification de la FAQ
 */

namespace App\Api\V2\State\Processor\Admin\Content;

use ApiPlatform\Metadata\Operation;
use App\Api\V2\Input\Admin\Content\CreateFaqQuestionInput;
use App\Api\V2\Resource\Admin\Content\AdminFaqQuestion;
use App\Security\Voter\ContentVoter;

/**
 * @extends AbstractAdminFaqProcessor<CreateFaqQuestionInput, AdminFaqQuestion>
 */
final class CreateAdminFaqQuestionProcessor extends AbstractAdminFaqProcessor
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
        $category = $this->findCategory((int) $data->category?->id);
        $this->denyUnlessGranted(ContentVoter::EDIT, $category->getFaq());
        $question = $this->faqService->createQuestion(
            $category,
            $this->toQuestionTexts($data->translations),
            $data->disabled,
        );
        return $this->questionProvider->map($question);
    }
}
