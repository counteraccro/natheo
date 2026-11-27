<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Valide ValidFaqInput, messages du domaine faq rattachés au champ en erreur
 */

namespace App\Api\V2\Validator\Admin\Content;

use App\Api\V2\Input\Admin\Content\CreateFaqCategoryInput;
use App\Api\V2\Input\Admin\Content\CreateFaqInput;
use App\Api\V2\Input\Admin\Content\CreateFaqQuestionInput;
use App\Api\V2\Input\Admin\Content\FaqQuestionTranslationInput;
use App\Api\V2\Input\Admin\Content\UpdateFaqCategoryInput;
use App\Api\V2\Input\Admin\Content\UpdateFaqInput;
use App\Api\V2\Input\Admin\Content\UpdateFaqQuestionInput;
use App\Api\V2\Input\Admin\Global\TitleTranslationInput;
use App\Api\V2\Resource\Admin\Content\AdminFaqQuestion;
use App\Api\V2\Validator\AbstractTranslatedInputValidator;
use App\Service\Api\Admin\Content\ApiAdminFaqService;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

use function Symfony\Component\Translation\t;

final class ValidFaqInputValidator extends AbstractTranslatedInputValidator
{
    /**
     * Longueur maximale du titre d'une FAQ ou d'une catégorie
     * @var int
     */
    private const int TITLE_MAX_LENGTH = 255;

    public function __construct(
        private readonly ApiAdminFaqService $faqService,
        private readonly RequestStack $requestStack,
    ) {}

    /**
     * @param mixed $value
     * @param Constraint $constraint
     * @return void
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!($constraint instanceof ValidFaqInput)) {
            throw new UnexpectedTypeException($constraint, ValidFaqInput::class);
        }

        if (
            !($value instanceof CreateFaqInput) &&
            !($value instanceof UpdateFaqInput) &&
            !($value instanceof CreateFaqCategoryInput) &&
            !($value instanceof UpdateFaqCategoryInput) &&
            !($value instanceof CreateFaqQuestionInput) &&
            !($value instanceof UpdateFaqQuestionInput)
        ) {
            return;
        }
        if ($value instanceof UpdateFaqQuestionInput) {
            $this->validateCategory($value);
        }

        $isCreation =
            $value instanceof CreateFaqInput ||
            $value instanceof CreateFaqCategoryInput ||
            $value instanceof CreateFaqQuestionInput;
        $hasTitleLimit = !($value instanceof CreateFaqQuestionInput) && !($value instanceof UpdateFaqQuestionInput);
        $this->validateTranslations($value->translations ?? [], $isCreation, $hasTitleLimit);
    }

    /**
     * @param TitleTranslationInput[]|FaqQuestionTranslationInput[] $translations
     * @param bool $allRequired chaque langue du site exigée
     * @param bool $hasTitleLimit titre limité à TITLE_MAX_LENGTH
     * @return void
     */
    private function validateTranslations(array $translations, bool $allRequired, bool $hasTitleLimit): void
    {
        $locales = $this->faqService->getLocales();
        $seen = [];
        foreach (array_values($translations) as $i => $translation) {
            $path = 'translations[' . $i . ']';
            if (!in_array($translation->locale, $locales, true)) {
                $this->addViolation(
                    t('faq.error.locale', ['{locale}' => $translation->locale], 'faq'),
                    $path . '.locale',
                );
                continue;
            }
            if (isset($seen[$translation->locale])) {
                $this->addViolation(
                    t('faq.error.locale_duplicate', ['{locale}' => $translation->locale], 'faq'),
                    $path . '.locale',
                );
                continue;
            }
            $seen[$translation->locale] = true;

            $title = trim($translation->title);
            if ($title === '') {
                $this->addViolation(t('faq.error.title_blank', domain: 'faq'), $path . '.title');
            } elseif ($hasTitleLimit && mb_strlen($title) > self::TITLE_MAX_LENGTH) {
                $this->addViolation(t('faq.error.title_length', domain: 'faq'), $path . '.title');
            }
            if ($translation instanceof FaqQuestionTranslationInput && trim($translation->answer) === '') {
                $this->addViolation(t('faq.error.answer_blank', domain: 'faq'), $path . '.answer');
            }
        }

        $missing = array_diff($locales, array_keys($seen));
        if ($allRequired && $missing !== []) {
            $this->addViolation(
                t('faq.error.translations_required', ['{locales}' => implode(', ', $missing)], 'faq'),
                'translations',
            );
        }
    }

    /**
     * Une question ne change de catégorie que dans sa FAQ
     * @param UpdateFaqQuestionInput $value
     * @return void
     */
    private function validateCategory(UpdateFaqQuestionInput $value): void
    {
        $previous = $this->requestStack->getCurrentRequest()?->attributes->get('previous_data');
        if ($value->category === null || !($previous instanceof AdminFaqQuestion)) {
            return;
        }
        if ($value->category->faq?->id !== $previous->faq?->id) {
            $this->addViolation(t('faq.error.category_other_faq', domain: 'faq'), 'category');
        }
    }
}
