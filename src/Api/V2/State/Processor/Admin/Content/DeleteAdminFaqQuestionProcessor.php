<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Supprime une question de FAQ, avec le droit de suppression de la FAQ
 */

namespace App\Api\V2\State\Processor\Admin\Content;

use ApiPlatform\Metadata\Operation;
use App\Security\Voter\ContentVoter;

/**
 * @extends AbstractAdminFaqProcessor<mixed, null>
 */
final class DeleteAdminFaqQuestionProcessor extends AbstractAdminFaqProcessor
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
        $question = $this->findQuestion((int) ($uriVariables['id'] ?? 0));
        $this->denyUnlessGranted(ContentVoter::DELETE, $question->getFaqCategory()?->getFaq());
        $this->faqService->deleteQuestion($question);
        return null;
    }
}
