<?php
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
use phpDocumentor\Reflection\Types\Void_;

class FaqPopulate
{
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
        if(!isset($this->populate[self::KEY_FAQ_CATEORIES]))
        {
            return;
        }

        $this->faq->getFaqCategories()->clear();
        foreach ($this->populate[self::KEY_FAQ_CATEORIES] as $dataFaqCategorie) {
            $faqCategory = new FaqCategory();
            $faqCategory->setFaq($this->faq);
            $this->mergeData($faqCategory, $dataFaqCategorie,
                ['id', 'faq', self::KEY_FAQ_CATEGORY_TRANSLATION, self::KEY_FAQ_QUESTION]);

            foreach ($dataFaqCategorie[self::KEY_FAQ_CATEGORY_TRANSLATION] as $dataFaqCatTranslation) {
                $faqCategoryTranslation = new FaqCategoryTranslation();
                $faqCategoryTranslation->setFaqCategory($faqCategory);
                $this->mergeData($faqCategoryTranslation, $dataFaqCatTranslation, ['id', 'faqCategory']);
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
    private function populateFAQQuestion(FaqCategory $faqCategory, array $dataFaqCategory) : FaqCategory
    {
        foreach ($dataFaqCategory[self::KEY_FAQ_QUESTION] as $dataFaqQuestion)
        {
            $faqQuestion = new FaqQuestion();
            $faqQuestion->setFaqCategory($faqCategory);
            $this->mergeData($faqQuestion, $dataFaqQuestion,
                ['id', 'faqCategory', self::KEY_FAQ_QUESTION_TRANSLATION]);

            foreach ($dataFaqQuestion[self::KEY_FAQ_QUESTION_TRANSLATION] as $dataFaqQuestionTranslation)
            {
                $faqQuestionTranslation = new FaqQuestionTranslation();
                $faqQuestionTranslation->setFaqQuestion($faqQuestion);
                $this->mergeData($faqQuestionTranslation, $dataFaqQuestionTranslation, ['id', 'FaqQuestion']);
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
                        $faqTranslation = $this->mergeData($faqTranslation, $dataTranslation,
                            ['id', 'faq', 'locale']);
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

    /**
     * Merge des données de $populate dans $object sans prendre en compte $exclude
     * @param mixed $object
     * @param array $populate
     * @param array $exclude
     * @return mixed
     */
    private function mergeData(mixed $object, array $populate, array $exclude = []): mixed
    {
        foreach ($populate as $key => $value) {
            if (in_array($key, $exclude)) {
                continue;
            }
            $func = 'set' . ucfirst($key);
            $object->$func($value);
        }
        return $object;
    }
}
