<?php

declare(strict_types=1);
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
use App\Utils\Content\Media\MediaConst;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;

class MediaControllerTest extends AppWebTestCase
{
    const IMG_UNIT_TEST = 'road.jpg';

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
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testLoadMedias(): void
    {
        $this->mediaService->resetAllMedia();

        $this->checkNoAccess('admin_media_load_medias');
        $user = $this->createUserContributeur();
        $this->client->loginUser($user, 'admin');

        $folder = $this->createMediaFolder();
        $this->mediaService->createFolder($folder);
        $media = $this->createMedia($folder, customData: ['trash' => false]);

        $this->mediaService->moveMediaFixture(
            self::IMG_UNIT_TEST,
            $media,
            $media->getName() . '.' . $media->getExtension(),
        );

        $media2 = $this->createMedia($folder, customData: ['trash' => false]);

        $this->mediaService->moveMediaFixture(
            self::IMG_UNIT_TEST,
            $media2,
            $media2->getName() . '.' . $media2->getExtension(),
        );

        $this->client->request(
            'GET',
            $this->router->generate('admin_media_load_medias', [
                'folder' => $folder->getId(),
                'order' => 'desc',
                'filter' => 'name',
            ]),
        );
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
        $this->assertArrayHasKey('upload', $content['url']);
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
        $media = $this->createMedia(
            $subFolder,
            customData: [
                'name' => 'road.jpg',
                'path' => $subFolder->getPath() . DIRECTORY_SEPARATOR . $subFolder->getName(),
            ],
        );
        $this->mediaService->moveMediaFixture('road.jpg', $media);

        $data = ['name' => 'unitTest', 'currentFolder' => 0, 'editFolder' => 0];
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
        $this->assertTrue(
            $this->fileSystem->exists($this->mediaService->getRootPathMedia() . $verif->getPath() . $verif->getName()),
        );

        $data = ['name' => 'editUnitTest', 'currentFolder' => 0, 'editFolder' => $folder->getId()];
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
        $this->assertTrue(
            $this->fileSystem->exists($this->mediaService->getRootPathMedia() . $verif->getPath() . $verif->getName()),
        );

        $data = ['name' => 'subEditUnitTest', 'currentFolder' => $folder->getId(), 'editFolder' => $subFolder->getId()];
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
        $this->assertTrue(
            $this->fileSystem->exists(
                $this->mediaService->getRootPathMedia() . $verif->getPath() . DIRECTORY_SEPARATOR . $verif->getName(),
            ),
        );

        $path =
            $this->mediaService->getRootPathMedia() .
            $verif->getPath() .
            $verif->getName() .
            DIRECTORY_SEPARATOR .
            $verif2->getName();
        $media = $verif2->getMedias()->first();
        $this->assertNotNull($media);
        $this->assertEquals($this->mediaService->getRootPathMedia() . $media->getPath(), $path);
    }

