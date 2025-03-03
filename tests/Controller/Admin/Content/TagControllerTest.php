<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test tagController
 */

namespace App\Tests\Controller\Admin\Content;

use App\Entity\Admin\Content\Tag\Tag;
use App\Repository\Admin\Content\Tag\TagRepository;
use App\Tests\AppWebTestCase;
use function PHPUnit\Framework\assertIsArray;

class TagControllerTest extends AppWebTestCase
{
    /**
     * Test méthode index()
     * @return void
     */
    public function testIndex(): void
    {
        $this->checkNoAccess('admin_tag_index');

        $user = $this->createUserContributeur();

        $this->client->loginUser($user, 'admin');
        $this->client->request('GET', $this->router->generate('admin_tag_index'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $this->translator->trans('tag.index.page_title_h1', domain: 'tag'));
    }

    /**
     * Test méthode loadGridData()
     * @return void
     */
    public function testLoadGridData(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $tag = $this->createTag();
            foreach ($this->locales as $lang) {
                $this->createTagTranslation($tag, ['locale' => $lang]);
            }
        }

        $this->checkNoAccess('admin_tag_load_grid_data');

        $userContributeur = $this->createUserContributeur();
        $this->client->loginUser($userContributeur, 'admin');
        $this->client->request('GET', $this->router->generate('admin_tag_load_grid_data', ['page' => 1, 'limit' => 5]));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);

