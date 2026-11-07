<?php

declare(strict_types=1);
/**
 * Contrôle que les traductions sont complètes dans toutes les langues supportées
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Tests\Translation;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

class TranslationCompletenessTest extends TestCase
{
    private const LOCALES = ['fr', 'en', 'es'];

    /**
     * @return array<string, array{string}>
     */
    public static function domainProvider(): array
    {
        $domains = [];
        foreach (glob(self::getDirectory() . '/*.fr.yaml') as $file) {
            $domain = basename($file, '.fr.yaml');
            $domains[$domain] = [$domain];
        }
        return $domains;
    }

    /**
     * Aucune valeur ne garde le marqueur `__` laissé par translation:extract
     * @param string $domain
     * @return void
     */
    #[DataProvider('domainProvider')]
    public function testNoUntranslatedValue(string $domain): void
    {
        foreach (self::LOCALES as $locale) {
            $untranslated = array_keys(
                array_filter(
                    $this->getValues($domain, $locale),
                    fn(string $value) => str_starts_with($value, '__') || str_contains($value, '{__'),
                ),
            );

            $this->assertSame([], $untranslated, 'Valeurs non traduites en ' . $locale . ' pour ' . $domain);
        }
    }

    /**
     * Une valeur utilise les mêmes paramètres ICU dans toutes les langues
     * @param string $domain
     * @return void
     */
    #[DataProvider('domainProvider')]
    public function testSameParametersInAllLocales(string $domain): void
    {
        $reference = $this->getValues($domain, 'fr');

        foreach (array_diff(self::LOCALES, ['fr']) as $locale) {
            $mismatches = [];
            foreach ($this->getValues($domain, $locale) as $key => $value) {
                if (isset($reference[$key]) && $this->getParameters($value) !== $this->getParameters($reference[$key])) {
                    $mismatches[] = $key;
                }
            }

            $this->assertSame([], $mismatches, 'Paramètres différents entre fr et ' . $locale . ' pour ' . $domain);
        }
    }

    /**
     * Retourne les noms des paramètres d'un message, sans le texte des branches plural / select
     * @param string $value
     * @return string[]
     */
    private function getParameters(string $value): array
    {
        $value = preg_replace('/(=\d+|zero|one|two|few|many|other)\s*\{[^{}]*\}/', '', $value);
        preg_match_all('/\{\{?\s*([A-Za-z_]\w*)\s*[,}]/', $value, $matches);

        $parameters = array_unique($matches[1]);
        sort($parameters);
        return $parameters;
    }

    /**
     * Retourne les valeurs d'un fichier de traduction indexées par clé
     * @param string $domain
     * @param string $locale
     * @return array<string, string>
     */
    private function getValues(string $domain, string $locale): array
    {
        $file = self::getDirectory() . '/' . $domain . '.' . $locale . '.yaml';
        $this->assertFileExists($file);

        $values = [];
        foreach (Yaml::parseFile($file) ?? [] as $key => $value) {
            $values[(string) $key] = (string) $value;
        }
        return $values;
    }

    /**
     * Dossier des traductions
     * @return string
     */
    private static function getDirectory(): string
    {
        return dirname(__DIR__, 2) . '/translations';
    }
}
