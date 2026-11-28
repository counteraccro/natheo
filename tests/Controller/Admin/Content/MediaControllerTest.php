<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Test du contenu de la médiathèque lu par les écrans Twig
 */

namespace App\Tests\Controller\Admin\Content;

use App\Dto\Media\MediaStorageConfig;
use App\Tests\AppWebTestCase;

class MediaControllerTest extends AppWebTestCase
{
    protected function tearDown(): void
    {
        $this->clearMediaStorage();
        parent::tearDown();
    }

    /**
     * Sous-dossiers puis médias hors corbeille, URL calculées depuis la clé du fichier
     * @return void
     */
    public function testLoadMedias(): void
    {
        $this->checkNoAccess('admin_media_load_medias');
        $user = $this->createUserContributeur();
        $this->client->loginUser($user, 'admin');

        $folder = $this->createMediaFolder();
        $child = $this->createMediaFolder($folder, customData: ['name' => 'sous-dossier']);
        $this->createMediaFolder($folder, customData: ['trashedAt' => new \DateTime()]);
        $media = $this->createMedia($folder, $user);
        $this->createMedia($folder, $user, ['trashedAt' => new \DateTime()]);
        $pdf = $this->createMedia($folder, $user, content: "%PDF-1.4\n", fileName: 'notice.pdf');

        $this->client->request(
            'GET',
            $this->router->generate('admin_media_load_medias', [
                'folder' => $folder->getId(),
                'order' => 'asc',
                'filter' => 'created_at',
            ]),
        );
        $this->assertResponseIsSuccessful();
        $items = json_decode($this->client->getResponse()->getContent(), true)['medias'];

        $this->assertSame(['folder', 'media', 'media'], array_column($items, 'type'));
        $this->assertSame($child->getId(), $items[0]['id']);
        $this->assertSame($media->getId(), $items[1]['id']);
        $this->assertSame('http://dev.natheo/assets/natheotheque-test/' . $media->getPath(), $items[1]['webPath']);
        $this->assertSame(
            'http://dev.natheo/assets/natheotheque-test/' . MediaStorageConfig::thumbnailKey($media->getPath()),
            $items[1]['thumbnail'],
        );
        $this->assertSame($media->getMediaTranslations()->first()->getTitle(), $items[1]['title']);
        $this->assertSame($pdf->getId(), $items[2]['id']);
        $this->assertSame('/assets/natheo/mediatheque/file_pdf.svg', $items[2]['thumbnail']);
    }

    /**
     * Le contenu d'un dossier à la corbeille n'est pas lu
     * @return void
     */
    public function testLoadMediasTrashedFolder(): void
    {
        $user = $this->createUserContributeur();
        $this->client->loginUser($user, 'admin');
        $folder = $this->createMediaFolder(customData: ['trashedAt' => new \DateTime()]);
        $this->createMedia($folder, $user);

        $this->client->request(
            'GET',
            $this->router->generate('admin_media_load_medias', ['folder' => $folder->getId()]),
        );
        $this->assertResponseIsSuccessful();
        $this->assertSame([], json_decode($this->client->getResponse()->getContent(), true)['medias']);
    }
}
