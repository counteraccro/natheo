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
        $this->client->request('PUT', $this->router->generate('admin_faq_disabled', ['id' => $faq->getId()]), parameters: ['_locale' => 'fr']);
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
    public function testDelete() :void {
        $faq = $this->createFaqAllDataDefault();

        $this->checkNoAccess('admin_faq_delete', ['id' => $faq->getId()], methode: 'DELETE');

        $user = $this->createUserContributeur();

        $this->client->loginUser($user, 'admin');
        $this->client->request('DELETE', $this->router->generate('admin_faq_delete', ['id' => $faq->getId()]), parameters: ['_locale' => 'fr']);
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
    public function testLoadFaq() :void {

        $faq = $this->createFaqAllDataDefault();

        $this->checkNoAccess('admin_faq_load_faq');

        $user = $this->createUserContributeur();

        $this->client->loginUser($user, 'admin');
        $this->client->request('GET', $this->router->generate('admin_faq_load_faq', ['id' => $faq->getId()]), parameters: ['_locale' => 'fr']);
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
    public function testSave() :void {
        $faq = $this->createFaqAllDataDefault();

        $dataPopulate = [
            "id" => $faq->getId(),
            "disabled" => false,
            "faqTranslations" => [
                0 => [
                    "id" => 4,
                    "faq" => 2,
                    "locale" => "fr",
                    "title" => self::getFaker()->text('20')
                ],
                1 => [
                    "id" => 5,
                    "faq" => 2,
                    "locale" => "en",
                    "title" => self::getFaker()->text('20'),
                ],
                2 => [
                    "id" => 6,
                    "faq" => 2,
                    "locale" => "es",
                    "title" => self::getFaker()->text('20')
                ]
            ],
            "faqCategories" => [
                0 => [
                    "id" => 5,
                    "faq" => 2,
                    "disabled" => false,
                    "renderOrder" => 1,
                    "faqCategoryTranslations" => [
                        0 => [
                            "id" => 13,
                            "faqCategory" => 5,
                            "locale" => "fr",
                            "title" => self::getFaker()->text('20')
                        ],
                        1 => [
                            "id" => 14,
                            "faqCategory" => 5,
                            "locale" => "en",
                            "title" => self::getFaker()->text('20')
                        ],
                        2 => [
                            "id" => 15,
                            "faqCategory" => 5,
                            "locale" => "es",
                            "title" => self::getFaker()->text('20')
                        ]
                    ],
                    "faqQuestions" => [
                        0 => [
                            "id" => 7,
                            "faqCategory" => 5,
                            "disabled" => false,
                            "renderOrder" => 1,
                            "faqQuestionTranslations" => [
                                0 => [
                                    "id" => 19,
                                    "FaqQuestion" => 7,
                                    "locale" => "fr",
                                    "title" => self::getFaker()->text('20'),
                                    "answer" => self::getFaker()->text('100')
                                ],
                                1 => [
                                    "id" => 20,
                                    "FaqQuestion" => 7,
                                    "locale" => "en",
                                    "title" => self::getFaker()->text('20'),
                                    "answer" => self::getFaker()->text('100')
                                ],
                                2 => [
                                    "id" => 21,
                                    "FaqQuestion" => 7,
                                    "locale" => "es",
                                    "title" => self::getFaker()->text('20'),
                                    "answer" => self::getFaker()->text('100')
                                ]
                            ]
                        ]
                    ],
                ],
            ],
            "faqStatistiques" => [
                0 => [
                    "id" => 3,
                    "faq" => 2,
                    "key" => "KEY_STAT_NB_CATEGORIES",
                    "value" => "1",
                ],
                1 => [
                    "id" => 4,
                    "faq" => 2,
                    "key" => "KEY_STAT_NB_QUESTIONS",
                    "value" => "1",
                ]
            ]
        ];

        $this->checkNoAccess('admin_faq_save', methode: 'PUT');

        $user = $this->createUserContributeur();

        $this->client->loginUser($user, 'admin');
        $this->client->request('PUT', $this->router->generate('admin_faq_save'), content: json_encode(['faq' => $dataPopulate]));
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
    public function newFaq() :void {

    }
}