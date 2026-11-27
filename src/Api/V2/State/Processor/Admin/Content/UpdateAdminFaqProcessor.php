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
        foreach ($this->getUpdateAttributes($data, $faq->isDisabled()) as $attribute) {
            $this->denyUnlessGranted($attribute, $faq);
        }
        $faq = $this->faqService->update($faq, $this->toTitles($data->translations), $data->disabled);
        return $this->faqProvider->map($faq);
    }
}
