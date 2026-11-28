<?php

declare(strict_types=1);
/**
 * Test de MediaStorageConfig
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Tests\Dto\Media;

use App\Dto\Media\MediaStorageConfig;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class MediaStorageConfigTest extends TestCase
{
    /**
     * L'URL n'a jamais de double slash, avec ou sans slash final dans les options
     * @param string $baseUrl
     * @param string $webPath
     * @param string $expected
     * @return void
     */
    #[DataProvider('urlProvider')]
    public function testUrl(string $baseUrl, string $webPath, string $expected): void
    {
        $config = new MediaStorageConfig('/root', '/trash', $webPath, $baseUrl);
        $this->assertSame($expected, $config->url('2026/11/photo-abcd1234.jpg'));
    }

    /**
     * @return array<string, array{string, string, string}>
     */
    public static function urlProvider(): array
    {
        $file = '/assets/natheotheque/2026/11/photo-abcd1234.jpg';
        return [
            'sans hôte' => ['', '/assets/natheotheque', $file],
            'hôte' => ['http://dev.natheo:8888', '/assets/natheotheque', 'http://dev.natheo:8888' . $file],
            'hôte avec slash final' => ['https://site.fr/', '/assets/natheotheque/', 'https://site.fr' . $file],
            'url externe avec chemin' => [
                'https://cdn.site.fr/medias//',
                'assets/natheotheque',
                'https://cdn.site.fr/medias' . $file,
            ],
            'préfixe sans hôte' => ['sous-dossier/', '/assets/natheotheque', '/sous-dossier' . $file],
            'slash seul' => ['/', '/assets/natheotheque', $file],
        ];
    }

    /**
     * La miniature garde la clé du fichier avec l'extension webp
     * @return void
     */
    public function testThumbnailKey(): void
    {
        $this->assertSame(
            'thumbnails/2026/11/photo-abcd1234.webp',
            MediaStorageConfig::thumbnailKey('2026/11/photo-abcd1234.jpg'),
        );
        $this->assertSame(
            'thumbnails/2026/11/notes-abcd1234.webp',
            MediaStorageConfig::thumbnailKey('2026/11/notes-abcd1234'),
        );
        $this->assertSame('thumbnails/2026/v1.0/notes.webp', MediaStorageConfig::thumbnailKey('/2026/v1.0/notes'));
    }

    /**
     * Les segments vides, « . » et « .. » sont retirés
     * @return void
     */
    public function testSegments(): void
    {
        $this->assertSame(['natheotheque', 'medias'], MediaStorageConfig::segments('/../natheotheque/./\\medias//'));
        $this->assertSame([], MediaStorageConfig::segments(''));
    }
}
