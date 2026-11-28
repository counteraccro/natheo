<?php

declare(strict_types=1);
/**
 * Test de MediaImageProcessor
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Tests\Service\Global\Media;

use App\Service\Global\Media\MediaFileException;
use App\Service\Global\Media\MediaImageProcessor;
use App\Service\Global\Media\MediaMetadataStripper;
use App\Tests\Helper\Media\TestImage;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class MediaImageProcessorTest extends TestCase
{
    private MediaImageProcessor $processor;

    /**
     * @var string[] fichiers temporaires à effacer
     */
    private array $files = [];

    protected function setUp(): void
    {
        $this->processor = new MediaImageProcessor(new MediaMetadataStripper());
    }

    protected function tearDown(): void
    {
        array_map(fn(string $file) => @unlink($file), $this->files);
    }

    /**
     * Une photo tournée est redressée : dimensions et position de la moitié rouge
     * @param int $orientation
     * @param array{int, int} $dimensions
     * @param array{int, int} $redPixel
     * @return void
     */
    #[DataProvider('orientationProvider')]
    public function testNormalizeOrientation(int $orientation, array $dimensions, array $redPixel): void
    {
        $path = $this->file(TestImage::jpeg(40, 20, $orientation));
        $this->processor->normalize($path, 'image/jpeg');

        $this->assertSame($dimensions, $this->processor->dimensions($path));
        $this->assertSame('red', TestImage::colorAt($path, ...$redPixel));
        $this->assertStringNotContainsString(TestImage::GPS_MARKER, (string) file_get_contents($path));
    }

    /**
     * @return array<string, array{int, array{int, int}, array{int, int}}>
     */
    public static function orientationProvider(): array
    {
        return [
            'normale' => [1, [40, 20], [5, 10]],
            'miroir horizontal' => [2, [40, 20], [35, 10]],
            '180°' => [3, [40, 20], [35, 10]],
            'miroir vertical' => [4, [40, 20], [5, 10]],
            'transposée' => [5, [20, 40], [10, 5]],
            '90° horaire' => [6, [20, 40], [10, 5]],
            'transverse' => [7, [20, 40], [10, 35]],
            '90° antihoraire' => [8, [20, 40], [10, 35]],
        ];
    }

    /**
     * Un WebP perd sa position GPS sans être réencodé
     * @return void
     */
    public function testNormalizeWebp(): void
    {
        $path = $this->file(TestImage::webp());
        $this->processor->normalize($path, 'image/webp');

        $this->assertStringNotContainsString(TestImage::GPS_MARKER, (string) file_get_contents($path));
        $this->assertSame([40, 20], $this->processor->dimensions($path));
    }

    /**
     * La miniature est un WebP qui tient dans 480 px, une petite image n'est pas agrandie
     * @return void
     */
    public function testThumbnail(): void
    {
        $large = $this->processor->thumbnail($this->file(TestImage::jpeg(1200, 600, null)), 'image/jpeg');
        $size = getimagesizefromstring((string) $large);
        $this->assertSame('image/webp', $size['mime']);
        $this->assertSame([480, 240], [$size[0], $size[1]]);

        $small = $this->processor->thumbnail($this->file(TestImage::png()), 'image/png');
        $size = getimagesizefromstring((string) $small);
        $this->assertSame([40, 20], [$size[0], $size[1]]);

        $this->assertNull($this->processor->thumbnail($this->file('%PDF-1.4'), 'application/pdf'));
    }

    /**
     * La couverture est réencodée en WebP et réduite à 1280 px
     * @return void
     */
    public function testCover(): void
    {
        $cover = $this->processor->cover($this->file(TestImage::jpeg(2000, 1000, 1)));
        $size = getimagesizefromstring($cover);
        $this->assertSame('image/webp', $size['mime']);
        $this->assertSame([1280, 640], [$size[0], $size[1]]);
        $this->assertStringNotContainsString(TestImage::GPS_MARKER, $cover);
    }

    /**
     * Une couverture qui n'est pas une image ou qui est trop grande est refusée
     * @return void
     */
    public function testCoverRefused(): void
    {
        $cases = [
            MediaFileException::COVER_INVALID => $this->file('<svg xmlns="http://www.w3.org/2000/svg"></svg>'),
            MediaFileException::COVER_TOO_LARGE => $this->file(TestImage::jpeg(4100, 10, null)),
        ];
        foreach ($cases as $reason => $path) {
            try {
                $this->processor->cover($path);
                $this->fail('Couverture acceptée : ' . $reason);
            } catch (MediaFileException $e) {
                $this->assertSame($reason, $e->reason);
            }
        }
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
