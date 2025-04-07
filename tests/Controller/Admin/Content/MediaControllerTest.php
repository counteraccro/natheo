<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 *
 */

namespace Controller\Admin\Content;

use App\Entity\Admin\Content\Media\Media;
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
    public function testIndex(): void
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
    public function testLoadMedias(): void
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
    public function testLoadFolder(): void
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
    public function testUpdateFolder(): void
    {
        $this->mediaService->resetAllMedia();

        $this->checkNoAccess('admin_media_save_folder', methode: 'POST');
        $user = $this->createUserContributeur();
        $this->client->loginUser($user, 'admin');

        $folder = $this->createMediaFolder();
        $this->mediaService->createFolder($folder);
        $subFolder = $this->createMediaFolder($folder);
        $this->mediaService->createFolder($subFolder);
        $media = $this->createMedia($subFolder, customData: ['name' => 'road.jpg', 'path' => $subFolder->getPath() . DIRECTORY_SEPARATOR . $subFolder->getName()]);
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

        $path = $this->mediaService->getRootPathMedia() . $verif->getPath() . $verif->getName() . DIRECTORY_SEPARATOR . $verif2->getName();
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
    public function testLoadInfo(): void
    {
        $this->mediaService->resetAllMedia();

        $this->checkNoAccess('admin_media_load_info');
        $user = $this->createUserContributeur();
        $this->client->loginUser($user, 'admin');

        $folder = $this->createMediaFolder();
        $this->mediaService->createFolder($folder);
        $media = $this->createMedia($folder, customData: ['name' => 'road.jpg', 'path' => $folder->getPath() . DIRECTORY_SEPARATOR . $folder->getName()]);
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

    /**
     * Test méthode loadMedia()
     * @return void
     */
    public function testLoadMedia(): void
    {
        $this->checkNoAccess('admin_media_load_media_edit');
        $user = $this->createUserContributeur();
        $this->client->loginUser($user, 'admin');

        $media = $this->createMedia();
        $this->client->request('GET', $this->router->generate('admin_media_load_media_edit', ['id' => $media->getId()]));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);

        $this->assertIsArray($content);
        $this->assertArrayHasKey('media', $content);
        $this->assertArrayHasKey('id', $content['media']);
        $this->assertArrayHasKey('name', $content['media']);
        $this->assertArrayHasKey('description', $content['media']);
        $this->assertArrayHasKey('thumbnail', $content['media']);
        $this->assertEquals($media->getId(), $content['media']['id']);
        $this->assertEquals($media->getTitle(), $content['media']['name']);
        $this->assertEquals($media->getDescription(), $content['media']['description']);
    }

    /**
     * Test méthode saveMedia()
     * @return void
     */
    public function testSaveMedia(): void
    {

        $this->checkNoAccess('admin_media_save_media_edit', methode: 'POST');
        $user = $this->createUserContributeur();
        $this->client->loginUser($user, 'admin');

        $media = $this->createMedia();
        $this->client->request('GET', $this->router->generate('admin_media_load_media_edit', ['id' => $media->getId()]));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);

        $content['media']['name'] = 'title-edit';
        $content['media']['description'] = 'description-edit';

        $this->client->request('POST', $this->router->generate('admin_media_save_media_edit'), content: json_encode($content));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('success', $content);
        $this->assertIsBool($content['success']);

        $this->em->clear();
        $verif = $this->mediaService->findOneById(Media::class, $media->getId());
        $this->assertNotNull($verif);
        $this->assertEquals('title-edit', $verif->getTitle());
        $this->assertEquals('description-edit', $verif->getDescription());
    }

    /**
     * Test méthode listeFolderToMove()
     * @return void
     */
    public function testListeFolderToMove(): void
    {
        $folder1 = $this->createMediaFolder(customData: ['name' => 'folder1']);
        $subFolder1 = $this->createMediaFolder($folder1, customData: ['name' => 'subfolder1']);
        $media = $this->createMedia($subFolder1);
        $this->createMediaFolder($subFolder1, customData: ['name' => 'subsubfolder1']);
        $folder2 = $this->createMediaFolder(customData: ['name' => 'folder2']);
        $this->createMediaFolder($folder2, customData: ['name' => 'subfolder2']);
        $this->createMediaFolder(customData: ['name' => 'folder3']);

        $this->checkNoAccess('admin_media_liste_move');
        $user = $this->createUserContributeur();
        $this->client->loginUser($user, 'admin');

        $this->client->request('GET', $this->router->generate('admin_media_liste_move', ['id' => $media->getId(), 'type' => 'media']));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);

        $this->assertArrayHasKey('dataMove', $content);
        $this->assertArrayHasKey('id', $content['dataMove']);
        $this->assertEquals($media->getId(), $content['dataMove']['id']);
        $this->assertArrayHasKey('parentIid', $content['dataMove']);
        $this->assertEquals($media->getMediaFolder()->getParent()->getId(), $content['dataMove']['parentIid']);
        $this->assertArrayHasKey('label', $content['dataMove']);
        $this->assertArrayHasKey('type', $content['dataMove']);
        $this->assertEquals('media', $content['dataMove']['type']);
        $this->assertArrayHasKey('listeFolder', $content['dataMove']);

        $check = true;
        foreach ($content['dataMove']['listeFolder'] as $folder) {
            if ($folder['name'] === $media->getMediaFolder()->getName()) {
                $check = false;
            }
        }
        $this->assertTrue($check);

        $this->client->request('GET', $this->router->generate('admin_media_liste_move', ['id' => $folder2->getId(), 'type' => 'folder']));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);

        $check = true;
        foreach ($content['dataMove']['listeFolder'] as $folder) {
            if ($folder['name'] === $folder2->getName()) {
                $check = false;
            }

            foreach ($folder2->getChildren() as $child) {
                if ($child->getName() === $folder['name']) {
                    $check = false;
                }
            }
        }
        $this->assertTrue($check);
    }

    /**
     * Test méthode move()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testMove(): void
    {
        $this->checkNoAccess('admin_media_move', methode: 'POST');
        $user = $this->createUserContributeur();
        $this->client->loginUser($user, 'admin');

        $this->mediaService->resetAllMedia();
        $mediaFolder = $this->createMediaFolder(customData: ['name' => 'start-folder']);
        $media = $this->createMedia($mediaFolder, customData: ['name' => 'road.jpg']);
        $this->mediaService->moveMediaFixture('road.jpg', $media);

        $mediaFolderEnd = $this->createMediaFolder(customData: ['name' => 'end-folder']);
        $this->mediaService->createFolder($mediaFolderEnd);

        $data = ['id' => $media->getId(), 'type' => 'media', 'idToMove' => $mediaFolderEnd->getId()];
        $this->client->request('POST', $this->router->generate('admin_media_move'), content: json_encode($data));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('success', $content);
        $this->assertIsBool($content['success']);

        $result = $this->fileSystem->exists($this->mediaService->getRootPathMedia() . DIRECTORY_SEPARATOR . 'end-folder' . DIRECTORY_SEPARATOR . 'road.jpg');
        $this->assertTrue($result);
    }

    /**
     * Test méthode updateTrash()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testUpdateTrash(): void
    {
        $this->checkNoAccess('admin_media_update_trash', methode: 'POST');

        $mediaFolder = $this->createMediaFolder(customData: ['trash' => false]);
        $media = $this->createMedia($mediaFolder, customData: ['name' => 'road.jpg', 'trash' => true]);

        $user = $this->createUserContributeur();
        $this->client->loginUser($user, 'admin');

        $data = ['type' => 'media', 'id' => $media->getId(), 'trash' => !$media->isTrash()];
        $this->client->request('POST', $this->router->generate('admin_media_update_trash'), content: json_encode($data));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('success', $content);
        $this->assertIsBool($content['success']);

        $this->em->clear();
        $verif = $this->mediaService->findOneById(Media::class, $media->getId());
        $this->assertEquals(!$media->isTrash(), $verif->isTrash());

    }

    /**
     * Test méthode nbTrash()
     * @return void
     */
    public function testNbTrash(): void
    {
        $folder = $this->createMediaFolder(customData: ['disabled' => false, 'trash' => true]);
        $folder2 = $this->createMediaFolder(customData: ['disabled' => false, 'trash' => false]);
        $this->createMedia($folder, customData: ['disabled' => false, 'trash' => true]);
        $this->createMedia($folder2, customData: ['disabled' => false, 'trash' => true]);

        $this->checkNoAccess('admin_media_nb_trash');
        $user = $this->createUserContributeur();
        $this->client->loginUser($user, 'admin');

        $this->client->request('GET', $this->router->generate('admin_media_nb_trash'));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('nb', $content);
        $this->assertEquals(3, $content['nb']);
    }

    /**
     * Test méthode listTrash()
     * @return void
     */
    public function testListTrash(): void {
        $folder = $this->createMediaFolder(customData: ['disabled' => false, 'trash' => true]);
        $folder2 = $this->createMediaFolder(customData: ['disabled' => false, 'trash' => false]);
        $this->createMedia($folder, customData: ['disabled' => false, 'trash' => true]);
        $this->createMedia($folder2, customData: ['disabled' => false, 'trash' => true]);

        $this->checkNoAccess('admin_media_list_trash');
        $user = $this->createUserContributeur();
        $this->client->loginUser($user, 'admin');

        $this->client->request('GET', $this->router->generate('admin_media_list_trash'));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('mediasTrash', $content);
        $this->assertCount(3, $content['mediasTrash']);
    }

    /**
     * Test méthode removeTrash()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testRemoveTrash() :void {

        $this->mediaService->resetAllMedia();

        $this->checkNoAccess('admin_media_remove', methode: 'POST');
        $user = $this->createUserContributeur();
        $this->client->loginUser($user, 'admin');

        $mediaFolder = $this->createMediaFolder(customData: ['trash' => true]);
        $media = $this->createMedia($mediaFolder, customData: ['name' => 'road.jpg', 'trash' => true]);
        $this->mediaService->moveMediaFixture('road.jpg', $media);

        $data = ['type' => 'folder', 'id' => $mediaFolder->getId()];
        $this->client->request('POST', $this->router->generate('admin_media_remove'), content: json_encode($data));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('success', $content);
        $this->assertEquals('remove', $content['success']);

        $this->assertFalse($this->fileSystem->exists($this->mediaService->getRootPathMedia() . $mediaFolder->getPath() . $mediaFolder->getName()));
    }
}