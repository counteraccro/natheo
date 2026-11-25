<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Base des processors des FAQ, de leurs catégories et de leurs questions : éléments lus, droits de ContentVoter
 * sur la FAQ, libellé du journal dans la langue par défaut
 */

namespace App\Api\V2\State\Processor\Admin\Content;

use ApiPlatform\Metadata\Operation;
use App\Api\V2\Exception\ForbiddenException;
use App\Api\V2\Exception\NotFoundException;
use App\Api\V2\Input\Admin\Content\FaqQuestionTranslationInput;
use App\Api\V2\Input\Admin\Global\TitleTranslationInput;
use App\Api\V2\Resource\Admin\Content\AdminFaq;
use App\Api\V2\Resource\Admin\Content\AdminFaqCategory;
use App\Api\V2\Resource\Admin\Content\AdminFaqQuestion;
use App\Api\V2\State\Processor\Admin\AbstractAdminProcessor;
use App\Api\V2\State\Provider\Admin\Content\AdminFaqCategoryProvider;
use App\Api\V2\State\Provider\Admin\Content\AdminFaqProvider;
use App\Api\V2\State\Provider\Admin\Content\AdminFaqQuestionProvider;
use App\Entity\Admin\Content\Faq\Faq;
use App\Entity\Admin\Content\Faq\FaqCategory;
use App\Entity\Admin\Content\Faq\FaqQuestion;
use App\Enum\Admin\System\ActionLog\ActionLogAction;
use App\Service\Api\Admin\Content\ApiAdminFaqService;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @template T1
 * @template T2
 * @extends AbstractAdminProcessor<T1, T2>
 */
abstract class AbstractAdminFaqProcessor extends AbstractAdminProcessor
{
    public function __construct(
        protected readonly ApiAdminFaqService $faqService,
        protected readonly AdminFaqProvider $faqProvider,
        protected readonly AdminFaqCategoryProvider $categoryProvider,
        protected readonly AdminFaqQuestionProvider $questionProvider,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly TranslatorInterface $translator,
    ) {}

    /**
     * @param int $id
     * @return Faq
     * @throws NotFoundException
     */
    protected function findFaq(int $id): Faq
    {
        return $this->faqService->find($id) ??
            throw new NotFoundException($this->translator->trans('faq.error.not_found', domain: 'faq'));
    }

    /**
     * @param int $id
     * @return FaqCategory
     * @throws NotFoundException
     */
    protected function findCategory(int $id): FaqCategory
    {
        return $this->faqService->findCategory($id) ??
            throw new NotFoundException($this->translator->trans('faq.error.category_not_found', domain: 'faq'));
    }

    /**
     * @param int $id
     * @return FaqQuestion
     * @throws NotFoundException
     */
    protected function findQuestion(int $id): FaqQuestion
    {
        return $this->faqService->findQuestion($id) ??
            throw new NotFoundException($this->translator->trans('faq.error.question_not_found', domain: 'faq'));
    }

    /**
     * @param string $attribute droit de ContentVoter
     * @param Faq|null $faq
     * @return void
     * @throws ForbiddenException
     */
    protected function denyUnlessGranted(string $attribute, ?Faq $faq): void
    {
        if ($faq === null || !$this->authorizationChecker->isGranted($attribute, $faq)) {
            throw new ForbiddenException($this->translator->trans('faq.error.forbidden', domain: 'faq'));
        }
    }

    /**
     * Titres indexés par langue
     * @param TitleTranslationInput[]|null $translations
     * @return array<string, string>
     */
    protected function toTitles(?array $translations): array
    {
        $titles = [];
        foreach ($translations ?? [] as $translation) {
            $titles[$translation->locale] = $translation->title;
        }
        return $titles;
    }

    /**
     * Titre et réponse indexés par langue
     * @param FaqQuestionTranslationInput[]|null $translations
     * @return array<string, array{title: string, answer: string}>
     */
    protected function toQuestionTexts(?array $translations): array
    {
        $texts = [];
        foreach ($translations ?? [] as $translation) {
            $texts[$translation->locale] = ['title' => $translation->title, 'answer' => $translation->answer];
        }
        return $texts;
    }

    /**
     * disable ou enable quand seul le statut change
     * @param Operation $operation
     * @param mixed $data
     * @return string
     */
    protected function getLogAction(Operation $operation, mixed $data): string
    {
        if (is_object($data) && method_exists($data, 'isStatusOnly') && $data->isStatusOnly()) {
            return ($data->disabled ? ActionLogAction::DISABLE : ActionLogAction::ENABLE)->value;
        }
        return parent::getLogAction($operation, $data);
    }

    /**
     * @param mixed $resource
     * @return string|null
     */
    protected function getLogLabel(mixed $resource): ?string
    {
        if (
            !($resource instanceof AdminFaq) &&
            !($resource instanceof AdminFaqCategory) &&
            !($resource instanceof AdminFaqQuestion)
        ) {
            return null;
        }
        return $resource->getTitleByLocale($this->faqService->getDefaultLocale()) ?? $resource->title;
    }
}