    /**
     * Test méthode updateFolder() : l'unicité du nom est scopée au dossier parent (deux
     * dossiers de même nom dans deux branches différentes sont autorisés), et le dossier en
     * cours d'édition est exclu du contrôle (un ré-enregistrement sans changer le nom ne doit
     * pas être rejeté comme "nom déjà existant")
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testUpdateFolderNameUniquenessScopedToParent(): void
    {
        $this->mediaService->resetAllMedia();

        $user = $this->createUserContributeur();
        $this->client->loginUser($user, 'admin');

        $branchA = $this->createMediaFolder(customData: ['name' => 'branch-a']);
        $this->mediaService->createFolder($branchA);
        $branchB = $this->createMediaFolder(customData: ['name' => 'branch-b']);
        $this->mediaService->createFolder($branchB);
        $photosInA = $this->createMediaFolder($branchA, customData: ['name' => 'photos']);
        $this->mediaService->createFolder($photosInA);

        // Même nom "photos", mais sous un parent différent : doit être accepté.
        $data = ['name' => 'photos', 'currentFolder' => $branchB->getId(), 'editFolder' => 0];
        $this->client->request('POST', $this->router->generate('admin_media_save_folder'), content: json_encode($data));
        $this->assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('success', $content['result']);

        // Ré-enregistrement de "photos" (branchA) sans changer le nom : ne doit pas être rejeté.
        $data = ['name' => 'photos', 'currentFolder' => $branchA->getId(), 'editFolder' => $photosInA->getId()];
        $this->client->request('POST', $this->router->generate('admin_media_save_folder'), content: json_encode($data));
        $this->assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('success', $content['result']);

        // Même nom, même parent (branchA) : toujours rejeté.
        $data = ['name' => 'photos', 'currentFolder' => $branchA->getId(), 'editFolder' => 0];
        $this->client->request('POST', $this->router->generate('admin_media_save_folder'), content: json_encode($data));
        $this->assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('error', $content['result']);
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
        $content = ['media' => ['id' => $media->getId()]];

        $content['media']['name'] = 'title-edit';
        $content['media']['description'] = 'description-edit';

        $this->client->request(
            'POST',
            $this->router->generate('admin_media_save_media_edit'),
            content: json_encode($content),
        );
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

        $this->client->request(
            'GET',
            $this->router->generate('admin_media_liste_move', ['id' => $media->getId(), 'type' => 'media']),
        );
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

        $this->client->request(
            'GET',
            $this->router->generate('admin_media_liste_move', ['id' => $folder2->getId(), 'type' => 'folder']),
        );
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

        $result = $this->fileSystem->exists(
            $this->mediaService->getRootPathMedia() .
                DIRECTORY_SEPARATOR .
                'end-folder' .
                DIRECTORY_SEPARATOR .
                'road.jpg',
        );
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
        $this->client->request(
            'POST',
            $this->router->generate('admin_media_update_trash'),
            content: json_encode($data),
        );
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
    public function testListTrash(): void
    {
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
    public function testRemoveTrash(): void
    {
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

        $this->assertFalse(
            $this->fileSystem->exists(
                $this->mediaService->getRootPathMedia() . $mediaFolder->getPath() . $mediaFolder->getName(),
            ),
        );
    }

    /**
     * Test que la route removeTrash() refuse de supprimer définitivement un média qui n'est pas
     * passé par la corbeille, même appelée directement avec un id valide (bypass de l'UI).
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testRemoveTrashRefusesWhenNotTrashed(): void
    {
        $this->mediaService->resetAllMedia();

        $user = $this->createUserContributeur();
        $this->client->loginUser($user, 'admin');
        $this->client->catchExceptions(false);

        $media = $this->createMedia(customData: ['name' => 'road.jpg', 'trash' => false]);
        $this->mediaService->moveMediaFixture('road.jpg', $media);

        $idMedia = $media->getId();

        $data = ['type' => 'media', 'id' => $idMedia];
        $this->expectException(\RuntimeException::class);
        $this->client->request('POST', $this->router->generate('admin_media_remove'), content: json_encode($data));
    }

    /**
     * Construit le payload attendu par la route upload-media (FileData côté front) à partir
     * d'un fichier réel des fixtures (public/assets/fixtures/)
     * @param string $fixtureFileName
     * @param string $mimeType
     * @param string $extension
     * @param string $name
     * @return array
     */
    private function buildUploadFile(
        string $fixtureFileName,
        string $mimeType,
        string $extension,
        string $name = 'unit-test-upload',
    ): array {
        $fixturesPath = dirname($this->mediaService->getRootPathMedia()) . DIRECTORY_SEPARATOR . 'fixtures';
        $content = file_get_contents($fixturesPath . DIRECTORY_SEPARATOR . $fixtureFileName);

        return [
            'name' => $name,
            'description' => 'unit test upload',
            'fileExtention' => $extension,
            'url' => 'data:' . $mimeType . ';base64,' . base64_encode($content),
        ];
    }

