<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Provider d'une question de FAQ de l'administration
 */

namespace App\Api\V2\State\Provider\Admin\Content;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Api\V2\Exception\NotFoundException;
use App\Api\V2\Mapper\Admin\Content\AdminFaqQuestionMapper;
use App\Api\V2\Resource\Admin\Content\AdminFaqQuestion;
use App\Api\V2\Security\AdminPermissionResolver;
use App\Entity\Admin\Content\Faq\FaqQuestion;
use App\Service\Api\Admin\Content\ApiAdminFaqService;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @implements ProviderInterface<AdminFaqQuestion>
 */
final readonly class AdminFaqQuestionProvider implements ProviderInterface
{
    public function __construct(
        private ApiAdminFaqService $faqService,
        private AdminPermissionResolver $permissionResolver,
        private TranslatorInterface $translator,
    ) {}

    /**
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return AdminFaqQuestion
     * @throws NotFoundException
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): AdminFaqQuestion
    {
        $question =
            $this->faqService->findQuestion((int) ($uriVariables['id'] ?? 0)) ??
            throw new NotFoundException($this->translator->trans('faq.error.question_not_found', domain: 'faq'));
        return $this->map($question);
    }

    /**
     * Ressource complète d'une question, partagée avec les processors
     * @param FaqQuestion $question
     * @return AdminFaqQuestion
     */
    public function map(FaqQuestion $question): AdminFaqQuestion
    {
        $mapper = new AdminFaqQuestionMapper(
            $this->faqService->getLocales(),
            $this->faqService->getContentLocale(),
            $this->faqService->getDefaultLocale(),
        );
        return $mapper->map($question, $this->permissionResolver->forContent($question->getFaqCategory()?->getFaq()));
    }
}
