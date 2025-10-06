<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test controller MarkdownController
 */

namespace App\Tests\Controller\Admin\Global;

use App\Controller\Admin\Global\MarkdownController;
use App\Tests\AppWebTestCase;

class MarkdownControllerTest extends AppWebTestCase
{
    /**
     * Test méthode loadDatas
     * @return void
     */
    public function testLoadDatas(): void
    {
        $this->checkNoAccess('admin_markdown_load-datas');

        $userContributeur = $this->createUserContributeur();
        $this->client->loginUser($userContributeur, 'admin');
        $this->client->request('GET', $this->router->generate('admin_markdown_load-datas'));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertCount(4, $content);
        $this->assertArrayHasKey('media', $content);
        $this->assertArrayHasKey('internalLinks', $content);
        $this->assertArrayHasKey('preview', $content);
        $this->assertArrayHasKey('initPreview', $content);
    }

    /**
     * Test méthode setPreviewSession()
     * @return void
     */
    public function testSetPreviewSession(): void
    {
        $this->checkNoAccess('admin_markdown_init_preview', methode: 'POST');

        $userContributeur = $this->createUserContributeur();
        $this->client->loginUser($userContributeur, 'admin');

        $data = ['value' => 'Je suis un **text**'];
        $this->client->request(
            'POST',
            $this->router->generate('admin_markdown_init_preview'),
            content: json_encode($data),
        );
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertTrue($content['success']);

        $session = $this->client->getRequest()->getSession();
        $this->assertEquals($data['value'], $session->get('natheo-markdown-preview'));
    }

    /**
     * Test méthode preview()
     * @return void
     */
    public function testPreview(): void
    {
        $this->checkNoAccess('admin_markdown_preview');

        $userContributeur = $this->createUserContributeur();
        $this->client->loginUser($userContributeur, 'admin');

        $data = ['value' => 'Je suis un **text**'];
        $this->client->request(
            'POST',
            $this->router->generate('admin_markdown_init_preview'),
            content: json_encode($data),
        );
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertTrue($content['success']);

        $session = $this->client->getRequest()->getSession();
        $this->assertEquals($data['value'], $session->get('natheo-markdown-preview'));

        $this->client->request('GET', $this->router->generate('admin_markdown_preview'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains(
            'legend',
            $this->translator->trans('preview.preview', domain: 'editor_markdown'),
        );
    }
}
