<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Teste TranslationControllerTest
 */

namespace App\Tests\Controller\Admin\System;

use App\Tests\AppWebTestCase;

class TranslationControllerTest extends AppWebTestCase
{
    /**
     * Test méthode index()
     * @return void
     */
    public function testIndex(): void
    {
        $this->checkNoAccess('admin_translation_index');

        $user = $this->createUserSuperAdmin();

        $this->client->loginUser($user, 'admin');
        $this->client->request('GET', $this->router->generate('admin_translation_index'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $this->translator->trans('translate.page_title_h1', domain: 'translate'));
    }

    /**
     * Test méthode loadLanguages()
     * @return void
     */
    public function testLoadLanguages(): void
    {
        $this->checkNoAccess('admin_translation_list_languages');

        $user = $this->createUserSuperAdmin();

        $this->client->loginUser($user, 'admin');
        $this->client->request('GET', $this->router->generate('admin_translation_list_languages'));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('trans', $content);
        $this->assertArrayHasKey('languages', $content);
        $this->assertIsArray($content['trans']);
        $this->assertIsArray($content['languages']);
    }

    /**
     * Test méthode loadFilesTranslates()
     * @return void
     */
    public function testLoadFilesTranslates(): void
    {
        $this->checkNoAccess('admin_translation_files_translate');

        $user = $this->createUserSuperAdmin();

        $this->client->loginUser($user, 'admin');
        $this->client->request('GET', $this->router->generate('admin_translation_files_translate'));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);
        $this->assertNotEmpty($content);
        $this->assertArrayHasKey('files', $content);

        $this->client->request('GET', $this->router->generate('admin_translation_files_translate', ['language' => 'es']));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);
        $this->assertNotEmpty($content);
        $this->assertArrayHasKey('files', $content);

        $this->client->request('GET', $this->router->generate('admin_translation_files_translate', ['language' => 'en']));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);
        $this->assertNotEmpty($content);
        $this->assertArrayHasKey('files', $content);
    }

    /**
     * Test méthode loadFileTranslate()
     * @return void
     */
    public function testLoadFileTranslate(): void
    {

        $path = $this->createTranslateFile();
        $tmp = explode(DIRECTORY_SEPARATOR, $path);
        $fileName = $tmp[array_key_last($tmp)];

        $this->checkNoAccess('admin_translation_file_translate');

        $user = $this->createUserSuperAdmin();

        $this->client->loginUser($user, 'admin');
        $this->client->request('GET', $this->router->generate('admin_translation_file_translate', ['file' => $fileName]));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);
        $this->assertNotEmpty($content);
        $this->assertArrayHasKey('file', $content);
        $this->assertNotEmpty($content['file']);

        $this->removeTranslateFile($path);

    }

    /**
     * Test méthode saveTranslate()
     * @return void
     */
    public function testSaveTranslate(): void
    {
        $data = [
            'key' => 'value',
            'key.2' => 'value 2',
        ];

        $path = $this->createTranslateFile(customData: $data);
        $tmp = explode(DIRECTORY_SEPARATOR, $path);
        $fileName = $tmp[array_key_last($tmp)];

        $this->checkNoAccess('admin_translation_save_translate', methode: 'PUT');

        $data = [
            "file" => $fileName,
            "translates" => [
                [
                    "key" => "key",
                    "value" => "value edit"
                ]
            ]
        ];


        $user = $this->createUserSuperAdmin();

        $this->client->loginUser($user, 'admin');
        $this->client->request('PUT', $this->router->generate('admin_translation_save_translate'), content: json_encode($data));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);
        $this->assertNotEmpty($content);
        $this->assertArrayHasKey('success', $content);
        $this->assertTrue($content['success']);
        $this->assertArrayHasKey('msg', $content);

        $this->client->request('GET', $this->router->generate('admin_translation_file_translate', ['file' => $fileName]));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);
        $this->assertNotEmpty($content);
        $this->assertArrayHasKey('file', $content);
        $this->assertNotEmpty($content['file']);
        $this->assertEquals('value edit', $content['file']['key']);
        $this->removeTranslateFile($path);
    }
}