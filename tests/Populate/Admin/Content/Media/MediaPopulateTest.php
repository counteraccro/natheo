<?php

declare(strict_types=1);
/**
 * Test de MediaPopulate
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Tests\Populate\Admin\Content\Media;

use App\Dto\Media\StoredMediaFile;
use App\Entity\Admin\Content\Media\Media;
use App\Enum\Admin\Content\Media\MediaType;
use App\Populate\Admin\Content\Media\MediaPopulate;
use PHPUnit\Framework\TestCase;

class MediaPopulateTest extends TestCase
{
    /**
     * Le média reçoit la clé, le type déduit du MIME, l'extension, les dimensions et l'empreinte
     * @return void
     */
    public function testFromStoredFile(): void
    {
        $file = new StoredMediaFile(
            '2026/11/film-abcd1234.mp4',
            'video/mp4',
            2048,
            str_repeat('a', 64),
            null,
            null,
            true,
        );
        $media = MediaPopulate::fromStoredFile(new Media(), $file, 'Mon film.MP4');

        $this->assertSame('Mon film.MP4', $media->getName());
        $this->assertSame('2026/11/film-abcd1234.mp4', $media->getPath());
        $this->assertSame(MediaType::VIDEO, $media->getType());
        $this->assertSame('mp4', $media->getExtension());
        $this->assertSame(2048, $media->getSize());
        $this->assertNull($media->getWidth());
        $this->assertSame(str_repeat('a', 64), $media->getHash());
        $this->assertTrue($media->hasThumbnail());
    }

    /**
     * Le type vient du MIME réel
     * @return void
     */
    public function testMediaTypeFromMimeType(): void
    {
        $this->assertSame(MediaType::IMG, MediaType::fromMimeType('image/webp'));
        $this->assertSame(MediaType::VIDEO, MediaType::fromMimeType('video/webm'));
        $this->assertSame(MediaType::FILE, MediaType::fromMimeType('application/pdf'));
    }
}
