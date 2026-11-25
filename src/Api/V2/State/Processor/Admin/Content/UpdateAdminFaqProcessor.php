<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Modifie une FAQ : droit de modification pour les titres ou un PATCH sans effet, de désactivation pour le statut
 */

namespace App\Api\V2\State\Processor\Admin\Content;

use ApiPlatform\Metadata\Operation;
use App\Api\V2\Input\Admin\Content\UpdateFaqInput;
use App\Api\V2\Resource\Admin\Content\AdminFaq;
use App\Security\Voter\ContentVoter;

/**
 * @extends AbstractAdminFaqProcessor<UpdateFaqInput, AdminFaq>
 */
final class UpdateAdminFaqProcessor extends AbstractAdminFaqProcessor
{
    /**
     * @param mixed $data
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return AdminFaq
     */
    protected function write(mixed $data, Operation $operation, array $uriVariables, array $context): AdminFaq
    {
        $faq = $this->findFaq((int) ($uriVariables['id'] ?? 0));
        $statusChanged = $data->disabled !== null && $data->disabled !== $faq->isDisabled();
        // Un PATCH sans effet est écrit et journalisé : il exige le droit de modification
        if ($data->translations !== null || !$statusChanged) {
            $this->denyUnlessGranted(ContentVoter::EDIT, $faq);
        }
        if ($statusChanged) {
            $this->denyUnlessGranted(ContentVoter::DISABLE, $faq);
        }
        $faq = $this->faqService->update($faq, $this->toTitles($data->translations), $data->disabled);
        return $this->faqProvider->map($faq);
    }
}
