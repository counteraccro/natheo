<?php

declare(strict_types=1);
/**
 * Test de MediaStorage
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Tests\Service\Global\Media;

use App\Dto\Media\MediaStorageConfig;
use App\Service\Global\Media\MediaFileException;
use App\Service\Global\Media\MediaImageProcessor;
use App\Service\Global\Media\MediaMetadataStripper;
use App\Service\Global\Media\MediaStorage;
use App\Service\Global\Media\MediaStorageConfigResolver;
use App\Tests\Helper\Media\TestImage;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class MediaStorageTest extends TestCase
{
    private string $root;

    private MediaStorage $storage;

    /**
     * @var string[] fichiers temporaires à effacer
     */
    private array $files = [];

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir() . '/natheo-media-' . bin2hex(random_bytes(4));
        $resolver = $this->createStub(MediaStorageConfigResolver::class);
        $resolver
            ->method('resolve')
            ->willReturn(
                new MediaStorageConfig($this->root . '/public', $this->root . '/trash', '/assets/natheotheque'),
            );
        $this->storage = new MediaStorage($resolver, new MediaImageProcessor(new MediaMetadataStripper()));
    }

    protected function tearDown(): void
    {
        (new Filesystem())->remove([$this->root, ...$this->files]);
    }

    /**
     * Une photo est rangée sous le mois de l'envoi, redressée, sans position GPS, avec sa miniature
     * @return void
     */
    public function testStoreImage(): void
    {
        $bytes = TestImage::jpeg(40, 20, 6);
        $source = $this->file($bytes);
        $stored = $this->storage->store($source, 'Été à la plage.JPG', new \DateTimeImmutable('2026-11-05'));

        $this->assertMatchesRegularExpression('#^2026/11/ete-a-la-plage-[a-z0-9]{8}\.jpg$#', $stored->key);
        $this->assertSame('image/jpeg', $stored->mimeType);
        $this->assertSame(hash('sha256', $bytes), $stored->hash);
        $this->assertSame([20, 40], [$stored->width, $stored->height]);
        $this->assertTrue($stored->hasThumbnail);
        $this->assertSame($bytes, file_get_contents($source));

        $file = $this->root . '/public/' . $stored->key;
        $this->assertFileExists($file);
        $this->assertSame($stored->size, filesize($file));
        $this->assertStringNotContainsString(TestImage::GPS_MARKER, (string) file_get_contents($file));
        $this->assertFileExists($this->root . '/public/' . MediaStorageConfig::thumbnailKey($stored->key));
    }

    /**
     * Un document est rangé tel quel, sans miniature ni dimensions ; un nom vide donne une clé « media »
     * @return void
     */
    public function testStoreDocument(): void
    {
        $bytes = "%PDF-1.4\n%Document de test\n";
        $stored = $this->storage->store($this->file($bytes), '.pdf', new \DateTimeImmutable('2026-01-31'));

        $this->assertMatchesRegularExpression('#^2026/01/media-[a-z0-9]{8}\.pdf$#', $stored->key);
        $this->assertNull($stored->width);
        $this->assertFalse($stored->hasThumbnail);
        $this->assertSame($bytes, file_get_contents($this->root . '/public/' . $stored->key));
    }

    /**
     * Deux envois du même nom reçoivent deux clés
     * @return void
     */
    public function testStoreSameName(): void
    {
        $first = $this->storage->store($this->file(TestImage::png()), 'logo.png');
        $second = $this->storage->store($this->file(TestImage::png()), 'logo.png');
        $this->assertNotSame($first->key, $second->key);
    }

    /**
     * Un fichier absent est refusé
     * @return void
     */
    public function testStoreUnreadable(): void
    {
        $this->expectException(MediaFileException::class);
        $this->storage->store($this->root . '/absent.jpg', 'absent.jpg');
    }

    /**
     * Corbeille puis restauration : fichier et miniature quittent le stockage servi puis y reviennent
     * @return void
     */
    public function testTrashAndRestore(): void
    {
        $key = $this->storage->store($this->file(TestImage::png()), 'logo.png')->key;

        $this->storage->trash($key);
        $this->assertFalse($this->storage->exists($key));
        $this->assertFalse($this->storage->hasThumbnail($key));
        $this->assertTrue($this->storage->isTrashed($key));
        $this->assertFileExists($this->root . '/trash/' . MediaStorageConfig::thumbnailKey($key));

        $this->storage->restore($key);
        $this->assertTrue($this->storage->exists($key));
        $this->assertTrue($this->storage->hasThumbnail($key));
        $this->assertFalse($this->storage->isTrashed($key));
    }

    /**
     * La suppression efface fichier et miniature, à la corbeille ou non
     * @return void
     */
    public function testDelete(): void
    {
        $served = $this->storage->store($this->file(TestImage::png()), 'servi.png')->key;
        $trashed = $this->storage->store($this->file(TestImage::png()), 'jete.png')->key;
        $this->storage->trash($trashed);

        $this->storage->delete($served);
        $this->storage->delete($trashed);
        $this->storage->delete('2026/11/inconnu.png');

        foreach ([$served, $trashed] as $key) {
            $this->assertFalse($this->storage->exists($key));
            $this->assertFalse($this->storage->isTrashed($key));
            $this->assertFileDoesNotExist($this->root . '/public/' . MediaStorageConfig::thumbnailKey($key));
            $this->assertFileDoesNotExist($this->root . '/trash/' . MediaStorageConfig::thumbnailKey($key));
        }
    }

    /**
     * La couverture d'une vidéo est rangée en WebP à la place de la miniature
     * @return void
     */
    public function testStoreCover(): void
    {
        $key = $this->storage->store($this->file("\x00\x00\x00\x18ftypmp42"), 'film.mp4')->key;
        $this->assertFalse($this->storage->hasThumbnail($key));

        $this->storage->storeCover($key, $this->file(TestImage::jpeg(orientation: null)));
        $thumbnail = $this->root . '/public/' . MediaStorageConfig::thumbnailKey($key);
        $this->assertSame('image/webp', getimagesize($thumbnail)['mime']);
    }

    /**
     * Fichier temporaire effacé à la fin du test
     * @param string $bytes
     * @return string
     */
    private function file(string $bytes): string
    {
        return $this->files[] = TestImage::file($bytes);
    }
}
