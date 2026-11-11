<?php

declare(strict_types=1);
/**
 * Test de AvatarUrl
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Tests\Service\Global\User;

use App\Service\Global\User\AvatarUrl;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AvatarUrlTest extends TestCase
{
    /**
     * Le dossier commence et se termine par un seul slash, préfixé du base path
     * @param string $avatarPath
     * @param string $basePath
     * @param string $expected
     * @return void
     */
    #[DataProvider('directoryProvider')]
    public function testDirectory(string $avatarPath, string $basePath, string $expected): void
    {
        $this->assertSame($expected, AvatarUrl::directory($avatarPath, $basePath));
    }

    /**
     * @return array<string, array{string, string, string}>
     */
    public static function directoryProvider(): array
    {
        return [
            'sans slash' => ['uploads/avatars', '', '/uploads/avatars/'],
            'slashes en trop' => ['/uploads/avatars/', '', '/uploads/avatars/'],
            'avec base path' => ['uploads/avatars', '/natheo', '/natheo/uploads/avatars/'],
        ];
    }

    /**
     * Le chemin d'un avatar est le dossier suivi du nom du fichier
     * @return void
     */
    public function testFile(): void
    {
        $this->assertSame('/uploads/avatars/avatar.png', AvatarUrl::file('avatar.png', 'uploads/avatars'));
        $this->assertSame(
            '/natheo/uploads/avatars/avatar.png',
            AvatarUrl::file('avatar.png', '/uploads/avatars/', '/natheo'),
        );
    }

    /**
     * Sans avatar, null ou chaîne vide, aucun chemin n'est retourné
     * @return void
     */
    public function testFileWithoutAvatar(): void
    {
        $this->assertNull(AvatarUrl::file(null, 'uploads/avatars'));
        $this->assertNull(AvatarUrl::file('', 'uploads/avatars', '/natheo'));
    }
}
