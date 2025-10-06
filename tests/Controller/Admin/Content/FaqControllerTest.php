<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 *
 */

namespace App\Tests\Controller\Admin\Content;

use App\Entity\Admin\Content\Faq\Faq;
use App\Repository\Admin\Content\Faq\FaqRepository;
use App\Tests\AppWebTestCase;
use App\Utils\Content\Faq\FaqConst;
use App\Utils\Content\Faq\FaqStatistiqueKey;

class FaqControllerTest extends AppWebTestCase
{
    /**
     * Test méthode index()
     * @return void
     */
    public function testIndex(): void
    {
        $this->checkNoAccess('admin_faq_index');

        $user = $this->createUserContributeur();

        $this->client->loginUser($user, 'admin');
        $this->client->request('GET', $this->router->generate('admin_faq_index'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $this->translator->trans('faq.index.page_title_h1', domain: 'faq'));
    }

    /**
     * Test méthode loadGridData()
     * @return void
     */
    public function testLoadGridData(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->createFaqAllDataDefault();
        }

        $this->checkNoAccess('admin_faq_load_grid_data');

        $userContributeur = $this->createUserContributeur();
        $this->client->loginUser($userContributeur, 'admin');
        $this->client->request('GET', $this->router->generate('admin_faq_load_grid_data', ['page' => 1, 'limit' => 4]));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);

