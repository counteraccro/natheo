<?php
/**
 * Permet de créer une FAQ ainsi que l'ensemble de ses données associées
 * @author Gourdon Aymeric
 * @version 1.2
 */
namespace App\Utils\Content\Faq;

use App\Entity\Admin\Content\Faq\Faq;
use App\Entity\Admin\Content\Faq\FaqCategory;
use App\Entity\Admin\Content\Faq\FaqCategoryTranslation;
use App\Entity\Admin\Content\Faq\FaqQuestion;
use App\Entity\Admin\Content\Faq\FaqQuestionTranslation;
use App\Entity\Admin\Content\Faq\FaqStatistique;
use App\Entity\Admin\Content\Faq\FaqTranslation;

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

    public function __construct(array $locales)
    {
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
        $this->faq->addFaqCategory($this->createFaqCategory($this->faq));
        $this->createFaqStatistique(FaqStatistiqueKey::KEY_STAT_NB_CATEGORIES, '1');
        $this->createFaqStatistique(FaqStatistiqueKey::KEY_STAT_NB_QUESTIONS, '1');
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
     * @return FaqCategory
     */
    public function createFaqCategory(Faq $faq): FaqCategory
    {
        $faqCategory = new FaqCategory();
        $faqCategory->setFaq($faq);
        $faqCategory->setDisabled(false);
        $faqCategory->setRenderOrder(1);
        $faqCategory = $this->createFaqCategoryTranslation($faqCategory);
        return $this->createFaqQuestion($faqCategory);
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
            $faqCategoryTranslation->setTitle($locale . '-Nouvelle Catégorie');
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
    private function createFaqQuestion(FaqCategory $faqCategory): FaqCategory
    {
        $faqQuestion = new FaqQuestion();
        $faqQuestion->setRenderOrder(1);
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
            $faqQuestionTranslation->setTitle($locale . '-Titre nouvelle question');
            $faqQuestionTranslation->setAnswer($locale . '-Contenu nouvelle question');
            $faqQuestionTranslation->setFaqQuestion($faqQuestion);
            $faqQuestion->addFaqQuestionTranslation($faqQuestionTranslation);
        }
        return $faqQuestion;
    }

    /**
     * Création d'une FAQStatistiques
     * @param string $key
     * @param string $value
     * @return void
     */
    private function createFaqStatistique(string $key, string $value):void
    {
        $faqStatistique = new FaqStatistique();
        $faqStatistique->setFaq($this->faq);
        $faqStatistique->setKey($key);
        $faqStatistique->setValue($value);

        $this->faq->addFaqStatistique($faqStatistique);
    }
}
