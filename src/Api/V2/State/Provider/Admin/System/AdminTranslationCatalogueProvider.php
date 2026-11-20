<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Provider API v2 : catalogue de traduction d'un domaine dans la langue de la requête
 */

namespace App\Api\V2\State\Provider\Admin\System;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Api\V2\Exception\NotFoundException;
use App\Api\V2\Mapper\Admin\System\AdminTranslationCatalogueMapper;
use App\Api\V2\Resource\Admin\System\AdminTranslationCatalogue;
use App\Service\Api\System\TranslationCatalogueService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @implements ProviderInterface<AdminTranslationCatalogue>
 */
final readonly class AdminTranslationCatalogueProvider implements ProviderInterface
{
    public function __construct(
        private TranslationCatalogueService $catalogueService,
        private TranslatorInterface $translator,
    ) {}

    /**
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return AdminTranslationCatalogue
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function provide(
        Operation $operation,
        array $uriVariables = [],
        array $context = [],
    ): AdminTranslationCatalogue {
        $domain = (string) ($uriVariables['domain'] ?? '');
        if (!$this->catalogueService->hasDomain($domain)) {
            throw new NotFoundException(
                $this->translator->trans('api_errors.find.translation_catalogue.not_found', domain: 'api_errors'),
            );
        }

        $locale = $this->catalogueService->getContentLocale();
        return (new AdminTranslationCatalogueMapper())->map(
            $domain,
            $locale,
            $this->catalogueService->getMessages($domain, $locale),
        );
    }
}
