<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test de la FaqFactory
 */

namespace App\Tests\Utils\Content\Faq;

use App\Entity\Admin\Content\Faq\Faq;
use App\Tests\AppWebTestCase;
use App\Utils\Content\Faq\FaqFactory;

class FaqFactoryTest extends AppWebTestCase
{
    /**
     * test méthode create()
     * @return void
     */
    public function testCreate(): void
    {
        $faqFactory = new FaqFactory($this->locales);
        $faq = $faqFactory->create()->getFaq();
        $this->assertInstanceOf(Faq::class, $faq);
        $this->assertCount(1, $faq->getFaqCategories());
        $this->assertCount(2, $faq->getFaqStatistiques());
        $faqCategory = $faq->getFaqCategories()->first();
        $this->assertCount(3, $faqCategory->getFaqCategoryTranslations());
        $this->assertEquals(1, $faqCategory->getRenderOrder());
        foreach ($faqCategory->getFaqCategoryTranslations() as $faqCategoryTranslation) {
            $this->assertTrue(in_array($faqCategoryTranslation->getLocale(), $this->locales));
        }
        $this->assertCount(1, $faqCategory->getFaqQuestions());

        $faqQuestion = $faqCategory->getFaqQuestions()->first();
        $this->assertCount(3, $faqQuestion->getFaqQuestionTranslations());
        foreach ($faqQuestion->getFaqQuestionTranslations() as $faqQuestionTranslation) {
            $this->assertTrue(in_array($faqQuestionTranslation->getLocale(), $this->locales));
        }
    }

    /**
     * Test méthode createFaqCategory()
     * @return void
     */
    public function testCreateFaqCategory(): void
    {
        $faq = $this->createFaq();
        $faqFactory = new FaqFactory($this->locales);
        $faq = $faqFactory->createFaqCategory($faq);
        $this->assertCount(1, $faq->getFaqCategories());
        $faqCategory = $faq->getFaqCategories()->first();
        $this->assertCount(3, $faqCategory->getFaqCategoryTranslations());
        $this->assertEquals(1, $faqCategory->getRenderOrder());
        foreach ($faqCategory->getFaqCategoryTranslations() as $faqCategoryTranslation) {
            $this->assertTrue(in_array($faqCategoryTranslation->getLocale(), $this->locales));
        }
        $this->assertCount(1, $faqCategory->getFaqQuestions());

        $faqQuestion = $faqCategory->getFaqQuestions()->first();
        $this->assertCount(3, $faqQuestion->getFaqQuestionTranslations());
        foreach ($faqQuestion->getFaqQuestionTranslations() as $faqQuestionTranslation) {
            $this->assertTrue(in_array($faqQuestionTranslation->getLocale(), $this->locales));
        }

        $faq = $faqFactory->createFaqCategory($faq);
        $this->assertCount(2, $faq->getFaqCategories());
        $i = 1;
        foreach ($faq->getFaqCategories() as $faqCategory) {
            $this->assertEquals($i, $faqCategory->getRenderOrder());
            $i++;
        }
    }

    /**
     * Test méthode createFaqQuestion()
     * @return void
     */
    public function testCreateFaqQuestion(): void
    {
        $faqCategory = $this->createFaqCategory();
        $faqFactory = new FaqFactory($this->locales);
        $faqCategory = $faqFactory->createFaqQuestion($faqCategory);
        $this->assertCount(1, $faqCategory->getFaqQuestions());

        $faqQuestion = $faqCategory->getFaqQuestions()->first();
        $this->assertCount(3, $faqQuestion->getFaqQuestionTranslations());
        foreach ($faqQuestion->getFaqQuestionTranslations() as $faqQuestionTranslation) {
            $this->assertTrue(in_array($faqQuestionTranslation->getLocale(), $this->locales));
        }

        $faqCategory = $faqFactory->createFaqQuestion($faqCategory);
        $this->assertCount(2, $faqCategory->getFaqQuestions());
        $i = 1;
        foreach ($faqCategory->getFaqQuestions() as $faqQuestion) {
            $this->assertEquals($i, $faqQuestion->getRenderOrder());
            $i++;
        }
    }
}
