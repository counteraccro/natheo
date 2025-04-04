<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 *
 */

namespace Controller\Admin\Content;

use App\Entity\Admin\Content\Media\MediaFolder;
use App\Service\Admin\Content\Media\MediaService;
use App\Tests\AppWebTestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;

class MediaControllerTest extends AppWebTestCase
{
    /**
     * @var MediaService
     */
    private MediaService $mediaService;

    /**
     * @var FileSystem
     */
    private Filesystem $fileSystem;


    public function setUp(): void
    {
        parent::setUp();
        $this->mediaService = $this->container->get(MediaService::class);
        $this->fileSystem = new FileSystem();
    }

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
        $this->checkNoAccess('admin_media_load_medias');
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

    /**
     * test méthode loadFolder()
     * @return void
     */
    public function testLoadFolder() :void
    {
        $this->checkNoAccess('admin_media_load_folder');
        $user = $this->createUserContributeur();
        $this->client->loginUser($user, 'admin');

        $folder = $this->createMediaFolder();
        $media = $this->createMedia($folder, customData: ['trash' => false]);
        $media2 = $this->createMedia($folder, customData: ['trash' => false]);

        $this->client->request('GET', $this->router->generate('admin_media_load_folder', ['id' => $folder->getId()]));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('folder', $content);
        $this->assertArrayHasKey('id', $content['folder']);
        $this->assertEquals($folder->getId(), $content['folder']['id']);
        $this->assertArrayHasKey('name', $content['folder']);
        $this->assertEquals($folder->getName(), $content['folder']['name']);
        $this->assertArrayHasKey('disabled', $content['folder']);
        $this->assertEquals($folder->isDisabled(), $content['folder']['disabled']);
        $this->assertArrayHasKey('createdAt', $content['folder']);
        $this->assertArrayHasKey('updateAt', $content['folder']);
        $this->assertArrayHasKey('trash', $content['folder']);
        $this->assertEquals($folder->isTrash(), $content['folder']['trash']);
        $this->assertArrayHasKey('path', $content['folder']);
        $this->assertEquals($folder->getPath(), $content['folder']['path']);

        $this->client->request('GET', $this->router->generate('admin_media_load_folder', ['id' => $folder->getId(), 'action' => 'see']));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('medias', $content['folder']);
    }

    /**
     * Test méthode updateFolder()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testUpdateFolder() :void
    {
        $this->mediaService->resetAllMedia();

        $this->checkNoAccess('admin_media_save_folder', methode: 'POST');
        $user = $this->createUserContributeur();
        $this->client->loginUser($user, 'admin');

        $folder = $this->createMediaFolder();
        $this->mediaService->createFolder($folder);
        $subFolder = $this->createMediaFolder($folder);
        $this->mediaService->createFolder($subFolder);
        $media = $this->createMedia($subFolder, customData : ['name' => 'road.jpg', 'path' => $subFolder->getPath() . DIRECTORY_SEPARATOR . $subFolder->getName()]);
        $this->mediaService->moveMediaFixture('road.jpg', $media);

        $data = ['name' => "unitTest", 'currentFolder' => 0, 'editFolder' => 0];
        $this->client->request('POST', $this->router->generate('admin_media_save_folder'), content: json_encode($data));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('result', $content);
        $this->assertEquals('success', $content['result']);

        /** @var MediaFolder $verif */
        $verif = $this->mediaService->findOneBy(MediaFolder::class, 'name', 'unitTest');
        $this->assertNotNull($verif);
        $this->assertTrue($this->fileSystem->exists($this->mediaService->getRootPathMedia() . $verif->getPath() . $verif->getName()));


        $data = ['name' => "editUnitTest", 'currentFolder' => 0, 'editFolder' => $folder->getId()];
        $this->client->request('POST', $this->router->generate('admin_media_save_folder'), content: json_encode($data));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('result', $content);
        $this->assertEquals('success', $content['result']);

