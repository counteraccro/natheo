<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test controller Page
 */

namespace App\Tests\Controller\Admin\Content;

use App\Entity\Admin\Content\Page\Page;
use App\Tests\AppWebTestCase;

class PageControllerTest extends AppWebTestCase
{
    /**
     * Test méthode index()
     * @return void
     */
    public function testIndex(): void
    {
        $this->checkNoAccess('admin_page_index');

        $user = $this->createUserContributeur();

        $this->client->loginUser($user, 'admin');
        $this->client->request('GET', $this->router->generate('admin_page_index'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $this->translator->trans('page.index.page_title_h1', domain: 'page'));
    }

    /**
     * Test méthode updateDisabled()
     * @return void
     */
    public function testUpdateDisabled(): void
    {
        $page = $this->createPage(customData: ['disabled' => false]);
        foreach ($this->locales as $locale) {
            $this->createPageTranslation($page, ['locale' => $locale]);
        }
        $pageTranslation = $page->getPageTranslationByLocale('fr');

        $this->checkNoAccess('admin_page_update_disabled', ['id' => $page->getId()], 'PUT');
        $user = $this->createUserContributeur();

        $this->client->loginUser($user, 'admin');
        $this->client->request('PUT', $this->router->generate('admin_page_update_disabled', ['id' => $page->getId()]));

        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('success', $content);
        $this->assertTrue($content['success']);
        $this->assertArrayHasKey('msg', $content);
        $this->assertStringContainsString($pageTranslation->getTitre(), $content['msg']);

        $pageRepository = $this->em->getRepository(Page::class);
        /** @var Page $result */
        $result = $pageRepository->findOneBy(['id' => $page->getId()]);
        $this->assertEquals($result->isDisabled(), !$page->isDisabled());
    }

    /**
     * Test méthode delete()
     * @return void
     */
    public function testDelete(): void
    {
        $page = $this->createPageAllDataDefault();
        $pageTranslation = $page->getPageTranslationByLocale('fr');

        $this->checkNoAccess('admin_page_delete', ['id' => $page->getId()], 'DELETE');
        $user = $this->createUserContributeur();

        $this->client->loginUser($user, 'admin');
        $this->client->request('DELETE', $this->router->generate('admin_page_delete', ['id' => $page->getId()]));

        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('success', $content);
        $this->assertTrue($content['success']);
        $this->assertArrayHasKey('msg', $content);
        $this->assertStringContainsString($pageTranslation->getTitre(), $content['msg']);

        $pageRepository = $this->em->getRepository(Page::class);
        /** @var Page $result */
        $result = $pageRepository->findOneBy(['id' => $page->getId()]);
        $this->assertNull($result);
    }

    /**
     * Test méthode switchLandingPage()
     * @return void
     */
    public function testSwitchLandingPage(): void
    {
        $page1 = $this->createPage();
        foreach ($this->locales as $locale) {
            $this->createPageTranslation($page1, ['locale' => $locale]);
        }
        $this->createPage();
        $this->createPage();
        $this->createPage(customData: ['landingPage' => true]);

        $pageTranslation = $page1->getPageTranslationByLocale('fr');

        $this->checkNoAccess('admin_page_switch_Landing_page', ['id' => $page1->getId()], 'PUT');
        $user = $this->createUserContributeur();

        $this->client->loginUser($user, 'admin');
        $this->client->request('PUT', $this->router->generate('admin_page_switch_Landing_page', ['id' => $page1->getId()]));

        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('success', $content);
        $this->assertTrue($content['success']);
        $this->assertArrayHasKey('msg', $content);
        $this->assertStringContainsString($pageTranslation->getTitre(), $content['msg']);

        $pageRepository = $this->em->getRepository(Page::class);
        $result = $pageRepository->findAll();
        foreach($result as $page) {
            /** @var Page $page */
            if($page->getId() == $page1->getId()) {
                $this->assertTrue($page->isLandingPage());
            }
            else {
                $this->assertFalse($page->isLandingPage());
            }
        }
    }

    /**
     * Test méthode add()
     * @return void
     */
    public function testAdd() :void {
        $page = $this->createFaqAllDataDefault();

        $this->checkNoAccess('admin_page_add');
        $this->checkNoAccess('admin_page_update', ['id' => $page->getId()]);

        $user = $this->createUserContributeur();

        $this->client->loginUser($user, 'admin');
        $this->client->request('GET', $this->router->generate('admin_page_add'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $this->translator->trans('page.add.page_title_h1', domain: 'page'));

        $this->client->request('GET', $this->router->generate('admin_page_update', ['id' => $page->getId()]));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $this->translator->trans('page.update.page_title_h1', domain: 'page'));
    }

    /**
     * Test méthode loadTabContent()
     * @return void
     */
    public function testLoadTabContent() :void {

    }
}