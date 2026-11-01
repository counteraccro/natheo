<?php

declare(strict_types=1);
/**
 * Permet de merger les données venant d'un tableau à un objet faq
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Utils\Content\Faq;

use App\Entity\Admin\Content\Faq\Faq;
use App\Entity\Admin\Content\Faq\FaqCategory;
use App\Entity\Admin\Content\Faq\FaqCategoryTranslation;
use App\Entity\Admin\Content\Faq\FaqQuestion;
use App\Entity\Admin\Content\Faq\FaqQuestionTranslation;
use App\Entity\Trait\PopulateTrait;
use phpDocumentor\Reflection\Types\Void_;

class FaqPopulate
{
    use PopulateTrait;

    /**
     * Champs modifiables de FaqCategory
     * @var string[]
     */
    private const array FIELDS_FAQ_CATEGORY = ['disabled', 'renderOrder'];

    /**
     * Champs modifiables de FaqCategoryTranslation
     * @var string[]
     */
    private const array FIELDS_FAQ_CATEGORY_TRANSLATION = ['locale', 'title'];

    /**
     * Champs modifiables de FaqQuestion
     * @var string[]
     */
    private const array FIELDS_FAQ_QUESTION = ['disabled', 'renderOrder'];

    /**
     * Champs modifiables de FaqQuestionTranslation
     * @var string[]
     */
    private const array FIELDS_FAQ_QUESTION_TRANSLATION = ['locale', 'title', 'answer'];

    /**
     * Champs modifiables de FaqTranslation
     * @var string[]
     */
    private const array FIELDS_FAQ_TRANSLATION = ['title'];

    /**
     * Clé pour faqTranslations
     * @var string
     */
    private const KEY_FAQ_TRANSLATIONS = 'faqTranslations';

    /**
     * Clé pour faqCategories
     * @var string
     */
    private const KEY_FAQ_CATEORIES = 'faqCategories';

    /**
     * Clé pour faqCategoryTranslations
     * @var string
     */
    private const KEY_FAQ_CATEGORY_TRANSLATION = 'faqCategoryTranslations';

    /**
     * Clé pour faqQuestions
     * @var string
     */
    private const KEY_FAQ_QUESTION = 'faqQuestions';

    /**
     * Clé pour faqQuestionTranslations
     * @var string
     */
    private const KEY_FAQ_QUESTION_TRANSLATION = 'faqQuestionTranslations';

    /**
     * @var Faq
     */
    private Faq $faq;

    /**
     * @var array
     */
    private array $populate;

    /**
     * Constructeur
     * @param Faq $faq
     * @param array $populate
     */
    public function __construct(Faq $faq, array $populate)
    {
        $this->populate = $populate;
        $this->faq = $faq;
    }

    /**
     * Merge les données de $populate dans $faq
     * @return $this
     */
    public function populate(): static
    {
        $this->faq->setDisabled($this->populate['disabled']);
        $this->populateFAQTranslation();
        $this->populateFAQCategory();

        return $this;
    }

    /**
     * Merge les données de $populate dans FAQCategory
     * @return void
     */
    private function populateFAQCategory(): void
    {
        if (!isset($this->populate[self::KEY_FAQ_CATEORIES])) {
            return;
        }

        $this->faq->getFaqCategories()->clear();
        foreach ($this->populate[self::KEY_FAQ_CATEORIES] as $dataFaqCategorie) {
            $faqCategory = new FaqCategory();
            $faqCategory->setFaq($this->faq);
            $this->mergeData($faqCategory, $dataFaqCategorie, self::FIELDS_FAQ_CATEGORY);

            foreach ($dataFaqCategorie[self::KEY_FAQ_CATEGORY_TRANSLATION] as $dataFaqCatTranslation) {
                $faqCategoryTranslation = new FaqCategoryTranslation();
                $faqCategoryTranslation->setFaqCategory($faqCategory);
                $this->mergeData($faqCategoryTranslation, $dataFaqCatTranslation, self::FIELDS_FAQ_CATEGORY_TRANSLATION);
                $faqCategory->addFaqCategoryTranslation($faqCategoryTranslation);
            }

            $faqCategory = $this->populateFAQQuestion($faqCategory, $dataFaqCategorie);
            $this->faq->addFaqCategory($faqCategory);
        }
    }

    /**
     * Merge les données de $populate dans FAQQuestion
     * @param FaqCategory $faqCategory
     * @param array $dataFaqCategory
     * @return FaqCategory
     */
    private function populateFAQQuestion(FaqCategory $faqCategory, array $dataFaqCategory): FaqCategory
    {
        foreach ($dataFaqCategory[self::KEY_FAQ_QUESTION] as $dataFaqQuestion) {
            $faqQuestion = new FaqQuestion();
            $faqQuestion->setFaqCategory($faqCategory);
            $this->mergeData($faqQuestion, $dataFaqQuestion, self::FIELDS_FAQ_QUESTION);

            foreach ($dataFaqQuestion[self::KEY_FAQ_QUESTION_TRANSLATION] as $dataFaqQuestionTranslation) {
                $faqQuestionTranslation = new FaqQuestionTranslation();
                $faqQuestionTranslation->setFaqQuestion($faqQuestion);
                $this->mergeData($faqQuestionTranslation, $dataFaqQuestionTranslation, self::FIELDS_FAQ_QUESTION_TRANSLATION);
                $faqQuestion->addFaqQuestionTranslation($faqQuestionTranslation);
            }
            $faqCategory->addFaqQuestion($faqQuestion);
        }
        return $faqCategory;
    }

    /**
     * Merge les données de $populate dans $faqTranslation
     * @return void
     */
    private function populateFAQTranslation(): void
    {
        if (isset($this->populate[self::KEY_FAQ_TRANSLATIONS])) {
            foreach ($this->faq->getFaqTranslations() as &$faqTranslation) {
                foreach ($this->populate[self::KEY_FAQ_TRANSLATIONS] as $dataTranslation) {
                    if ($faqTranslation->getLocale() === $dataTranslation['locale']) {
                        $faqTranslation = $this->mergeData($faqTranslation, $dataTranslation, self::FIELDS_FAQ_TRANSLATION);
                    }
                }
            }
        }
    }

    /**
     * Retourne une FAQ
     * @return Faq
     */
    public function getFaq(): Faq
    {
        return $this->faq;
    }

}
