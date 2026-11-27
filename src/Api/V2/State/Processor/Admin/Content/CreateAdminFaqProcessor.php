<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Crée une FAQ, l'utilisateur de la session en étant l'auteur
 */

namespace App\Api\V2\State\Processor\Admin\Content;

use ApiPlatform\Metadata\Operation;
use App\Api\V2\Input\Admin\Content\CreateFaqInput;
use App\Api\V2\Resource\Admin\Content\AdminFaq;
use App\Api\V2\Security\CurrentUserResolver;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * @extends AbstractAdminFaqProcessor<CreateFaqInput, AdminFaq>
 */
final class CreateAdminFaqProcessor extends AbstractAdminFaqProcessor
{
    private CurrentUserResolver $currentUserResolver;

    /**
     * @param CurrentUserResolver $currentUserResolver
     * @return void
     */
    #[Required]
    public function setCurrentUserResolver(CurrentUserResolver $currentUserResolver): void
    {
        $this->currentUserResolver = $currentUserResolver;
    }

    /**
     * @param mixed $data
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return AdminFaq
     */
    protected function write(mixed $data, Operation $operation, array $uriVariables, array $context): AdminFaq
    {
        $faq = $this->faqService->create(
            $this->currentUserResolver->getUser(),
            $this->toTitles($data->translations),
            $data->disabled,
        );
        return $this->faqProvider->map($faq);
    }
}
