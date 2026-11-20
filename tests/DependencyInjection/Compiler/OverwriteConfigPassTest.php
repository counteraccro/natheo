<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Lecture et contrôle des fichiers de surcharge des opérations d'API
 */

namespace App\Tests\DependencyInjection\Compiler;

use App\DependencyInjection\Compiler\OverwriteConfigPass;
use App\Tests\Api\V2\State\Overwrite\OverwriteProbeProcessor;
use App\Tests\Api\V2\State\Overwrite\OverwriteProbeProvider;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

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
     * Clé overwrite.api lue, ancien format des redirections refusé
     * @return void
     */
    public function testEntriesOf(): void
    {
        $this->assertSame([], OverwriteConfigPass::entriesOf(null, 'vide.yaml'));
        $this->assertSame([], OverwriteConfigPass::entriesOf(['overwrite' => ['api' => []]], 'vide.yaml'));
        $entries = [['operation' => 'a_get', 'provider' => OverwriteProbeProvider::class]];
        $this->assertSame($entries, OverwriteConfigPass::entriesOf(['overwrite' => ['api' => $entries]], 'un.yaml'));

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('ancien.yaml');
        OverwriteConfigPass::entriesOf(
            ['overwrite' => [['controller' => 'App\Controller\Admin\DashboardController', 'route' => 'x']]],
            'ancien.yaml',
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

    /**
     * La classe de remplacement doit être un service portant le tag de son rôle
     * @return void
     */
    public function testCheckService(): void
    {
        $container = new ContainerBuilder();
        $container->register(OverwriteProbeProvider::class)->addTag(OverwriteConfigPass::PROVIDER_TAG);
        $container->register(OverwriteProbeProcessor::class);

        OverwriteConfigPass::checkService($container, 'a_get', [
            OverwriteConfigPass::PROVIDER => OverwriteProbeProvider::class,
        ]);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('n\'a pas le tag ' . OverwriteConfigPass::PROCESSOR_TAG);
        OverwriteConfigPass::checkService($container, 'a_patch', [
            OverwriteConfigPass::PROCESSOR => OverwriteProbeProcessor::class,
        ]);
    }

    /**
     * Une classe qui n'est pas un service est refusée
     * @return void
     */
    public function testCheckServiceMissing(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('n\'est pas un service');
        OverwriteConfigPass::checkService(new ContainerBuilder(), 'a_get', [
            OverwriteConfigPass::PROVIDER => OverwriteProbeProvider::class,
        ]);
    }
}