        $this->assertEquals(5, $content['nb']);
        $this->assertCount(4, $content['data']);
        $this->assertArrayHasKey('nb', $content);
        $this->assertArrayHasKey('data', $content);
        $this->assertArrayHasKey('column', $content);
        $this->assertArrayHasKey('sql', $content);
        $this->assertArrayHasKey('translate', $content);
    }

    /**
     * Test méthode add()
     * @return void
     */
    public function testAdd(): void
    {
        $this->checkNoAccess('admin_faq_add');

        $user = $this->createUserContributeur();

        $this->client->loginUser($user, 'admin');
        $this->client->request('GET', $this->router->generate('admin_faq_add'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $this->translator->trans('faq.add.page_title_h1', domain: 'faq'));

        $faq = $this->createFaqAllDataDefault();

        $this->client->request('GET', $this->router->generate('admin_faq_update', ['id' => $faq->getId()]));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $this->translator->trans('faq.update.page_title_h1', domain: 'faq'));
    }

    /**
     * Test méthode updateDisabled()
     * @return void
     */
    public function testUpdateDisabled(): void
    {
        $faq = $this->createFaqAllDataDefault();

        $this->checkNoAccess('admin_faq_disabled', ['id' => $faq->getId()], methode: 'PUT');

        $user = $this->createUserContributeur();

        $this->client->loginUser($user, 'admin');
        $this->client->request(
            'PUT',
            $this->router->generate('admin_faq_disabled', ['id' => $faq->getId()]),
            parameters: ['_locale' => 'fr'],
        );
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('success', $content);
        $this->assertTrue($content['success']);

        /** @var FaqRepository $faqRepo */
        $faqRepo = $this->em->getRepository(Faq::class);
        $verif = $faqRepo->findOneBy(['id' => $faq->getId()]);
        $this->assertEquals(!$verif->isDisabled(), $faq->isDisabled());
    }

    /**
     * Test méthode delete()
     * @return void
     */
    public function testDelete(): void
    {
        $faq = $this->createFaqAllDataDefault();

        $this->checkNoAccess('admin_faq_delete', ['id' => $faq->getId()], methode: 'DELETE');

        $user = $this->createUserContributeur();

        $this->client->loginUser($user, 'admin');
        $this->client->request(
            'DELETE',
            $this->router->generate('admin_faq_delete', ['id' => $faq->getId()]),
            parameters: ['_locale' => 'fr'],
        );
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('success', $content);
        $this->assertTrue($content['success']);

        /** @var FaqRepository $faqRepo */
        $faqRepo = $this->em->getRepository(Faq::class);
        $verif = $faqRepo->findOneBy(['id' => $faq->getId()]);
        $this->assertNull($verif);
    }

    /**
     * Test méthode loadFaq()
     * @return void
     */
    public function testLoadFaq(): void
    {
        $faq = $this->createFaqAllDataDefault();

        $this->checkNoAccess('admin_faq_load_faq');

        $user = $this->createUserContributeur();

        $this->client->loginUser($user, 'admin');
        $this->client->request(
            'GET',
            $this->router->generate('admin_faq_load_faq', ['id' => $faq->getId()]),
            parameters: ['_locale' => 'fr'],
        );
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('faq', $content);
        $this->assertEquals($content['faq']['id'], $faq->getId());
        $this->assertArrayHasKey('max_render_order', $content);
        $this->assertEquals($content['max_render_order'], $faq->getAllMaxRender());
    }

    /**
     * test méthode save()
     * @return void
     */
    public function testSave(): void
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

        $this->checkNoAccess('admin_faq_save', methode: 'PUT');

        $user = $this->createUserContributeur();

        $this->client->loginUser($user, 'admin');
        $this->client->request(
            'PUT',
            $this->router->generate('admin_faq_save'),
            content: json_encode(['faq' => $dataPopulate]),
        );
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('success', $content);
        $this->assertTrue($content['success']);
    }

    /**
     * Test méthode newFaq()
     * @return void
     */
    public function testNewFaq(): void
    {
        $this->checkNoAccess('admin_faq_new_faq', methode: 'POST');

        $user = $this->createUserContributeur();

        $data = ['title' => self::getFaker()->text(50)];

        $this->client->loginUser($user, 'admin');
        $this->client->request('POST', $this->router->generate('admin_faq_new_faq'), content: json_encode($data));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('success', $content);
        $this->assertTrue($content['success']);
        $this->assertArrayHasKey('msg', $content);
        $this->assertArrayHasKey('url_redirect', $content);

        $tmp = explode('/', $content['url_redirect']);
        $id = $tmp[count($tmp) - 1];

        /** @var FaqRepository $faqRepo */
        $faqRepo = $this->em->getRepository(Faq::class);
        $verif = $faqRepo->find($id);
        $this->assertEquals($data['title'], $verif->getFaqTranslationByLocale('fr')->getTitle());
    }

    /**
     * Test méthode updateDisabledCatQuestion()
     * @return void
     */
    public function testUpdateDisabledCatQuestion(): void
    {
        $faq = $this->createFaqAllDataDefault();
        $category = $faq->getFaqCategories()->first();
        $question = $category->getFaqQuestions()->first();

        $data = [
            'allQuestion' => true,
            'id' => $question->getId(),
            'type' => FaqConst::TYPE_QUESTION,
            'value' => true,
        ];

        $this->checkNoAccess('admin_faq_update_disabled', methode: 'PUT');

        $user = $this->createUserContributeur();

        $this->client->loginUser($user, 'admin');
        $this->client->request(
            'PUT',
            $this->router->generate('admin_faq_update_disabled'),
            content: json_encode($data),
        );
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);

        $this->assertIsArray($content);
        $this->assertArrayHasKey('success', $content);
        $this->assertTrue($content['success']);

        $data = [
            'allQuestion' => true,
            'id' => $category->getId(),
            'type' => FaqConst::TYPE_CATEGORY,
            'value' => false,
        ];

        $this->checkNoAccess('admin_faq_update_disabled', methode: 'PUT');

        $user = $this->createUserContributeur();

        $this->client->loginUser($user, 'admin');
        $this->client->request(
            'PUT',
            $this->router->generate('admin_faq_update_disabled'),
            content: json_encode($data),
        );
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);

        $this->assertIsArray($content);
        $this->assertArrayHasKey('success', $content);
        $this->assertTrue($content['success']);
    }

    /**
     * Test méthode orderListeByEntity()
     * @return void
     */
    public function testOrderListeByEntity(): void
    {
        $this->checkNoAccess('admin_faq_order_by_type');

        $faq = $this->createFaq();
        $faqCat1 = $this->createFaqCategory($faq, ['renderOrder' => 2]);
        $this->createFaqCategoryTranslation($faqCat1, ['locale' => 'fr', 'title' => 'faqCat 2']);

        $question1 = $this->createFaqQuestion($faqCat1, ['renderOrder' => 2]);
        $this->createFaqQuestionTranslation($question1, ['locale' => 'fr', 'title' => 'question 2']);

        $question2 = $this->createFaqQuestion($faqCat1, ['renderOrder' => 1]);
        $this->createFaqQuestionTranslation($question2, ['locale' => 'fr', 'title' => 'question 1']);

        $question3 = $this->createFaqQuestion($faqCat1, ['renderOrder' => 3]);
        $this->createFaqQuestionTranslation($question3, ['locale' => 'fr', 'title' => 'question 3']);

        $faqCat2 = $this->createFaqCategory($faq, ['renderOrder' => 1]);
        $this->createFaqCategoryTranslation($faqCat2, ['locale' => 'fr', 'title' => 'faqCat 1']);

        $faqCat3 = $this->createFaqCategory($faq, ['renderOrder' => 3]);
        $this->createFaqCategoryTranslation($faqCat3, ['locale' => 'fr', 'title' => 'faqCat 3']);

        $user = $this->createUserContributeur();
        $this->client->loginUser($user, 'admin');
        $this->client->request(
            'GET',
            $this->router->generate('admin_faq_order_by_type', [
                'id' => $faqCat1->getId(),
                'type' => FaqConst::TYPE_QUESTION,
            ]),
        );
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);

        $this->assertIsArray($content);
        $this->assertArrayHasKey('success', $content);
        $this->assertTrue($content['success']);

        $this->assertArrayHasKey('list', $content);
        $this->assertArrayHasKey('msg', $content);
        $this->assertEquals($question2->getId(), $content['list'][0]['id']);
        $this->assertEquals($question1->getId(), $content['list'][1]['id']);
        $this->assertEquals($question3->getId(), $content['list'][2]['id']);

        $this->client->request(
            'GET',
            $this->router->generate('admin_faq_order_by_type', [
                'id' => $faq->getId(),
                'type' => FaqConst::TYPE_CATEGORY,
            ]),
        );
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);

        $this->assertIsArray($content);
        $this->assertArrayHasKey('success', $content);
        $this->assertTrue($content['success']);

        $this->assertArrayHasKey('list', $content);
        $this->assertArrayHasKey('msg', $content);

        $this->assertEquals($faqCat2->getId(), $content['list'][0]['id']);
        $this->assertEquals($faqCat1->getId(), $content['list'][1]['id']);
        $this->assertEquals($faqCat3->getId(), $content['list'][2]['id']);
    }

    /**
     * Test méthode newCatQuestion()
     * @return void
     */
    public function testNewCatQuestion(): void
    {
        $this->checkNoAccess('admin_faq_new_cat_question', methode: 'POST');

        $faq = $this->createFaq();
        $this->createFaqStatistique($faq, [
            'key' => FaqStatistiqueKey::KEY_STAT_NB_CATEGORIES,
            'value' => self::getFaker()->numberBetween(1, 1000),
        ]);
        $this->createFaqStatistique($faq, [
            'key' => FaqStatistiqueKey::KEY_STAT_NB_QUESTIONS,
            'value' => self::getFaker()->numberBetween(1, 5),
        ]);
        $faqCat1 = $this->createFaqCategory($faq, ['renderOrder' => 1]);
        $question = $this->createFaqQuestion($faqCat1, ['renderOrder' => 1]);

        $data = [
            'id' => $faq->getId(),
            'idOrder' => $faqCat1->getId(),
            'orderType' => 'before',
            'type' => FaqConst::TYPE_CATEGORY,
        ];

        $user = $this->createUserContributeur();
        $this->client->loginUser($user, 'admin');
        $this->client->request(
            'POST',
            $this->router->generate('admin_faq_new_cat_question'),
            content: json_encode($data),
        );
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);

        $this->assertIsArray($content);
        $this->assertArrayHasKey('success', $content);
        $this->assertTrue($content['success']);

        $data = [
            'id' => $faqCat1->getId(),
            'idOrder' => $question->getId(),
            'orderType' => 'before',
            'type' => FaqConst::TYPE_QUESTION,
        ];

        $this->client->loginUser($user, 'admin');
        $this->client->request(
            'POST',
            $this->router->generate('admin_faq_new_cat_question'),
            content: json_encode($data),
        );
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);

        $this->assertIsArray($content);
        $this->assertArrayHasKey('success', $content);
        $this->assertTrue($content['success']);
    }

    /**
     * test méthode changeOrderRender()
     * @return void
     */
    public function testChangeOrderRender(): void
    {
        $faq = $this->createFaq();

        $faqCat1 = $this->createFaqCategory($faq, ['renderOrder' => 1]);
        $this->createFaqCategoryTranslation($faqCat1, ['locale' => 'fr', 'title' => 'faqCat 1']);

        $question1 = $this->createFaqQuestion($faqCat1, ['renderOrder' => 1]);
        $this->createFaqQuestionTranslation($question1, ['locale' => 'fr', 'title' => 'question 1']);

        $question2 = $this->createFaqQuestion($faqCat1, ['renderOrder' => 2]);
        $this->createFaqQuestionTranslation($question2, ['locale' => 'fr', 'title' => 'question 2']);

        $question3 = $this->createFaqQuestion($faqCat1, ['renderOrder' => 3]);
        $this->createFaqQuestionTranslation($question3, ['locale' => 'fr', 'title' => 'question 3']);

        $faqCat2 = $this->createFaqCategory($faq, ['renderOrder' => 2]);
        $this->createFaqCategoryTranslation($faqCat2, ['locale' => 'fr', 'title' => 'faqCat 2']);

        $faqCat3 = $this->createFaqCategory($faq, ['renderOrder' => 3]);
        $this->createFaqCategoryTranslation($faqCat3, ['locale' => 'fr', 'title' => 'faqCat 3']);

        $this->checkNoAccess('admin_faq_update_order', methode: 'PUT');

        $user = $this->createUserContributeur();
        $this->client->loginUser($user, 'admin');

        $data = [
            'id' => $faq->getId(),
            'idOrder' => $faqCat3->getId(),
            'orderType' => 'before',
            'type' => FaqConst::TYPE_CATEGORY,
        ];

        $this->client->request('PUT', $this->router->generate('admin_faq_update_order'), content: json_encode($data));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);

        $this->assertIsArray($content);
        $this->assertArrayHasKey('success', $content);
        $this->assertTrue($content['success']);

        $data = [
            'id' => $faqCat1->getId(),
            'idOrder' => $question2->getId(),
            'orderType' => 'before',
            'type' => FaqConst::TYPE_QUESTION,
        ];

        $this->client->request('PUT', $this->router->generate('admin_faq_update_order'), content: json_encode($data));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);

        $this->assertIsArray($content);
        $this->assertArrayHasKey('success', $content);
        $this->assertTrue($content['success']);
    }

    /**
     * Test méthode deleteCategoryQuestion()
     * @return void
     */
    public function testDeleteCategoryQuestion(): void
    {
        $faq = $this->createFaq();
        $this->createFaqStatistique($faq, ['key' => FaqStatistiqueKey::KEY_STAT_NB_CATEGORIES, 'value' => 3]);
        $this->createFaqStatistique($faq, ['key' => FaqStatistiqueKey::KEY_STAT_NB_QUESTIONS, 'value' => 3]);
        $faqCat1 = $this->createFaqCategory($faq);
        $this->createFaqCategoryTranslation($faqCat1, ['locale' => 'fr', 'title' => 'faqCat 1']);
        $question1 = $this->createFaqQuestion($faqCat1, ['renderOrder' => 1]);
        $this->createFaqQuestionTranslation($question1, ['locale' => 'fr', 'title' => 'question 1']);

        $question2 = $this->createFaqQuestion($faqCat1, ['renderOrder' => 2]);
        $this->createFaqQuestionTranslation($question2, ['locale' => 'fr', 'title' => 'question 2']);

        $question3 = $this->createFaqQuestion($faqCat1, ['renderOrder' => 3]);
        $this->createFaqQuestionTranslation($question3, ['locale' => 'fr', 'title' => 'question 3']);

        $faqCat2 = $this->createFaqCategory($faq, ['renderOrder' => 2]);
        $this->createFaqCategoryTranslation($faqCat2, ['locale' => 'fr', 'title' => 'faqCat 2']);

        $this->checkNoAccess('admin_faq_delete_category_question', methode: 'DELETE');

        $user = $this->createUserContributeur();
        $this->client->loginUser($user, 'admin');

        $this->client->request(
            'DELETE',
            $this->router->generate('admin_faq_delete_category_question', [
                'id' => $question1->getId(),
                'type' => FaqConst::TYPE_QUESTION,
            ]),
        );
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);

        $this->assertIsArray($content);
        $this->assertArrayHasKey('success', $content);
        $this->assertTrue($content['success']);

        $this->client->request(
            'DELETE',
            $this->router->generate('admin_faq_delete_category_question', [
                'id' => $faqCat1->getId(),
                'type' => FaqConst::TYPE_CATEGORY,
            ]),
        );
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);

        $this->assertIsArray($content);
        $this->assertArrayHasKey('success', $content);
        $this->assertTrue($content['success']);
    }
}
