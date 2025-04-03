<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 *
 */

namespace Controller\Admin\Content;

use App\Tests\AppWebTestCase;

class MediaControllerTest extends AppWebTestCase
{
    /**
     * Test méthode index()
     * @return void
     */
    public function testIndex() :void
    {
        $this->checkNoAccess('admin_media_index');
        $user = $this->createUserContributeur();
        $this->client->loginUser($user, 'admin');

        $this->client->request('GET', $this->router->generate('admin_media_index'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $this->translator->trans('media.index.page_title_h1', domain: 'media'));
    }

    /**
     * Test méthode  loadMedias
     * @return void
     */
    public function testLoadMedias() :void
    {
        $this->checkNoAccess('admin_media_index');
        $user = $this->createUserContributeur();
        $this->client->loginUser($user, 'admin');

        $folder = $this->createMediaFolder();
        $media = $this->createMedia($folder, customData: ['trash' => false]);
        $media2 = $this->createMedia($folder, customData: ['trash' => false]);

        $this->client->request('GET', $this->router->generate('admin_media_load_medias', ['folder' => $folder->getId(), 'order' => 'desc', 'filter' => 'name']));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('medias', $content);
        $this->assertArrayHasKey('currentFolder', $content);
        $this->assertArrayHasKey('root', $content['currentFolder']);
        $this->assertArrayHasKey('size', $content['currentFolder']);
        $this->assertArrayHasKey('path', $content['currentFolder']);
        $this->assertArrayHasKey('id', $content['currentFolder']);
        $this->assertArrayHasKey('canDelete', $content);
        $this->assertArrayHasKey('url', $content);
        $this->assertArrayHasKey('loadFolder', $content['url']);
        $this->assertArrayHasKey('loadInfo', $content['url']);
        $this->assertArrayHasKey('upload', $content['url']);
        $this->assertArrayHasKey('loadMediaEdit', $content['url']);
        $this->assertArrayHasKey('saveMediaEdit', $content['url']);
        $this->assertArrayHasKey('listeMove', $content['url']);
        $this->assertArrayHasKey('move', $content['url']);
        $this->assertArrayHasKey('updateTrash', $content['url']);
        $this->assertArrayHasKey('nbTrash', $content['url']);
        $this->assertArrayHasKey('listTrash', $content['url']);
        $this->assertArrayHasKey('remove', $content['url']);
        $this->assertCount(2, $content['medias']);
    }
}