<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test service Faq
 */

namespace App\Tests\Service\Admin\Content\Faq;

use App\Entity\Admin\Content\Faq\Faq;
use App\Entity\Admin\Content\Faq\FaqCategory;
use App\Entity\Admin\Content\Faq\FaqQuestion;
use App\Service\Admin\Content\Faq\FaqService;
use App\Tests\AppWebTestCase;
use App\Utils\Content\Faq\FaqStatistiqueKey;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class FaqServiceTest extends AppWebTestCase
{
    /**
     * @var FaqService
     */
    private FaqService $faqService;

    public function setUp(): void
    {
        parent::setUp();
        $this->faqService = $this->container->get(FaqService::class);
    }

    /**
     * Test méthode getAllFormatToGrid()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetAllFormatToGrid() :void
    {
        for($i = 0; $i < 5; $i++) {
            $faq = $this->createFaqAllDataDefault();
        }

        $result = $this->faqService->getAllFormatToGrid(1, 4);
        $this->assertArrayHasKey('nb', $result);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('column', $result);
        $this->assertArrayHasKey('sql', $result);
        $this->assertArrayHasKey('translate', $result);
        $this->assertEquals(5, $result['nb']);
        $this->assertCount(4, $result['data']);

        $search = $faq->getFaqTranslationByLocale('fr')->getTitle();
        $result = $this->faqService->getAllFormatToGrid(1, 5, $search);
        $this->assertEquals(1, $result['nb']);
        $this->assertCount(1, $result['data']);

        $userId = $faq->getUser()->getId();
        $result = $this->faqService->getAllFormatToGrid(1, 5, $search, $userId);
        $this->assertEquals(1, $result['nb']);
        $this->assertCount(1, $result['data']);

        $faq = $this->createFaqAllDataDefault();
        $userId = $faq->getUser()->getId();
        $result = $this->faqService->getAllFormatToGrid(1, 5, null, $userId);
        $this->assertEquals(1, $result['nb']);
        $this->assertCount(1, $result['data']);
    }

    /**
     * Test méthode getAllPaginate()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetAllPaginate() :void
    {
        for($i = 0; $i < 5; $i++) {
            $faq = $this->createFaqAllDataDefault();
        }
        $result = $this->faqService->getAllPaginate(1, 4);
        $this->assertEquals(4, $result->getIterator()->count());
        $this->assertEquals(5, $result->count());

        $search = $faq->getFaqTranslationByLocale('fr')->getTitle();
        $result = $this->faqService->getAllPaginate(1, 4, $search);
        $this->assertEquals(1, $result->getIterator()->count());
        $this->assertEquals(1, $result->count());

        $userId = $faq->getUser()->getId();
        $result = $this->faqService->getAllPaginate(1, 4, $search, $userId);
        $this->assertEquals(1, $result->getIterator()->count());
        $this->assertEquals(1, $result->count());

        $faq = $this->createFaqAllDataDefault();
        $userId = $faq->getUser()->getId();
        $result = $this->faqService->getAllPaginate(1, 4, null, $userId);
        $this->assertEquals(1, $result->getIterator()->count());
        $this->assertEquals(1, $result->count());
    }

    /**
     * Test méthode updateDisabledQuestion()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testUpdateDisabledQuestion() :void
    {
        $faq = $this->createFaqAllDataDefault();
        $faqCat = $faq->getFaqCategories()->first();
        $question = $faqCat->getFaqQuestions()->first();

        $title = $question->getFaqQuestionTranslationByLocale('fr')->getTitle();

        $result = $this->faqService->updateDisabledQuestion($question->getId(), true);
        $this->assertEquals($this->translator->trans('faq.question.disabled.ok', ['question' => $title], domain: 'faq'), $result);
        $verif = $this->faqService->findOneById(FaqQuestion::class, $question->getId());
        $this->assertTrue($verif->isDisabled());

        $result = $this->faqService->updateDisabledQuestion($question->getId(), false);
        $this->assertEquals($this->translator->trans('faq.question.enabled.ok', ['question' => $title], domain: 'faq'), $result);
        $verif = $this->faqService->findOneById(FaqQuestion::class, $question->getId());
        $this->assertFalse($verif->isDisabled());
    }

    /**
     * Test méthode updateDisabledCategory()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testUpdateDisabledCategory() :void
    {
        $faq = $this->createFaqAllDataDefault();
        $faqCat = $faq->getFaqCategories()->first();

        $title = $faqCat->getFaqCategoryTranslationByLocale('fr')->getTitle();
        $result = $this->faqService->updateDisabledCategory($faqCat->getId(), false, true);
        $this->assertEquals($this->translator->trans('faq.category.disabled.ok', ['category' => $title], domain: 'faq'), $result);
        /** @var FaqCategory $verif */
        $verif = $this->faqService->findOneById(FaqCategory::class, $faqCat->getId());
        $this->assertTrue($verif->isDisabled());

        foreach($verif->getFaqQuestions() as $faqQuestion) {
            $this->assertTrue($faqQuestion->isDisabled());
        }

        $title = $faqCat->getFaqCategoryTranslationByLocale('fr')->getTitle();
        $result = $this->faqService->updateDisabledCategory($faqCat->getId(), false, false);
        $this->assertEquals($this->translator->trans('faq.category.enabled.ok', ['category' => $title], domain: 'faq'), $result);
        /** @var FaqCategory $verif */
        $verif = $this->faqService->findOneById(FaqCategory::class, $faqCat->getId());
        $this->assertFalse($verif->isDisabled());

        foreach($verif->getFaqQuestions() as $faqQuestion) {
            $this->assertTrue($faqQuestion->isDisabled());
        }

        $title = $faqCat->getFaqCategoryTranslationByLocale('fr')->getTitle();
        $result = $this->faqService->updateDisabledCategory($faqCat->getId(), true, false);
        $this->assertEquals($this->translator->trans('faq.category.enabled.all.questions.ok', ['category' => $title], domain: 'faq'), $result);
        /** @var FaqCategory $verif */
        $verif = $this->faqService->findOneById(FaqCategory::class, $faqCat->getId());
        $this->assertFalse($verif->isDisabled());

        foreach($verif->getFaqQuestions() as $faqQuestion) {
            $this->assertFalse($faqQuestion->isDisabled());
        }
    }

    /**
     * Test méthode getListeCategoryOrderByFaq()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetListeCategoryOrderByFaq() :void
    {
        $faq = $this->createFaq();

        $faqCat1 = $this->createFaqCategory($faq, ['renderOrder' => 2]);
        $this->createFaqCategoryTranslation($faqCat1, ['locale' => 'fr', 'title' => 'faqCat 2']);

        $faqCat2 = $this->createFaqCategory($faq, ['renderOrder' => 1]);
        $this->createFaqCategoryTranslation($faqCat2, ['locale' => 'fr', 'title' => 'faqCat 1']);

        $faqCat3 = $this->createFaqCategory($faq, ['renderOrder' => 3]);
        $this->createFaqCategoryTranslation($faqCat3, ['locale' => 'fr', 'title' => 'faqCat 3']);


        $result = $this->faqService->getListeCategoryOrderByFaq($faq->getId());
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertEquals($faqCat2->getId(), $result[0]['id']);
        $this->assertEquals($faqCat1->getId(), $result[1]['id']);
        $this->assertEquals($faqCat3->getId(), $result[2]['id']);
    }

    /**
     * test getListeQuestionOrderByCategory()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetListeQuestionOrderByCategory() :void
    {
        $faq = $this->createFaq();
        $faqCat1 = $this->createFaqCategory($faq);
        $question1 = $this->createFaqQuestion($faqCat1, ['renderOrder' => 2]);
        $this->createFaqQuestionTranslation($question1, ['locale' => 'fr', 'title' => 'question 2']);

        $question2 = $this->createFaqQuestion($faqCat1, ['renderOrder' => 1]);
        $this->createFaqQuestionTranslation($question2, ['locale' => 'fr', 'title' => 'question 1']);

        $question3 = $this->createFaqQuestion($faqCat1, ['renderOrder' => 3]);
        $this->createFaqQuestionTranslation($question3, ['locale' => 'fr', 'title' => 'question 3']);

        $result = $this->faqService->getListeQuestionOrderByCategory($faqCat1->getId());
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertEquals($question2->getId(), $result[0]['id']);
        $this->assertEquals($question1->getId(), $result[1]['id']);
        $this->assertEquals($question3->getId(), $result[2]['id']);
    }

    /**
     * Test méthode addNewCategory()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testAddNewCategory() :void
    {
        $faq = $this->createFaq();
        $this->createFaqStatistique($faq, ['key' => FaqStatistiqueKey::KEY_STAT_NB_CATEGORIES, 'value' => self::getFaker()->numberBetween(1, 1000)]);
        $this->createFaqStatistique($faq, ['key' => FaqStatistiqueKey::KEY_STAT_NB_QUESTIONS, 'value' => self::getFaker()->numberBetween(1, 5)]);
        $faqCat1 = $this->createFaqCategory($faq, ['renderOrder' => 1]);
        $this->faqService->addNewCategory($faq->getId(), $faqCat1->getId(), 1);

        /** @var Faq $verif */
        $verif = $this->faqService->findOneById(Faq::class, $faq->getId());
        $this->assertCount(2, $verif->getFaqCategories());
        foreach($verif->getSortedFaqCategories() as $faqCategory) {
            /** @var FaqCategory $faqCategory */
            if($faqCategory->getId() === $faqCat1->getId()) {
                $this->assertEquals(2, $faqCategory->getRenderOrder());
            }
        }

        $faqCat2 = $this->createFaqCategory($faq, ['renderOrder' => 3]);
        $this->faqService->addNewCategory($faq->getId(), $faqCat2->getId(), 1);

        $verif = $this->faqService->findOneById(Faq::class, $faq->getId());
        $this->assertCount(4, $verif->getFaqCategories());
        foreach($verif->getSortedFaqCategories() as $faqCategory) {
            /** @var FaqCategory $faqCategory */
            if($faqCategory->getId() === $faqCat1->getId()) {
                $this->assertEquals(2, $faqCategory->getRenderOrder());
            }
            if($faqCategory->getId() === $faqCat2->getId()) {
                $this->assertEquals(4, $faqCategory->getRenderOrder());
            }
        }
    }

    /**
     * Test méthode addNewQuestion()
     * @return void
     */
    public function testAddNewQuestion() :void
    {

    }
}