        $this->assertEquals(10, $content['nb']);
        $this->assertCount(5, $content['data']);
        $this->assertArrayHasKey('nb', $content);
        $this->assertArrayHasKey('data', $content);
        $this->assertArrayHasKey('column', $content);
        $this->assertArrayHasKey('sql', $content);
        $this->assertArrayHasKey('translate', $content);
    }

    /**
     * Test méthode updateDisabled()
     * @return void
     */
    public function testUpdateDisabled(): void
    {
        /** @var TagRepository $repo */
        $repo = $this->em->getRepository(Tag::class);

        $tag = $this->createTag(['disabled' => false]);
        foreach ($this->locales as $lang) {
            $this->createTagTranslation($tag, ['locale' => $lang]);
        }

        $this->checkNoAccess('admin_tag_update_disabled', ['id' => $tag->getId()], 'PUT');

        $userContributeur = $this->createUserContributeur();
        $this->client->loginUser($userContributeur, 'admin');
        $this->client->request('PUT', $this->router->generate('admin_tag_update_disabled', ['id' => $tag->getId()]));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertTrue($content['success']);

        $verif = $repo->findOneBy(['id' => $tag->getId()]);
        $this->assertTrue($verif->isDisabled());

        $this->client->request('PUT', $this->router->generate('admin_tag_update_disabled', ['id' => $tag->getId()]));
        $this->em->clear();
        $verif = $repo->findOneBy(['id' => $tag->getId()]);
        $this->assertFalse($verif->isDisabled());

    }

    /**
     * Test méthode delete()
     * @return void
     */
    public function testDelete(): void
    {
        /** @var TagRepository $repo */
        $repo = $this->em->getRepository(Tag::class);

        $tag = $this->createTag(['disabled' => false]);
        foreach ($this->locales as $lang) {
            $this->createTagTranslation($tag, ['locale' => $lang]);
        }

        $this->checkNoAccess('admin_tag_delete', ['id' => $tag->getId()], 'DELETE');

        $userContributeur = $this->createUserContributeur();
        $this->client->loginUser($userContributeur, 'admin');
        $this->client->request('DELETE', $this->router->generate('admin_tag_delete', ['id' => $tag->getId()]));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertTrue($content['success']);

        $result = $repo->findOneBy(['id' => $tag->getId()]);
        $this->assertNull($result);
    }

    /**
     * Test méthode add()
     * @return void
     */
    public function testAdd(): void
    {
        $this->checkNoAccess('admin_tag_add');

        $tag = $this->createTag(['disabled' => false]);
        foreach ($this->locales as $lang) {
            $this->createTagTranslation($tag, ['locale' => $lang]);
        }

        $user = $this->createUserContributeur();

        $this->client->loginUser($user, 'admin');
        $this->client->request('GET', $this->router->generate('admin_tag_add'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $this->translator->trans('tag.add.page_title_h1', domain: 'tag'));

        $this->client->request('GET', $this->router->generate('admin_tag_update', ['id' => $tag->getId()]));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $this->translator->trans('tag.update.page_title_h1', domain: 'tag'));
    }

    /**
     * Test méthode save()
     * @return void
     */
    public function testSave(): void
    {
        /** @var TagRepository $repo */
        $repo = $this->em->getRepository(Tag::class);

        $data = [
            'tag' => [
                "id" => null,
                "color" => "#9e2e2e",
                "disabled" => false,
                "tagTranslations" => [
                    [
                        "id" => null,
                        "tag" => null,
                        "locale" => "fr",
                        "label" => "fr-test-unitaire"
                    ],
                    [
                        "id" => null,
                        "tag" => null,
                        "locale" => "en",
                        "label" => "en-test-unitaire"
                    ],
                    [
                        "id" => null,
                        "tag" => null,
                        "locale" => "es",
                        "label" => "es-test-unitaire"
                    ]
                ]
            ]
        ];

        $this->checkNoAccess('admin_tag_save', methode: 'POST');

        $userContributeur = $this->createUserContributeur();
        $this->client->loginUser($userContributeur, 'admin');
        $this->client->request('POST', $this->router->generate('admin_tag_save'), content: json_encode($data));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertEquals('new', $content['etat']);

        $tags = $repo->findAll();
        $this->assertCount(1, $tags);
        $tag = $tags[0];
        $this->assertInstanceOf(Tag::class, $tag);
        $this->assertEquals($data['tag']['color'], $tag->getColor());
    }

    /**
     * Test méthode search
     * @return void
     */
    public function testSearch(): void
    {
        $tag = $this->createTag(['disabled' => false]);
        foreach ($this->locales as $locale) {
            $this->createTagTranslation($tag, ['locale' => $locale, 'label' => $locale . ' Un label']);
        }

        $this->checkNoAccess('admin_tag_search');

        $userContributeur = $this->createUserContributeur();
        $this->client->loginUser($userContributeur, 'admin');
        $this->client->request('GET', $this->router->generate('admin_tag_search', ['locale' => 'fr', 'search' => 'fr Un label']));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        assertIsArray($content);
        $this->assertCount(1, $content);
    }

    /**
     * Test méthode getTagByName()
     * @return void
     */
    public function testGetTagByName(): void
    {
        /** @var TagRepository $repo */
        $repo = $this->em->getRepository(Tag::class);

        $this->checkNoAccess('admin_tag_tag_by_name');

        $userContributeur = $this->createUserContributeur();
        $this->client->loginUser($userContributeur, 'admin');
        $this->client->request('GET', $this->router->generate('admin_tag_tag_by_name', ['locale' => 'fr', 'search' => 'label-unitaire']));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        assertIsArray($content);
        $this->assertTrue($content['success']);

        /** @var Tag $tag */
        $tag = $repo->findOneBy(['id' => $content['tag']['id']]);
        $this->assertEquals('label-unitaire', $tag->getTagTranslationByLocale('fr')->getLabel());

        $tag = $this->createTag(['disabled' => false]);
        foreach ($this->locales as $locale) {
            $this->createTagTranslation($tag, ['locale' => $locale, 'label' => $locale . ' Un label']);
        }
        $this->client->request('GET', $this->router->generate('admin_tag_tag_by_name', ['locale' => 'fr', 'search' => 'fr Un label']));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        assertIsArray($content);
        $this->assertTrue($content['success']);

        /** @var Tag $tag */
        $tag = $repo->findOneBy(['id' => $content['tag']['id']]);
        $this->assertEquals('fr Un label', $tag->getTagTranslationByLocale('fr')->getLabel());
    }
}