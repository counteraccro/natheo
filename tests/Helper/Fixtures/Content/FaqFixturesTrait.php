<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Jeu de données FAQ
 */

namespace App\Tests\Helper\Fixtures\Content;

use App\Entity\Admin\Content\Faq\Faq;
use App\Entity\Admin\Content\Faq\FaqCategory;
use App\Entity\Admin\Content\Faq\FaqCategoryTranslation;
use App\Entity\Admin\Content\Faq\FaqQuestion;
use App\Entity\Admin\Content\Faq\FaqQuestionTranslation;
use App\Entity\Admin\Content\Faq\FaqStatistique;
use App\Entity\Admin\Content\Faq\FaqTranslation;
use App\Entity\Admin\System\User;
use App\Tests\Helper\FakerTrait;
use App\Utils\Content\Faq\FaqStatistiqueKey;
use App\Utils\Translate\Content\FaqTranslate;

trait FaqFixturesTrait
{
    use FakerTrait;

    /**
     * Création d'une FAQ
     * @param User|null $user
     * @param array $customData
     * @param bool $persist
     * @return Faq
     */
    public function createFaq(User $user = null, array $customData = [], bool $persist = true): Faq
    {
        if ($user === null) {
            $user = $this->createUserContributeur();
        }

        $data = [
            'disabled' => self::getFaker()->boolean(),
            'user' => $user,
        ];

        $faq = $this->initEntity(Faq::class, array_merge($data, $customData));

        if ($persist) {
            $this->persistAndFlush($faq);
        }
        return $faq;
    }

    /**
     * Création d'une FaqTranslate
     * @param Faq|null $faq
     * @param array $customData
     * @param bool $persist
     * @return FaqTranslation
     */
    public function createFaqTranslation(Faq $faq = null, array $customData = [], bool $persist = true): FaqTranslation
    {
        if ($faq === null) {
            $faq = $this->createFaq();
        }

        $data = [
            'locale' => self::getFaker()->languageCode(),
            'title' => self::getFaker()->text(),
            'faq' => $faq,
        ];

        $faqTranslate = $this->initEntity(FaqTranslation::class, array_merge($data, $customData));
        $faq->addFaqTranslation($faqTranslate);

        if ($persist) {
            $this->persistAndFlush($faqTranslate);
        }
        return $faqTranslate;
    }

    /**
     * Création d'une FaqCategory
     * @param Faq|null $faq
     * @param array $customData
     * @param bool $persist
     * @return FaqCategory
     */
    public function createFaqCategory(Faq $faq = null, array $customData = [], bool $persist = true): FaqCategory
    {
        $renderOrder = 1;
        if ($faq === null) {
            $faq = $this->createFaq();

        } else {
            if ($faq->getFaqCategories()->count() > 0) {
                $renderOrder = $faq->getFaqCategories()->last()->getRenderOrder() + 1;
            }
        }

        $data = [
            'disabled' => self::getFaker()->boolean(),
            'renderOrder' => $renderOrder,
            'faq' => $faq,
        ];

        $faqCategory = $this->initEntity(FaqCategory::class, array_merge($data, $customData));
        $faq->addFaqCategory($faqCategory);
        if ($persist) {
            $this->persistAndFlush($faqCategory);
        }
        return $faqCategory;
    }

    /**
     * Création d'une FaqCategoryTranslation
     * @param FaqCategory|null $faqCategory
     * @param array $customData
     * @param bool $persist
     * @return FaqCategoryTranslation
     */
    public function createFaqCategoryTranslation(FaqCategory $faqCategory = null, array $customData = [], bool $persist = true): FaqCategoryTranslation
    {
        if ($faqCategory === null) {
            $faqCategory = $this->createFaqCategory();
        }

        $data = [
            'locale' => self::getFaker()->languageCode(),
            'title' => self::getFaker()->languageCode(),
            'faqCategory' => $faqCategory,
        ];

        $faqCategoryTranslation = $this->initEntity(FaqCategoryTranslation::class, array_merge($data, $customData));
        $faqCategory->addFaqCategoryTranslation($faqCategoryTranslation);
        if ($persist) {
            $this->persistAndFlush($faqCategoryTranslation);
        }
        return $faqCategoryTranslation;
    }

