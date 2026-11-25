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
use App\Api\V2\Security\CurrentUserResolver;
use App\Enum\Admin\Module\CoreModuleId;
use App\Enum\Admin\System\ActionLog\ActionLogAction;
use App\Security\Voter\ContentVoter;
use App\Service\Api\Admin\ActionLogService;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * @extends AbstractAdminFaqProcessor<UpdateFaqCategoryInput, AdminFaqCategory>
 */
final class UpdateAdminFaqCategoryProcessor extends AbstractAdminFaqProcessor
{
    private ActionLogService $actionLogService;

    private CurrentUserResolver $currentUserResolver;

    /**
     * @param ActionLogService $actionLogService
     * @param CurrentUserResolver $currentUserResolver
     * @return void
     */
    #[Required]
    public function setLogServices(ActionLogService $actionLogService, CurrentUserResolver $currentUserResolver): void
    {
        $this->actionLogService = $actionLogService;
        $this->currentUserResolver = $currentUserResolver;
    }

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
        $statusChanged = $data->disabled !== null && $data->disabled !== $category->isDisabled();
        // Un PATCH sans effet est écrit et journalisé : il exige le droit de modification
        if ($data->translations !== null || $data->position !== null || !$statusChanged) {
            $this->denyUnlessGranted(ContentVoter::EDIT, $faq);
        }
        if ($statusChanged) {
            $this->denyUnlessGranted(ContentVoter::DISABLE, $faq);
        }

        $disabledQuestions = $this->faqService->updateCategory(
            $category,
            $this->toTitles($data->translations),
            $data->disabled,
            $data->position,
        );
        foreach ($disabledQuestions as $questionId) {
            $this->actionLogService->log(
                $this->currentUserResolver->getSession(),
                CoreModuleId::FAQ->value,
                ActionLogAction::DISABLE->value,
                'AdminFaqQuestion',
                (string) $questionId,
            );
        }
        return $this->categoryProvider->map($category);
    }
}
