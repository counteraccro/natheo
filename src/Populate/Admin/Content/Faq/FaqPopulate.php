<?php

declare(strict_types=1);
/**
 * Permet de merger les données venant d'un tableau à un objet faq
 * Les catégories et questions existantes sont mises à jour par id (leurs ids sont conservés),
 * les nouvelles sont créées et seules celles absentes des données sont supprimées
 * @author Gourdon Aymeric
 * @version 2.0
 */

namespace App\Populate\Admin\Content\Faq;

use App\Entity\Admin\Content\Faq\Faq;
use App\Entity\Admin\Content\Faq\FaqCategory;
use App\Entity\Admin\Content\Faq\FaqCategoryTranslation;
use App\Entity\Admin\Content\Faq\FaqQuestion;
use App\Entity\Admin\Content\Faq\FaqQuestionTranslation;
use App\Entity\Trait\PopulateTrait;

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
    private const array FIELDS_FAQ_CATEGORY_TRANSLATION = ['title'];

    /**
     * Champs modifiables de FaqQuestion
     * @var string[]
     */
    private const array FIELDS_FAQ_QUESTION = ['disabled', 'renderOrder'];

    /**
     * Champs modifiables de FaqQuestionTranslation
     * @var string[]
     */
    private const array FIELDS_FAQ_QUESTION_TRANSLATION = ['title', 'answer'];

    /**
     * Champs modifiables de FaqTranslation
     * @var string[]
     */
    private const array FIELDS_FAQ_TRANSLATION = ['title'];

    /**
     * Clé pour faqTranslations
     * @var string
     */
    private const string KEY_FAQ_TRANSLATIONS = 'faqTranslations';

    /**
     * Clé pour faqCategories
     * @var string
     */
    private const string KEY_FAQ_CATEGORIES = 'faqCategories';

    /**
     * Clé pour faqCategoryTranslations
     * @var string
     */
    private const string KEY_FAQ_CATEGORY_TRANSLATION = 'faqCategoryTranslations';

    /**
     * Clé pour faqQuestions
     * @var string
     */
    private const string KEY_FAQ_QUESTION = 'faqQuestions';

    /**
     * Clé pour faqQuestionTranslations
     * @var string
     */
    private const string KEY_FAQ_QUESTION_TRANSLATION = 'faqQuestionTranslations';

    /**
     * @var Faq
     */
    private Faq $faq;

    /**
     * @var array
     */
    private array $populate;

    /**
     * Questions existantes de la FAQ, indexées par id (une question peut changer de catégorie)
     * @var array<int, FaqQuestion>
     */
    private array $existingQuestions = [];

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
        if (isset($this->populate['disabled'])) {
            $this->faq->setDisabled((bool) $this->populate['disabled']);
        }
        $this->populateFAQTranslation();
        $this->populateFAQCategory();

        return $this;
    }

    /**
     * Merge les données de $populate dans les catégories de la FAQ
     * @return void
     */
    private function populateFAQCategory(): void
    {
        if (!isset($this->populate[self::KEY_FAQ_CATEGORIES]) || !is_array($this->populate[self::KEY_FAQ_CATEGORIES])) {
            return;
        }

        $existingCategories = [];
        foreach ($this->faq->getFaqCategories() as $faqCategory) {
            $existingCategories[$faqCategory->getId()] = $faqCategory;
            foreach ($faqCategory->getFaqQuestions() as $faqQuestion) {
                $this->existingQuestions[$faqQuestion->getId()] = $faqQuestion;
            }
        }

        // Associe chaque catégorie des données à une catégorie existante (même id) ou nouvelle
        $categories = [];
        foreach ($this->populate[self::KEY_FAQ_CATEGORIES] as $dataFaqCategory) {
            $faqCategory = $existingCategories[$this->getId($dataFaqCategory)] ?? null;
            if ($faqCategory === null) {
                $faqCategory = new FaqCategory();
                $this->faq->addFaqCategory($faqCategory);
            }
            $this->mergeData($faqCategory, $dataFaqCategory, self::FIELDS_FAQ_CATEGORY);
            $this->populateFAQCategoryTranslation($faqCategory, $dataFaqCategory);
            $categories[] = [$faqCategory, $dataFaqCategory];
        }

        // Questions traitées une fois toutes les catégories connues : une question peut changer de catégorie
        $keptQuestionIds = [];
        foreach ($categories as [$faqCategory, $dataFaqCategory]) {
            $keptQuestionIds = array_merge(
                $keptQuestionIds,
                $this->populateFAQQuestion($faqCategory, $dataFaqCategory),
            );
        }

        foreach ($this->existingQuestions as $id => $faqQuestion) {
            if (!in_array($id, $keptQuestionIds, true)) {
                $faqQuestion->getFaqCategory()?->removeFaqQuestion($faqQuestion);
            }
        }

        $keptCategories = array_column($categories, 0);
        foreach ($existingCategories as $faqCategory) {
            if (!in_array($faqCategory, $keptCategories, true)) {
                $this->faq->removeFaqCategory($faqCategory);
            }
        }
    }

    /**
     * Merge les traductions d'une catégorie, retrouvées par locale
     * @param FaqCategory $faqCategory
     * @param array $dataFaqCategory
     * @return void
     */
    private function populateFAQCategoryTranslation(FaqCategory $faqCategory, array $dataFaqCategory): void
    {
        foreach ($dataFaqCategory[self::KEY_FAQ_CATEGORY_TRANSLATION] ?? [] as $dataTranslation) {
            $locale = (string) ($dataTranslation['locale'] ?? '');
            if ($locale === '') {
                continue;
            }
            $translation = $faqCategory
                ->getFaqCategoryTranslations()
                ->findFirst(fn(int $key, FaqCategoryTranslation $t) => $t->getLocale() === $locale);
            if ($translation === null) {
                $translation = new FaqCategoryTranslation();
                $translation->setLocale($locale);
                $faqCategory->addFaqCategoryTranslation($translation);
            }
            $this->mergeData($translation, $dataTranslation, self::FIELDS_FAQ_CATEGORY_TRANSLATION);
        }
    }

    /**
     * Merge les questions d'une catégorie ; une question existante d'une autre catégorie y est déplacée
     * @param FaqCategory $faqCategory
     * @param array $dataFaqCategory
     * @return int[] ids des questions existantes conservées
     */
    private function populateFAQQuestion(FaqCategory $faqCategory, array $dataFaqCategory): array
    {
        $keptIds = [];
        foreach ($dataFaqCategory[self::KEY_FAQ_QUESTION] ?? [] as $dataFaqQuestion) {
            $faqQuestion = $this->existingQuestions[$this->getId($dataFaqQuestion)] ?? null;
            if ($faqQuestion === null) {
                $faqQuestion = new FaqQuestion();
                $faqCategory->addFaqQuestion($faqQuestion);
            } else {
                $keptIds[] = $faqQuestion->getId();
                $currentCategory = $faqQuestion->getFaqCategory();
                if ($currentCategory !== $faqCategory) {
                    // Retrait avant l'ajout : l'ajout annule la suppression orpheline (orphanRemoval)
                    $currentCategory?->getFaqQuestions()->removeElement($faqQuestion);
                    $faqCategory->addFaqQuestion($faqQuestion);
                }
            }
            $this->mergeData($faqQuestion, $dataFaqQuestion, self::FIELDS_FAQ_QUESTION);
            $this->populateFAQQuestionTranslation($faqQuestion, $dataFaqQuestion);
        }
        return $keptIds;
    }

    /**
     * Merge les traductions d'une question, retrouvées par locale
     * @param FaqQuestion $faqQuestion
     * @param array $dataFaqQuestion
     * @return void
     */
    private function populateFAQQuestionTranslation(FaqQuestion $faqQuestion, array $dataFaqQuestion): void
    {
        foreach ($dataFaqQuestion[self::KEY_FAQ_QUESTION_TRANSLATION] ?? [] as $dataTranslation) {
            $locale = (string) ($dataTranslation['locale'] ?? '');
            if ($locale === '') {
                continue;
            }
            $translation = $faqQuestion
                ->getFaqQuestionTranslations()
                ->findFirst(fn(int $key, FaqQuestionTranslation $t) => $t->getLocale() === $locale);
            if ($translation === null) {
                $translation = new FaqQuestionTranslation();
                $translation->setLocale($locale);
                $faqQuestion->addFaqQuestionTranslation($translation);
            }
            $this->mergeData($translation, $dataTranslation, self::FIELDS_FAQ_QUESTION_TRANSLATION);
        }
    }

    /**
     * Merge les données de $populate dans les traductions de la FAQ
     * @return void
     */
    private function populateFAQTranslation(): void
    {
        foreach ($this->populate[self::KEY_FAQ_TRANSLATIONS] ?? [] as $dataTranslation) {
            foreach ($this->faq->getFaqTranslations() as $faqTranslation) {
                if ($faqTranslation->getLocale() === ($dataTranslation['locale'] ?? null)) {
                    $this->mergeData($faqTranslation, $dataTranslation, self::FIELDS_FAQ_TRANSLATION);
                }
            }
        }
    }

    /**
     * Retourne l'id d'un élément des données, 0 pour un élément nouveau (id temporaire négatif côté Vue)
     * @param array $data
     * @return int
     */
    private function getId(array $data): int
    {
        $id = $data['id'] ?? 0;
        return is_numeric($id) ? max(0, (int) $id) : 0;
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