    /**
     * Test méthode upload() : cas nominal (upload d'une image valide dans un dossier)
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testUpload(): void
    {
        $this->mediaService->resetAllMedia();

        $user = $this->createUserContributeur();
        $this->client->loginUser($user, 'admin');

        $mediaFolder = $this->createMediaFolder();
        $this->mediaService->createFolder($mediaFolder);

        $data = [
            'folder' => $mediaFolder->getId(),
            'file' => $this->buildUploadFile('road.jpg', 'image/jpeg', 'jpg', 'road-photo'),
        ];
        $this->client->request('POST', $this->router->generate('admin_media_upload'), content: json_encode($data));
        $this->assertResponseIsSuccessful();

        $medias = $this->mediaService->getMediaByMediaFolder($mediaFolder);
        $this->assertCount(1, $medias);
        $this->assertEquals('road-photo', $medias[0]->getTitle());
        $this->assertEquals('jpg', $medias[0]->getExtension());
        $this->assertTrue(
            $this->fileSystem->exists($this->mediaService->getRootPathMedia() . $medias[0]->getPath()),
        );
    }

    /**
     * Test méthode upload() : accepte aussi les documents bureautiques autorisés
     * (pas seulement les images)
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testUploadAllowsOfficeDocuments(): void
    {
        $this->mediaService->resetAllMedia();

        $user = $this->createUserContributeur();
        $this->client->loginUser($user, 'admin');

        $data = [
            'folder' => 0,
            'file' => $this->buildUploadFile('documentation-natheo.pdf', 'application/pdf', 'pdf'),
        ];
        $this->client->request('POST', $this->router->generate('admin_media_upload'), content: json_encode($data));
        $this->assertResponseIsSuccessful();

        $data = [
            'folder' => 0,
            'file' => $this->buildUploadFile('documentation-natheo.docx', 'application/octet-stream', 'docx'),
        ];
        $this->client->request('POST', $this->router->generate('admin_media_upload'), content: json_encode($data));
        $this->assertResponseIsSuccessful();

        $medias = $this->mediaService->getMediaByMediaFolder(null);
        $this->assertCount(2, $medias);
        $extensions = array_map(fn($media) => $media->getExtension(), $medias);
        $this->assertContains('pdf', $extensions);
        $this->assertContains('docx', $extensions);
    }

    /**
     * Test méthode upload() : rejette une extension absente de la liste blanche
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testUploadRejectsDisallowedExtension(): void
    {
        $this->mediaService->resetAllMedia();

        $user = $this->createUserContributeur();
        $this->client->loginUser($user, 'admin');
        $this->client->catchExceptions(false);

        $data = ['folder' => 0, 'file' => $this->buildUploadFile('road.jpg', 'image/jpeg', 'exe')];

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('File extension not allowed.');
        $this->client->request('POST', $this->router->generate('admin_media_upload'), content: json_encode($data));
    }

    /**
     * Test méthode upload() : rejette un fichier dont le contenu réel ne correspond pas
     * à l'extension déclarée (extension autorisée mais MIME usurpé)
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testUploadRejectsMimeMismatch(): void
    {
        $this->mediaService->resetAllMedia();

        $user = $this->createUserContributeur();
        $this->client->loginUser($user, 'admin');
        $this->client->catchExceptions(false);

        $data = [
            'folder' => 0,
            'file' => [
                'name' => 'fake-image',
                'description' => 'unit test upload',
                'fileExtention' => 'jpg',
                'url' => 'data:image/jpeg;base64,' . base64_encode('<?php echo "not an image"; ?>'),
            ],
        ];

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('#^File type not allowed#');
        $this->client->request('POST', $this->router->generate('admin_media_upload'), content: json_encode($data));
    }

    /**
     * Test méthode upload() : rejette un payload dont la partie base64 est invalide
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testUploadRejectsInvalidBase64(): void
    {
        $this->mediaService->resetAllMedia();

        $user = $this->createUserContributeur();
        $this->client->loginUser($user, 'admin');
        $this->client->catchExceptions(false);

        $data = [
            'folder' => 0,
            'file' => [
                'name' => 'invalid-payload',
                'description' => 'unit test upload',
                'fileExtention' => 'jpg',
                'url' => 'data:image/jpeg;base64,not-valid-base64-!!!',
            ],
        ];

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid base64 data.');
        $this->client->request('POST', $this->router->generate('admin_media_upload'), content: json_encode($data));
    }

    /**
     * Test méthode upload() : une tentative de path traversal via le nom de fichier
     * (ex: "../../evil") est neutralisée (basename()) et le fichier reste dans le dossier attendu
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testUploadSanitizesPathTraversalInName(): void
    {
        $this->mediaService->resetAllMedia();

        $user = $this->createUserContributeur();
        $this->client->loginUser($user, 'admin');

        $file = $this->buildUploadFile('road.jpg', 'image/jpeg', 'jpg', 'road-photo');
        $file['name'] = '../../../../etc/evil';

        $data = ['folder' => 0, 'file' => $file];
        $this->client->request('POST', $this->router->generate('admin_media_upload'), content: json_encode($data));
        $this->assertResponseIsSuccessful();

        $medias = $this->mediaService->getMediaByMediaFolder(null);
        $this->assertCount(1, $medias);

        $realRoot = realpath($this->mediaService->getRootPathMedia());
        $realMediaPath = realpath($this->mediaService->getRootPathMedia() . $medias[0]->getPath());
        $this->assertNotFalse($realMediaPath);
        $this->assertStringStartsWith($realRoot . DIRECTORY_SEPARATOR, $realMediaPath);
        $this->assertStringNotContainsString('..', $medias[0]->getName());
    }

    /**
     * Test méthode upload() : rejette un fichier dont le contenu réel correspond à un AUTRE
     * type autorisé que celui déclaré par l'extension (ex: contenu jpeg réel, extension "pdf")
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testUploadRejectsCrossTypeMismatch(): void
    {
        $this->mediaService->resetAllMedia();

        $user = $this->createUserContributeur();
        $this->client->loginUser($user, 'admin');
        $this->client->catchExceptions(false);

        // road.jpg est un vrai jpeg, mais déclaré ici avec l'extension "pdf".
        $file = $this->buildUploadFile('road.jpg', 'image/jpeg', 'pdf');

        $data = ['folder' => 0, 'file' => $file];
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('#^File type not allowed#');
        $this->client->request('POST', $this->router->generate('admin_media_upload'), content: json_encode($data));
    }

    /**
     * Test méthode upload() : le fallback "application/zip" n'est accepté que pour les
     * extensions OOXML (docx/xlsx/pptx), jamais pour une extension sans rapport (ex: jpg)
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testUploadAcceptsGenericZipOnlyForOfficeExtensions(): void
    {
        $this->mediaService->resetAllMedia();

        $user = $this->createUserContributeur();
        $this->client->loginUser($user, 'admin');

        $tmpZip = tempnam(sys_get_temp_dir(), 'media-test-zip');
        $zip = new \ZipArchive();
        $zip->open($tmpZip, \ZipArchive::OVERWRITE);
        $zip->addFromString('dummy.txt', 'hello world');
        $zip->close();
        $zipContent = file_get_contents($tmpZip);
        unlink($tmpZip);

        $file = [
            'name' => 'archive',
            'description' => 'unit test upload',
            'fileExtention' => 'docx',
            'url' => 'data:application/zip;base64,' . base64_encode($zipContent),
        ];
        $data = ['folder' => 0, 'file' => $file];
        $this->client->request('POST', $this->router->generate('admin_media_upload'), content: json_encode($data));
        $this->assertResponseIsSuccessful();

        $this->client->catchExceptions(false);
        $file['fileExtention'] = 'jpg';
        $data = ['folder' => 0, 'file' => $file];
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('#^File type not allowed#');
        $this->client->request('POST', $this->router->generate('admin_media_upload'), content: json_encode($data));
    }

    /**
     * Test méthode upload() : rejette un fichier dépassant la limite de taille serveur
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testUploadRejectsOversizedFile(): void
    {
        $this->mediaService->resetAllMedia();

        $user = $this->createUserContributeur();
        $this->client->loginUser($user, 'admin');
        $this->client->catchExceptions(false);

        $oversized = str_repeat('A', MediaConst::MAX_UPLOAD_SIZE_BYTES + 1);
        $file = [
            'name' => 'too-big',
            'description' => 'unit test upload',
            'fileExtention' => 'jpg',
            'url' => 'data:image/jpeg;base64,' . base64_encode($oversized),
        ];

        $data = ['folder' => 0, 'file' => $file];
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('File size exceeds the allowed limit.');
        $this->client->request('POST', $this->router->generate('admin_media_upload'), content: json_encode($data));
    }

    /**
     * Test méthode upload() : un upload avec l'extension "jpeg" génère bien une miniature
     * (régression du bug Thumbnail::getGdImage() qui ne gérait pas "jpeg", seulement "jpg")
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testUploadJpegGeneratesThumbnail(): void
    {
        $this->mediaService->resetAllMedia();

        $user = $this->createUserContributeur();
        $this->client->loginUser($user, 'admin');

        $data = ['folder' => 0, 'file' => $this->buildUploadFile('road.jpg', 'image/jpeg', 'jpeg', 'photo')];
        $this->client->request('POST', $this->router->generate('admin_media_upload'), content: json_encode($data));
        $this->assertResponseIsSuccessful();

        $medias = $this->mediaService->getMediaByMediaFolder(null);
        $this->assertCount(1, $medias);
        $this->assertEquals(MediaConst::MEDIA_TYPE_IMG, $medias[0]->getType());
        $this->assertNotNull($medias[0]->getThumbnail());
        $this->assertTrue(
            $this->fileSystem->exists(
                $this->mediaService->getRootPathThumbnail() . DIRECTORY_SEPARATOR . $medias[0]->getThumbnail(),
            ),
        );

        // getThumbnail() ne doit pas lever de TypeError.
        $thumbnailUrl = $this->mediaService->getThumbnail($medias[0]);
        $this->assertStringContainsString($medias[0]->getThumbnail(), $thumbnailUrl);
    }
}
