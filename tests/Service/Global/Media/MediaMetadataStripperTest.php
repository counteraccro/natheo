<?php

declare(strict_types=1);
/**
 * Test de MediaMetadataStripper
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Tests\Service\Global\Media;

use App\Service\Global\Media\MediaMetadataStripper;
use App\Tests\Helper\Media\TestImage;
use PHPUnit\Framework\TestCase;

class MediaMetadataStripperTest extends TestCase
{
    private MediaMetadataStripper $stripper;

    protected function setUp(): void
    {
        $this->stripper = new MediaMetadataStripper();
    }

    /**
     * L'orientation est lue dans le bloc EXIF, normale sans EXIF
     * @return void
     */
    public function testJpegOrientation(): void
    {
        $this->assertSame(6, $this->stripper->jpegOrientation(TestImage::jpeg(orientation: 6)));
        $this->assertSame(1, $this->stripper->jpegOrientation(TestImage::jpeg(orientation: null)));
        $this->assertSame(1, $this->stripper->jpegOrientation('pas une image'));
    }

    /**
     * Un JPEG perd son EXIF (position GPS) et son commentaire, l'image reste lisible
     * @return void
     */
    public function testStripJpeg(): void
    {
        $jpeg = TestImage::jpeg();
        $this->assertStringContainsString(TestImage::GPS_MARKER, $jpeg);

        $stripped = $this->stripper->strip($jpeg, 'image/jpeg');
        $this->assertNotNull($stripped);
        $this->assertStringNotContainsString(TestImage::GPS_MARKER, $stripped);
        $this->assertStringNotContainsString("Exif\0\0", $stripped);
        $size = getimagesizefromstring($stripped);
        $this->assertSame([40, 20], [$size[0], $size[1]]);
    }

    /**
     * Un WebP perd ses blocs EXIF et XMP et les indicateurs de son en-tête étendu
     * @return void
     */
    public function testStripWebp(): void
    {
        $webp = TestImage::webp();
        $this->assertStringContainsString(TestImage::GPS_MARKER, $webp);

        $stripped = $this->stripper->strip($webp, 'image/webp');
        $this->assertNotNull($stripped);
        $this->assertStringNotContainsString(TestImage::GPS_MARKER, $stripped);
        $this->assertStringNotContainsString('EXIF', $stripped);
        $this->assertSame(strlen($stripped) - 8, unpack('V', substr($stripped, 4, 4))[1]);
        $this->assertSame(0, ord($stripped[20]) & 0x0c);
        $this->assertInstanceOf(\GdImage::class, imagecreatefromstring($stripped));
    }

    /**
     * Un fichier à la structure illisible est signalé, un autre format est gardé tel quel
     * @return void
     */
    public function testStripUnreadable(): void
    {
        $this->assertNull($this->stripper->strip("\xff\xd8\xff\xe1\x00", 'image/jpeg'));
        $this->assertNull($this->stripper->strip('RIFF....WEBPVP8 ', 'image/webp'));
        $png = TestImage::png();
        $this->assertSame($png, $this->stripper->strip($png, 'image/png'));
    }
}
