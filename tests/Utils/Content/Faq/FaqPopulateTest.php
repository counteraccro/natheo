<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test de FaqPopulate
 */

namespace App\Tests\Utils\Content\Faq;

use App\Entity\Admin\Content\Faq\Faq;
use App\Tests\AppWebTestCase;
use App\Utils\Content\Faq\FaqPopulate;

class FaqPopulateTest extends AppWebTestCase
{
    /**
     * Test méthode populate()
     * @return void
     */
    public function testPopulate(): void
    {
        $faq = $this->createFaqAllDataDefault();

        $dataPopulate = [
            'id' => $faq->getId(),
            'disabled' => false,
            'faqTranslations' => [
                0 => [
                    'id' => 4,
                    'faq' => 2,
                    'locale' => 'fr',
                    'title' => self::getFaker()->text('20'),
                ],
                1 => [
                    'id' => 5,
                    'faq' => 2,
                    'locale' => 'en',
                    'title' => self::getFaker()->text('20'),
                ],
                2 => [
                    'id' => 6,
                    'faq' => 2,
                    'locale' => 'es',
                    'title' => self::getFaker()->text('20'),
                ],
            ],
            'faqCategories' => [
                0 => [
                    'id' => 5,
                    'faq' => 2,
                    'disabled' => false,
                    'renderOrder' => 1,
                    'faqCategoryTranslations' => [
                        0 => [
                            'id' => 13,
                            'faqCategory' => 5,
                            'locale' => 'fr',
                            'title' => self::getFaker()->text('20'),
                        ],
                        1 => [
                            'id' => 14,
                            'faqCategory' => 5,
                            'locale' => 'en',
                            'title' => self::getFaker()->text('20'),
                        ],
                        2 => [
                            'id' => 15,
                            'faqCategory' => 5,
                            'locale' => 'es',
                            'title' => self::getFaker()->text('20'),
                        ],
                    ],
                    'faqQuestions' => [
                        0 => [
                            'id' => 7,
                            'faqCategory' => 5,
                            'disabled' => false,
                            'renderOrder' => 1,
                            'faqQuestionTranslations' => [
                                0 => [
                                    'id' => 19,
                                    'FaqQuestion' => 7,
                                    'locale' => 'fr',
                                    'title' => self::getFaker()->text('20'),
                                    'answer' => self::getFaker()->text('100'),
                                ],
                                1 => [
                                    'id' => 20,
                                    'FaqQuestion' => 7,
                                    'locale' => 'en',
                                    'title' => self::getFaker()->text('20'),
                                    'answer' => self::getFaker()->text('100'),
                                ],
                                2 => [
                                    'id' => 21,
                                    'FaqQuestion' => 7,
                                    'locale' => 'es',
                                    'title' => self::getFaker()->text('20'),
                                    'answer' => self::getFaker()->text('100'),
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'faqStatistiques' => [
                0 => [
                    'id' => 3,
                    'faq' => 2,
                    'key' => 'KEY_STAT_NB_CATEGORIES',
                    'value' => '1',
                ],
                1 => [
                    'id' => 4,
                    'faq' => 2,
                    'key' => 'KEY_STAT_NB_QUESTIONS',
                    'value' => '1',
                ],
            ],
        ];

        $faqPopulate = new FaqPopulate($faq, $dataPopulate);
        $faq = $faqPopulate->populate()->getFaq();

        $this->assertInstanceOf(Faq::class, $faq);
        $this->assertCount(3, $faq->getFaqTranslations());

        foreach ($faq->getFaqTranslations() as $faqTranslation) {
            foreach ($dataPopulate['faqTranslations'] as $faqTranslationPopulate) {
                if ($faqTranslation->getLocale() == $faqTranslationPopulate['locale']) {
                    $this->assertEquals($faqTranslation->getTitle(), $faqTranslationPopulate['title']);
                }
            }
        }

        $this->assertCount(1, $faq->getFaqCategories());
        foreach ($faq->getFaqCategories() as $faqCategory) {
            foreach ($faqCategory->getFaqCategoryTranslations() as $faqCategoryTranslation) {
                foreach (
                    $dataPopulate['faqCategories'][0]['faqCategoryTranslations']
                    as $faqCategoryTranslationsPopulate
                ) {
                    if ($faqCategoryTranslation->getLocale() == $faqCategoryTranslationsPopulate['locale']) {
                        $this->assertEquals(
                            $faqCategoryTranslation->getTitle(),
                            $faqCategoryTranslationsPopulate['title'],
                        );
                    }
                }
            }

            foreach ($faqCategory->getFaqQuestions() as $faqQuestion) {
                foreach ($faqQuestion->getFaqQuestionTranslations() as $faqQuestionTranslation) {
                    foreach (
                        $dataPopulate['faqCategories'][0]['faqQuestions'][0]['faqQuestionTranslations']
                        as $faqQuestionsPopulate
                    ) {
                        if ($faqQuestionTranslation->getLocale() == $faqQuestionsPopulate['locale']) {
                            $this->assertEquals($faqQuestionTranslation->getTitle(), $faqQuestionsPopulate['title']);
                            $this->assertEquals($faqQuestionTranslation->getAnswer(), $faqQuestionsPopulate['answer']);
                        }
                    }
                }
            }
        }
    }
}