    /**
     * Création d'une FaqQuestion
     * @param FaqCategory|null $faqCategory
     * @param array $customData
     * @param bool $persist
     * @return FaqQuestion
     */
    public function createFaqQuestion(FaqCategory $faqCategory = null, array $customData = [], bool $persist = true): FaqQuestion
    {
        $renderOrder = 1;
        if ($faqCategory === null) {
            $faqCategory = $this->createFaqCategory();
        } else {
            if ($faqCategory->getFaqQuestions()->count() > 0) {
                $renderOrder = $faqCategory->getFaqQuestions()->last()->getRenderOrder() + 1;
            }
        }

        $data = [
            'disabled' => self::getFaker()->boolean(),
            'renderOrder' => $renderOrder,
            'faqCategory' => $faqCategory,
        ];

        $faqQuestion = $this->initEntity(FaqQuestion::class, array_merge($data, $customData));
        $faqCategory->addFaqQuestion($faqQuestion);
        if ($persist) {
            $this->persistAndFlush($faqQuestion);
        }
        return $faqQuestion;
    }

    /**
     * Création d'une FAQQuestionTranslation
     * @param FaqQuestion|null $faqQuestion
     * @param array $customData
     * @param bool $persist
     * @return FaqQuestionTranslation
     */
    public function createFaqQuestionTranslation(FaqQuestion $faqQuestion = null, array $customData = [], bool $persist = true): FaqQuestionTranslation
    {
        if ($faqQuestion === null) {
            $faqQuestion = $this->createFaqQuestion();
        }

        $data = [
            'locale' => self::getFaker()->languageCode(),
            'title' => self::getFaker()->text(),
            'answer' => self::getFaker()->text(),
            'FaqQuestion' => $faqQuestion,
        ];

        $faqQuestionTranslation = $this->initEntity(FaqQuestionTranslation::class, array_merge($data, $customData));
        $faqQuestion->addFaqQuestionTranslation($faqQuestionTranslation);
        if ($persist) {
            $this->persistAndFlush($faqQuestionTranslation);
        }
        return $faqQuestionTranslation;
    }

    /**
     * Création FaqStatistique
     * @param Faq|null $faq
     * @param array $customData
     * @param bool $persist
     * @return FaqStatistique
     */
    public function createFaqStatistique(Faq $faq = null, array $customData = [], bool $persist = true): FaqStatistique
    {
        if ($faq === null) {
            $faq = $this->createFaq();
        }

        $data = [
            'key' => self::getFaker()->text(),
            'value' => self::getFaker()->text(),
            'faq' => $faq,
        ];

        $faqStatistique = $this->initEntity(FaqStatistique::class, array_merge($data, $customData));
        $faq->addFaqStatistique($faqStatistique);
        if ($persist) {
            $this->persistAndFlush($faqStatistique);
        }
        return $faqStatistique;
    }

    /**
     * Créer une FAQ et toutes les données associées par défaut
     * @return Faq
     */
    public function createFaqAllDataDefault(): Faq
    {
        $faq = $this->createFaq();

        $this->createFaqStatistique($faq, ['key' => FaqStatistiqueKey::KEY_STAT_NB_CATEGORIES, 'value' => self::getFaker()->numberBetween(1, 1000)]);
        $this->createFaqStatistique($faq, ['key' => FaqStatistiqueKey::KEY_STAT_NB_QUESTIONS, 'value' => self::getFaker()->numberBetween(1, 5)]);

        foreach ($this->locales as $locale) {
            $data = ['locale' => $locale];
          $this->createFaqTranslation($faq, $data);
        }

        for ($i = 0; $i < 2; $i++) {
            $faqCategory = $this->createFaqCategory($faq);

            foreach ($this->locales as $locale) {
                $data = ['locale' => $locale];
               $this->createFaqCategoryTranslation($faqCategory, $data);
            }

            for ($j = 0; $j < 3; $j++) {
                $faqQuestion = $this->createFaqQuestion($faqCategory);
                foreach ($this->locales as $locale) {
                    $data = ['locale' => $locale];
                    $this->createFaqQuestionTranslation($faqQuestion, $data);
                }
                $faqCategory->addFaqQuestion($faqQuestion);
            }

            $faq->addFaqCategory($faqCategory);
        }

        $this->persistAndFlush($faq);

        return $faq;
    }
}