        $this->em->clear();
        /** @var MediaFolder $verif */
        $verif = $this->mediaService->findOneById(MediaFolder::class, $folder->getId());
        $this->assertNotNull($verif);
        $this->assertTrue($this->fileSystem->exists($this->mediaService->getRootPathMedia() . $verif->getPath() . $verif->getName()));


        $data = ['name' => "subEditUnitTest", 'currentFolder' => $folder->getId(), 'editFolder' => $subFolder->getId()];
        $this->client->request('POST', $this->router->generate('admin_media_save_folder'), content: json_encode($data));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('result', $content);
        $this->assertEquals('success', $content['result']);

        $this->em->clear();
        /** @var MediaFolder $verif2 */
        $verif2 = $this->mediaService->findOneById(MediaFolder::class, $subFolder->getId());
        $this->assertNotNull($verif);
        $this->assertEquals($verif->getPath() . $verif->getName(), $verif2->getPath());
        $this->assertTrue($this->fileSystem->exists($this->mediaService->getRootPathMedia() . $verif->getPath() . DIRECTORY_SEPARATOR . $verif->getName()));

        $path =  $this->mediaService->getRootPathMedia() . $verif->getPath() . $verif->getName() . DIRECTORY_SEPARATOR . $verif2->getName();
        $media = $verif2->getMedias()->first();
        $this->assertNotNull($media);
        $this->assertEquals($this->mediaService->getRootPathMedia() . $media->getPath(), $path);
    }

    /**
     * Test méthode loadInfo()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testLoadInfo() :void {
        $this->mediaService->resetAllMedia();

        $this->checkNoAccess('admin_media_load_info', methode: 'GET');
        $user = $this->createUserContributeur();
        $this->client->loginUser($user, 'admin');

        $folder = $this->createMediaFolder();
        $this->mediaService->createFolder($folder);
        $media = $this->createMedia($folder, customData : ['name' => 'road.jpg', 'path' => $folder->getPath() . DIRECTORY_SEPARATOR . $folder->getName()]);
        $this->mediaService->moveMediaFixture('road.jpg', $media);

        $this->client->request('GET', $this->router->generate('admin_media_load_info', ['id' => $folder->getId(), 'type' => 'folder']));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('data', $content);
        $this->assertArrayHasKey('Nom', $content['data']);
        $this->assertEquals($folder->getName(), $content['data']['Nom']);
        $this->assertArrayHasKey('Emplacement', $content['data']);
        $this->assertArrayHasKey('Taille (disque)', $content['data']);
        $this->assertArrayHasKey('Contenu', $content['data']);
        $this->assertStringContainsString('1', $content['data']['Contenu']);
        $this->assertArrayHasKey('Créer le', $content['data']);
        $this->assertArrayHasKey('Dernière modification le', $content['data']);

        $this->client->request('GET', $this->router->generate('admin_media_load_info', ['id' => $media->getId(), 'type' => 'media']));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('data', $content);
        $this->assertArrayHasKey('Nom', $content['data']);
        $this->assertEquals($media->getName(), $content['data']['Nom']);
        $this->assertArrayHasKey('Titre', $content['data']);
        $this->assertEquals($media->getTitle(), $content['data']['Titre']);
        $this->assertArrayHasKey('Description', $content['data']);
        $this->assertEquals($media->getDescription(), $content['data']['Description']);
        $this->assertArrayHasKey('Extension', $content['data']);
        $this->assertEquals($media->getExtension(), $content['data']['Extension']);
        $this->assertArrayHasKey('Utilisateur', $content['data']);
        $this->assertEquals($media->getUser()->getEmail(), $content['data']['Utilisateur']);
        $this->assertArrayHasKey('Emplacement', $content['data']);
        $this->assertArrayHasKey('Taille (disque)', $content['data']);
        $this->assertArrayHasKey('Créer le', $content['data']);
        $this->assertArrayHasKey('Dernière modification le', $content['data']);
        $this->assertArrayHasKey('thumbnail', $content);
        $this->assertArrayHasKey('web_path', $content);
        $this->assertArrayHasKey('media_type', $content);
        $this->assertArrayHasKey('type', $content);
    }
}