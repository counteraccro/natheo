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
use App\Utils\Content\Faq\FaqConst;
use App\Utils\Content\Faq\FaqStatistiqueKey;
use App\Utils\Global\OrderEntity;
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
    public function testGetAllFormatToGrid(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $faq = $this->createFaqAllDataDefault();
        }

        $queryParams = [
            'search' => '',
            'orderField' => 'id',
            'order' => 'DESC',
            'locale' => 'fr',
        ];

        $result = $this->faqService->getAllFormatToGrid(1, 4, $queryParams);
        $this->assertArrayHasKey('nb', $result);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('column', $result);
        $this->assertArrayHasKey('sql', $result);
        $this->assertArrayHasKey('translate', $result);
        $this->assertEquals(5, $result['nb']);
        $this->assertCount(4, $result['data']);

        $search = $faq->getFaqTranslationByLocale('fr')->getTitle();

        $queryParams = [
            'search' => $search,
            'orderField' => 'id',
            'order' => 'DESC',
            'locale' => 'fr',
        ];

        $result = $this->faqService->getAllFormatToGrid(1, 5, $queryParams);
        $this->assertEquals(1, $result['nb']);
        $this->assertCount(1, $result['data']);

        $userId = $faq->getUser()->getId();

        $queryParams = [
            'search' => $search,
            'orderField' => 'id',
            'order' => 'DESC',
            'locale' => 'fr',
            'userId' => $userId,
        ];

        $result = $this->faqService->getAllFormatToGrid(1, 5, $queryParams);
        $this->assertEquals(1, $result['nb']);
        $this->assertCount(1, $result['data']);

        $queryParams = [
            'search' => '',
            'orderField' => 'id',
            'order' => 'DESC',
            'locale' => 'fr',
            'userId' => $userId,
        ];

        $faq = $this->createFaqAllDataDefault();
        $userId = $faq->getUser()->getId();
        $result = $this->faqService->getAllFormatToGrid(1, 5, $queryParams);
        $this->assertEquals(1, $result['nb']);
        $this->assertCount(1, $result['data']);
    }

    /**
     * Test méthode getAllPaginate()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetAllPaginate(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $faq = $this->createFaqAllDataDefault();
        }

        $queryParams = [
            'search' => '',
            'orderField' => 'id',
            'order' => 'DESC',
            'locale' => 'fr',
        ];

        $result = $this->faqService->getAllPaginate(1, 4, $queryParams);
        $this->assertEquals(4, $result->getIterator()->count());
        $this->assertEquals(5, $result->count());

        $search = $faq->getFaqTranslationByLocale('fr')->getTitle();

        $queryParams = [
            'search' => $search,
            'orderField' => 'id',
            'order' => 'DESC',
            'locale' => 'fr',
        ];
        $result = $this->faqService->getAllPaginate(1, 4, $queryParams);
        $this->assertEquals(1, $result->getIterator()->count());
        $this->assertEquals(1, $result->count());

        $userId = $faq->getUser()->getId();

        $queryParams = [
            'search' => $search,
            'orderField' => 'id',
            'order' => 'DESC',
            'locale' => 'fr',
            'userId' => $userId,
        ];

        $result = $this->faqService->getAllPaginate(1, 4, $queryParams);
        $this->assertEquals(1, $result->getIterator()->count());
        $this->assertEquals(1, $result->count());

        $faq = $this->createFaqAllDataDefault();
        $userId = $faq->getUser()->getId();

        $queryParams = [
            'search' => '',
            'orderField' => 'id',
            'order' => 'DESC',
            'locale' => 'fr',
            'userId' => $userId,
        ];

        $result = $this->faqService->getAllPaginate(1, 4, $queryParams);
        $this->assertEquals(1, $result->getIterator()->count());
        $this->assertEquals(1, $result->count());
    }

    /**
     * Test méthode updateFaqStatistique()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testUpdateFaqStatistique(): void
    {
        $faq = $this->createFaq();
        $faqStat1 = $this->createFaqStatistique($faq, [
            'key' => FaqStatistiqueKey::KEY_STAT_NB_CATEGORIES,
            'value' => self::getFaker()->numberBetween(1, 10),
        ]);
        $faqStat2 = $this->createFaqStatistique($faq, [
            'key' => FaqStatistiqueKey::KEY_STAT_NB_QUESTIONS,
            'value' => self::getFaker()->numberBetween(12, 40),
        ]);
        $faqStat3 = $this->createFaqStatistique($faq, [
            'key' => 'key-3',
            'value' => self::getFaker()->numberBetween(12, 40),
        ]);

        $this->faqService->updateFaqStatistique(
            $faq,
            FaqStatistiqueKey::KEY_STAT_NB_CATEGORIES,
            FaqConst::STATISTIQUE_ACTION_ADD,
            10,
        );
        $this->faqService->updateFaqStatistique(
            $faq,
            FaqStatistiqueKey::KEY_STAT_NB_QUESTIONS,
            FaqConst::STATISTIQUE_ACTION_SUB,
            10,
        );
        $this->faqService->updateFaqStatistique($faq, 'key-3', FaqConst::STATISTIQUE_ACTION_OVERWRITE, 99);
        $verif = $this->faqService->findOneById(Faq::class, $faq->getId());

        $stat1 = $verif->getFaqStatistiqueByKey(FaqStatistiqueKey::KEY_STAT_NB_CATEGORIES);
        $stat2 = $verif->getFaqStatistiqueByKey(FaqStatistiqueKey::KEY_STAT_NB_QUESTIONS);
        $stat3 = $verif->getFaqStatistiqueByKey('key-3');

        $this->assertEquals($faqStat1->getValue(), $stat1->getValue());
        $this->assertEquals($faqStat2->getValue(), $stat2->getValue());
        $this->assertEquals(99, $stat3->getValue());
    }
}
