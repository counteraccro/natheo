<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Base des processors des tags : tag lu, droits de ContentVoter, libellé du journal dans la langue par défaut
 */

namespace App\Api\V2\State\Processor\Admin\Content;

use App\Api\V2\Exception\ForbiddenException;
use App\Api\V2\Exception\NotFoundException;
use App\Api\V2\Input\Admin\Global\LabelTranslationInput;
use App\Api\V2\Resource\Admin\Content\AdminTag;
use App\Api\V2\State\Processor\Admin\AbstractAdminProcessor;
use App\Api\V2\State\Provider\Admin\Content\AdminTagProvider;
use App\Entity\Admin\Content\Tag\Tag;
use App\Service\Api\Admin\Content\ApiAdminTagService;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @template T1
 * @template T2
 * @extends AbstractAdminProcessor<T1, T2>
 */
abstract class AbstractAdminTagProcessor extends AbstractAdminProcessor
{
    public function __construct(
        protected readonly ApiAdminTagService $tagService,
        protected readonly AdminTagProvider $tagProvider,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly TranslatorInterface $translator,
    ) {}

    /**
     * Tag de l'adresse
     * @param array $uriVariables
     * @return Tag
     * @throws NotFoundException
     */
    protected function findTag(array $uriVariables): Tag
    {
        return $this->tagService->find((int) ($uriVariables['id'] ?? 0)) ??
            throw new NotFoundException($this->translator->trans('tag.error.not_found', domain: 'tag'));
    }

    /**
     * @param string $attribute droit de ContentVoter
     * @param Tag $tag
     * @return void
     * @throws ForbiddenException
     */
    protected function denyUnlessGranted(string $attribute, Tag $tag): void
    {
        if (!$this->authorizationChecker->isGranted($attribute, $tag)) {
            throw new ForbiddenException($this->translator->trans('tag.error.forbidden', domain: 'tag'));
        }
    }

    /**
     * Libellés indexés par langue
     * @param LabelTranslationInput[]|null $translations
     * @return array<string, string>
     */
    protected function toLabels(?array $translations): array
    {
        $labels = [];
        foreach ($translations ?? [] as $translation) {
            $labels[$translation->locale] = $translation->label;
        }
        return $labels;
    }

    /**
     * @param mixed $resource
     * @return string|null
     */
    protected function getLogLabel(mixed $resource): ?string
    {
        if (!($resource instanceof AdminTag)) {
            return null;
        }
        return $resource->getTranslationValue($this->tagService->getDefaultLocale(), 'label') ?? $resource->label;
    }
}
