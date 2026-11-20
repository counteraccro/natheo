<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Lecture et contrôle des fichiers de surcharge des opérations d'API
 */

namespace App\Tests\DependencyInjection\Compiler;

use App\DependencyInjection\Compiler\OverwriteConfigPass;
use App\Tests\Api\V2\State\Overwrite\OverwriteProbeProcessor;
use App\Tests\Api\V2\State\Overwrite\OverwriteProbeProvider;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class OverwriteConfigPassTest extends TestCase
{
    /**
     * Entrées de plusieurs fichiers rangées par nom d'opération
     * @return void
     */
    public function testParse(): void
    {
        $this->assertSame(
            [
                'a_get' => ['provider' => OverwriteProbeProvider::class],
                'b_patch' => ['processor' => OverwriteProbeProcessor::class],
            ],
            OverwriteConfigPass::parse([
                'un.yaml' => [['operation' => 'a_get', 'provider' => OverwriteProbeProvider::class]],
                'deux.yaml' => [['operation' => 'b_patch', 'processor' => OverwriteProbeProcessor::class]],
                'vide.yaml' => [],
            ]),
        );
    }

    /**
     * @param array $entries
     * @param string $message
     * @return void
     */
    #[DataProvider('invalidProvider')]
    public function testInvalid(array $entries, string $message): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage($message);
        OverwriteConfigPass::parse(['overwrite.yaml' => $entries]);
    }

    /**
     * @return array<string, array{0: array, 1: string}>
     */
    public static function invalidProvider(): array
    {
        $provider = OverwriteProbeProvider::class;
        return [
            'pas une liste' => [['a_get' => ['provider' => $provider]], 'doit être une liste'],
            'sans opération' => [[['provider' => $provider]], 'sans « operation »'],
            'en double' => [
                [['operation' => 'a_get', 'provider' => $provider], ['operation' => 'a_get', 'provider' => $provider]],
                'surchargée deux fois',
            ],
            'ni provider ni processor' => [[['operation' => 'a_get']], 'soit « provider », soit « processor »'],
            'les deux' => [
                [['operation' => 'a_get', 'provider' => $provider, 'processor' => OverwriteProbeProcessor::class]],
                'soit « provider », soit « processor »',
            ],
            'clé inconnue' => [
                [['operation' => 'a_get', 'provider' => $provider, 'route' => 'x']],
                'soit « provider », soit « processor »',
            ],
            'classe absente' => [[['operation' => 'a_get', 'provider' => 'App\Inconnue']], 'App\Inconnue'],
            'mauvaise interface' => [
                [['operation' => 'a_get', 'processor' => $provider]],
                'doit implémenter ApiPlatform\State\ProcessorInterface',
            ],
        ];
    }
}
