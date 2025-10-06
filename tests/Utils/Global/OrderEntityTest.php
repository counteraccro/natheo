<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test class OrderEntity
 */

namespace App\Tests\Utils\Global;

use App\Entity\Admin\Content\Faq\FaqCategory;
use App\Tests\AppWebTestCase;
use App\Utils\Global\OrderEntity;

class OrderEntityTest extends AppWebTestCase
{
    /**
     * Test méthode orderByIdByAction()
     * @return void
     * @throws \Exception
     */
    public function testOrderByIdByAction(): void
    {
        $faq = $this->createFaq();
        $this->createFaqCategory($faq, ['renderOrder' => 1]);
        $faqCat2 = $this->createFaqCategory($faq, ['renderOrder' => 2]);
        $faqCat3 = $this->createFaqCategory($faq, ['renderOrder' => 3]);

        $orderEntity = new OrderEntity($faq->getFaqCategories());
        $result = $orderEntity
            ->orderByIdByAction($faqCat2->getId(), $faqCat3->getId(), OrderEntity::ACTION_BEFORE)
            ->getCollection();
        foreach ($result as $item) {
            /** @var FaqCategory $item */

            if ($item->getId() == $faqCat2->getId()) {
                $this->assertEquals(3, $item->getRenderOrder());
            }

            if ($item->getId() == $faqCat3->getId()) {
                $this->assertEquals(2, $item->getRenderOrder());
            }
        }

        $result = $orderEntity
            ->orderByIdByAction($faqCat2->getId(), $faqCat3->getId(), OrderEntity::ACTION_AFTER)
            ->getCollection();
        foreach ($result as $item) {
            /** @var FaqCategory $item */

            if ($item->getId() == $faqCat2->getId()) {
                $this->assertEquals(3, $item->getRenderOrder());
            }

            if ($item->getId() == $faqCat3->getId()) {
                $this->assertEquals(4, $item->getRenderOrder());
            }
        }
    }

    /**
     * Test méthode getIdByOrder()
     * @return void
     * @throws \Exception
     */
    public function testGetIdByOrder(): void
    {
        $faq = $this->createFaq();
        $faqCat1 = $this->createFaqCategory($faq, ['renderOrder' => 1]);
        $faqCat2 = $this->createFaqCategory($faq, ['renderOrder' => 2]);
        $faqCat3 = $this->createFaqCategory($faq, ['renderOrder' => 3]);

        $orderEntity = new OrderEntity($faq->getFaqCategories());

        $id = $orderEntity->getIdByOrder($faqCat1->getRenderOrder());
        $this->assertEquals($faqCat1->getId(), $id);

        $id = $orderEntity->getIdByOrder($faqCat2->getRenderOrder());
        $this->assertEquals($faqCat2->getId(), $id);

        $id = $orderEntity->getIdByOrder($faqCat3->getRenderOrder());
        $this->assertEquals($faqCat3->getId(), $id);
    }

    /**
     * Test méthode reOrderList()
     * @return void
     * @throws \Exception
     */
    public function testReOrderList(): void
    {
        $faq = $this->createFaq();
        $faqCat1 = $this->createFaqCategory($faq, ['renderOrder' => 1]);
        $faqCat2 = $this->createFaqCategory($faq, ['renderOrder' => 20]);
        $faqCat3 = $this->createFaqCategory($faq, ['renderOrder' => 30]);

        $orderEntity = new OrderEntity($faq->getFaqCategories());
        $result = $orderEntity->reOrderList()->getCollection();

        foreach ($result as $item) {
            /** @var FaqCategory $item */

            if ($item->getId() == $faqCat1->getId()) {
                $this->assertEquals(1, $item->getRenderOrder());
            }

            if ($item->getId() == $faqCat2->getId()) {
                $this->assertEquals(2, $item->getRenderOrder());
            }

            if ($item->getId() == $faqCat3->getId()) {
                $this->assertEquals(3, $item->getRenderOrder());
            }
        }
    }

    /**
     * Test méthode sortByProperty()
     * @return void
     * @throws \Exception
     */
    public function testSortByProperty(): void
    {
        $faq = $this->createFaq();
        $faqCat1 = $this->createFaqCategory($faq, ['renderOrder' => 3]);
        $faqCat2 = $this->createFaqCategory($faq, ['renderOrder' => 2]);
        $faqCat3 = $this->createFaqCategory($faq, ['renderOrder' => 1]);

        $orderEntity = new OrderEntity($faq->getFaqCategories());
        $result = $orderEntity->sortByProperty()->getCollection();

        foreach ($result as $item) {
            /** @var FaqCategory $item */

            if ($item->getId() == $faqCat1->getId()) {
                $this->assertEquals(3, $item->getRenderOrder());
            }

            if ($item->getId() == $faqCat2->getId()) {
                $this->assertEquals(2, $item->getRenderOrder());
            }

            if ($item->getId() == $faqCat3->getId()) {
                $this->assertEquals(1, $item->getRenderOrder());
            }
        }
    }

    /**
     * Test méthode getCollection()
     * @return void
     * @throws \Exception
     */
    public function testGetCollection(): void
    {
        $faq = $this->createFaq();
        $this->createFaqCategory($faq, ['renderOrder' => 3]);
        $this->createFaqCategory($faq, ['renderOrder' => 2]);
        $this->createFaqCategory($faq, ['renderOrder' => 1]);

        $orderEntity = new OrderEntity($faq->getFaqCategories());
        $result = $orderEntity->getCollection();

        $this->assertEquals($faq->getFaqCategories(), $result);
    }
}
