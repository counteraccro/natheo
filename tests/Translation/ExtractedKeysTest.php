<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Vérifie que les clés trouvées par translation:extract sont exactement celles des fichiers fr, pour les domaines
 * dont chaque clé est écrite en entier dans le code
 */

namespace App\Tests\Translation;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Translation\Extractor\ExtractorInterface;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\MessageCatalogueInterface;
use Symfony\Component\Yaml\Yaml;

class ExtractedKeysTest extends KernelTestCase
{
    /**
     * Domaines vérifiés
     * @var string[]
     */
    private const array DOMAINS = ['admin_ui', 'admin_module', 'admin_app', 'demo_overwrite'];

    private static ?MessageCatalogue $extracted = null;

    /**
     * @return array<string, array{string}>
     */
    public static function domainProvider(): array
    {
        return array_combine(self::DOMAINS, array_map(fn(string $domain): array => [$domain], self::DOMAINS));
    }

    /**
     * @param string $domain
     * @return void
     */
    #[DataProvider('domainProvider')]
    public function testExtractedKeysMatchFrenchFile(string $domain): void
    {
        $projectDir = self::getContainer()->getParameter('kernel.project_dir');
        $file = $projectDir . '/translations/' . $domain . MessageCatalogueInterface::INTL_DOMAIN_SUFFIX . '.fr.yaml';
        $expected = array_keys(Yaml::parseFile($file));
        $extracted = array_keys($this->getExtracted()->all($domain));
        sort($expected);
        sort($extracted);

        $this->assertSame([], array_values(array_diff($extracted, $expected)), 'Clés du code absentes du fichier fr');
        $this->assertSame([], array_values(array_diff($expected, $extracted)), 'Clés du fichier fr absentes du code');
    }

    /**
     * Catalogue rempli par les extracteurs de la commande, sur les mêmes dossiers qu'elle
     * @return MessageCatalogue
     */
    private function getExtracted(): MessageCatalogue
    {
        if (self::$extracted === null) {
            $container = self::getContainer();
            $projectDir = $container->getParameter('kernel.project_dir');
            /** @var ExtractorInterface $extractor */
            $extractor = $container->get('translation.extractor');
            $catalogue = new MessageCatalogue('fr');
            foreach (['/src', '/templates'] as $directory) {
                $extractor->extract($projectDir . $directory, $catalogue);
            }
            self::$extracted = $catalogue;
        }
        return self::$extracted;
    }
}
