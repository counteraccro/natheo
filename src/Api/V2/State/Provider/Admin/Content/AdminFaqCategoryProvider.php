<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Provider d'une catégorie de FAQ de l'administration
 */

namespace App\Api\V2\State\Provider\Admin\Content;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Api\V2\Exception\NotFoundException;
use App\Api\V2\Mapper\Admin\Content\AdminFaqCategoryMapper;
use App\Api\V2\Resource\Admin\Content\AdminFaqCategory;
use App\Api\V2\Security\AdminPermissionResolver;
use App\Entity\Admin\Content\Faq\FaqCategory;
use App\Service\Api\Admin\Content\ApiAdminFaqService;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @implements ProviderInterface<AdminFaqCategory>
 */
final readonly class AdminFaqCategoryProvider implements ProviderInterface
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
     * @return AdminFaqCategory
     * @throws NotFoundException
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): AdminFaqCategory
    {
        $category =
            $this->faqService->findCategory((int) ($uriVariables['id'] ?? 0)) ??
            throw new NotFoundException($this->translator->trans('faq.error.category_not_found', domain: 'faq'));

        // Catégorie désignée par son IRI dans un corps de requête : seules son identité et sa FAQ servent
        if ($context['api_denormalize'] ?? false) {
            return $this->getMapper()->mapSummary((int) $category->getId(), (int) $category->getFaq()?->getId());
        }
        return $this->map($category);
    }

    /**
     * Ressource complète d'une catégorie, partagée avec les processors
     * @param FaqCategory $category
     * @return AdminFaqCategory
     */
    public function map(FaqCategory $category): AdminFaqCategory
    {
        $faq = $category->getFaq();
        return $this->getMapper()->map($category, $this->permissionResolver->forContent($faq));
    }

    /**
     * @return AdminFaqCategoryMapper
     */
    private function getMapper(): AdminFaqCategoryMapper
    {
        return new AdminFaqCategoryMapper(
            $this->faqService->getLocales(),
            $this->faqService->getContentLocale(),
            $this->faqService->getDefaultLocale(),
        );
    }
}
