<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Provider d'une FAQ de l'administration, avec son arbre
 */

namespace App\Api\V2\State\Provider\Admin\Content;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Api\V2\Exception\NotFoundException;
use App\Api\V2\Mapper\Admin\Content\AdminFaqMapper;
use App\Api\V2\Resource\Admin\Content\AdminFaq;
use App\Api\V2\Security\AdminPermissionResolver;
use App\Entity\Admin\Content\Faq\Faq;
use App\Service\Api\Admin\Content\ApiAdminFaqService;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @implements ProviderInterface<AdminFaq>
 */
final readonly class AdminFaqProvider implements ProviderInterface
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
     * @return AdminFaq
     * @throws NotFoundException
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): AdminFaq
    {
        $faq =
            $this->faqService->find((int) ($uriVariables['id'] ?? 0)) ??
            throw new NotFoundException($this->translator->trans('faq.error.not_found', domain: 'faq'));

        // FAQ désignée par son IRI dans un corps de requête : seule son identité sert
        if ($context['api_denormalize'] ?? false) {
            $resource = new AdminFaq();
            $resource->id = (int) $faq->getId();
            return $resource;
        }
        return $this->map($faq);
    }

    /**
     * Ressource complète d'une FAQ, partagée avec les processors
     * @param Faq $faq
     * @return AdminFaq
     */
    public function map(Faq $faq): AdminFaq
    {
        $mapper = new AdminFaqMapper(
            $this->faqService->getLocales(),
            $this->faqService->getContentLocale(),
            $this->faqService->getDefaultLocale(),
        );
        return $mapper->map(
            $faq,
            $this->faqService->getTreeCategories($faq),
            $this->faqService->getTreeQuestionRows($faq),
            $this->faqService->countPages($faq),
            $this->permissionResolver->forContent($faq),
        );
    }
}
