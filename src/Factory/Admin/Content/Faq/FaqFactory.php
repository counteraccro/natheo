<?php

declare(strict_types=1);
/**
 * Permet de créer une FAQ ainsi que l'ensemble de ses données associées
 * @author Gourdon Aymeric
 * @version 1.4
 */
namespace App\Factory\Admin\Content\Faq;

use App\Entity\Admin\Content\Faq\Faq;
use App\Entity\Admin\Content\Faq\FaqCategory;
use App\Entity\Admin\Content\Faq\FaqCategoryTranslation;
use App\Entity\Admin\Content\Faq\FaqQuestion;
use App\Entity\Admin\Content\Faq\FaqQuestionTranslation;
use App\Entity\Admin\Content\Faq\FaqTranslation;
use Symfony\Contracts\Translation\TranslatorInterface;

class FaqFactory
{
    /**
     * @var ?Faq
     */
    private ?Faq $faq = null;

    /**
     * @var array
     */
    private array $locales;

    /**
     * @param array $locales langues des traductions créées
     * @param TranslatorInterface $translator textes par défaut des catégories et questions
     */
    public function __construct(
        array $locales,
        private readonly TranslatorInterface $translator,
    ) {
        $this->locales = $locales;
    }

    /**
     * Créer un nouvel objet FAQ
     * @return FaqFactory
     */
    public function create(): FaqFactory
    {
        $this->faq = new Faq();
        $this->faq->setDisabled(false);
        $this->createFaqTranslation();
        $this->faq = $this->createFaqCategory($this->faq);
        return $this;
    }

    /**
     * Retourne la faq courante
     * @return Faq
     */
    public function getFaq(): Faq
    {
        return $this->faq;
    }

    /**
     * Créer les FaqTranslation en fonction des locales
     * @return void
     */
    private function createFaqTranslation(): void
    {
        foreach ($this->locales as $locale) {
            $faqTranslation = new FaqTranslation();
            $faqTranslation->setLocale($locale);
            $faqTranslation->setFaq($this->faq);
            $faqTranslation->setTitle('');
            $this->faq->addFaqTranslation($faqTranslation);
        }
    }

    /**
     * Créer une FAQ catégorie
     * @param Faq $faq
     * @return Faq
     */
    public function createFaqCategory(Faq $faq): Faq
    {
        $faqCategory = new FaqCategory();
        $faqCategory->setFaq($faq);
        $faqCategory->setDisabled(false);
        $faqCategory->setRenderOrder($faq->getMaxRenderOrderCategory() + 1);
        $faqCategory = $this->createFaqCategoryTranslation($faqCategory);
        $faqCategory = $this->createFaqQuestion($faqCategory);
        $faq->addFaqCategory($faqCategory);
        return $faq;
    }

    /**
     * Création des FaqCategoryTranslation en fonctions des locales
     * @param FaqCategory $faqCategory
     * @return FaqCategory
     */
    private function createFaqCategoryTranslation(FaqCategory $faqCategory): FaqCategory
    {
        foreach ($this->locales as $locale) {
            $faqCategoryTranslation = new FaqCategoryTranslation();
            $faqCategoryTranslation->setFaqCategory($faqCategory);
            $faqCategoryTranslation->setTitle($this->defaultText('faq.default.category_title', $locale));
            $faqCategoryTranslation->setLocale($locale);
            $faqCategory->addFaqCategoryTranslation($faqCategoryTranslation);
        }
        return $faqCategory;
    }

    /**
     * Création des FAQ questions
     * @param FaqCategory $faqCategory
     * @return FaqCategory
     */
    public function createFaqQuestion(FaqCategory $faqCategory): FaqCategory
    {
        $faqQuestion = new FaqQuestion();
        $faqQuestion->setRenderOrder($faqCategory->getMaxRenderOrderQuestion() + 1);
        $faqQuestion->setDisabled(false);
        $faqQuestion->setFaqCategory($faqCategory);
        $faqQuestion = $this->createFaqQuestionTranslation($faqQuestion);
        $faqCategory->addFaqQuestion($faqQuestion);
        return $faqCategory;
    }

    /**
     * Création d'une FaqQuestionTranslation en fonction des locales
     * @param FaqQuestion $faqQuestion
     * @return FaqQuestion
     */
    private function createFaqQuestionTranslation(FaqQuestion $faqQuestion): FaqQuestion
    {
        foreach ($this->locales as $locale) {
            $faqQuestionTranslation = new FaqQuestionTranslation();
            $faqQuestionTranslation->setLocale($locale);
            $faqQuestionTranslation->setTitle($this->defaultText('faq.default.question_title', $locale));
            $faqQuestionTranslation->setAnswer($this->defaultText('faq.default.question_answer', $locale));
            $faqQuestionTranslation->setFaqQuestion($faqQuestion);
            $faqQuestion->addFaqQuestionTranslation($faqQuestionTranslation);
        }
        return $faqQuestion;
    }

    /**
     * Texte par défaut traduit dans $locale
     * @param string $key
     * @param string $locale
     * @return string
     */
    private function defaultText(string $key, string $locale): string
    {
        return $this->translator->trans($key, domain: 'faq', locale: $locale);
    }
}
