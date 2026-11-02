<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Test service Faq
 */

namespace App\Tests\Service\Admin\Content\Faq;

use App\Entity\Admin\Content\Faq\Faq;
use App\Entity\Admin\Content\Faq\FaqCategory;
use App\Entity\Admin\Content\Faq\FaqQuestion;
use App\Service\Admin\Content\Faq\FaqService;
use App\Tests\AppWebTestCase;
use App\Enum\Admin\Content\Faq\FaqStatistiqueAction;
use App\Enum\Admin\Content\Faq\FaqStatistiqueKey;
use App\Enum\Admin\Content\Page\PageContentType;
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
            'key' => FaqStatistiqueKey::NB_CATEGORIES->value,
            'value' => strval(self::getFaker()->numberBetween(1, 10)),
        ]);
        $faqStat2 = $this->createFaqStatistique($faq, [
            'key' => FaqStatistiqueKey::NB_QUESTIONS->value,
            'value' => strval(self::getFaker()->numberBetween(12, 40)),
        ]);
        $faqStat3 = $this->createFaqStatistique($faq, [
            'key' => 'key-3',
            'value' => strval(self::getFaker()->numberBetween(12, 40)),
        ]);

        $this->faqService->updateFaqStatistique(
            $faq,
            FaqStatistiqueKey::NB_CATEGORIES->value,
            FaqStatistiqueAction::ADD,
            10,
        );
        $this->faqService->updateFaqStatistique(
            $faq,
            FaqStatistiqueKey::NB_QUESTIONS->value,
            FaqStatistiqueAction::SUB,
            10,
        );
        $this->faqService->updateFaqStatistique($faq, 'key-3', FaqStatistiqueAction::OVERWRITE, 99);
        $verif = $this->faqService->findOneById(Faq::class, $faq->getId());

        $stat1 = $verif->getFaqStatistiqueByKey(FaqStatistiqueKey::NB_CATEGORIES->value);
        $stat2 = $verif->getFaqStatistiqueByKey(FaqStatistiqueKey::NB_QUESTIONS->value);
        $stat3 = $verif->getFaqStatistiqueByKey('key-3');

        $this->assertEquals($faqStat1->getValue(), $stat1->getValue());
        $this->assertEquals($faqStat2->getValue(), $stat2->getValue());
        $this->assertEquals(99, $stat3->getValue());
    }

    /**
     * Test méthode updateFaqStatistique() : statistique absente créée
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testUpdateFaqStatistiqueMissing(): void
    {
        $faq = $this->createFaq();
        $this->assertNull($faq->getFaqStatistiqueByKey(FaqStatistiqueKey::NB_QUESTIONS->value));
        $this->assertEquals(0, $this->faqService->getStatistiqueValue($faq, FaqStatistiqueKey::NB_QUESTIONS));

        $this->faqService->updateFaqStatistique(
            $faq,
            FaqStatistiqueKey::NB_QUESTIONS->value,
            FaqStatistiqueAction::SUB,
            3,
        );
        $this->assertEquals(0, $this->faqService->getStatistiqueValue($faq, FaqStatistiqueKey::NB_QUESTIONS));

        $this->faqService->updateFaqStatistique($faq, FaqStatistiqueKey::NB_QUESTIONS->value, value: 4);
        $this->em->clear();
        $verif = $this->faqService->findOneById(Faq::class, $faq->getId());
        $this->assertEquals(4, $this->faqService->getStatistiqueValue($verif, FaqStatistiqueKey::NB_QUESTIONS));
    }

    /**
     * Test méthode countPages()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testCountPages(): void
    {
        $faq = $this->createFaqAllDataDefault();
        $this->assertEquals(0, $this->faqService->countPages($faq));

        $page = $this->createPage();
        // Deux blocs FAQ sur la même page : comptée une seule fois
        $this->createPageContent($page, ['type' => PageContentType::FAQ->value, 'typeId' => $faq->getId()]);
        $this->createPageContent($page, ['type' => PageContentType::FAQ->value, 'typeId' => $faq->getId()]);
        $this->createPageContent(customData: ['type' => PageContentType::FAQ->value, 'typeId' => $faq->getId()]);
        // Même identifiant mais autre type de contenu : ignoré
        $this->createPageContent(customData: ['type' => PageContentType::LISTING->value, 'typeId' => $faq->getId()]);

        $this->assertEquals(2, $this->faqService->countPages($faq));
    }
}
