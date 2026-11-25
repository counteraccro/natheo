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
     * Test méthode countQuestions()
     * @return void
     */
    public function testCountQuestions(): void
    {
        $faq = $this->createFaq();
        $this->assertEquals(0, $this->faqService->countQuestions($faq));

        $category = $this->createFaqCategory($faq);
        $this->createFaqQuestion($category);
        $this->createFaqQuestion($category);
        $this->createFaqQuestion($this->createFaqCategory($faq));
        $this->assertEquals(3, $this->faqService->countQuestions($faq));
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
