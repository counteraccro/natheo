<?php

declare(strict_types=1);
/**
 * Contrôle de la convention de nommage des clés de traduction
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Tests\Translation;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

class TranslationKeyConventionTest extends TestCase
{
    /**
     * Domaines contrôlés par le test
     */
    private const DOMAINS = [
        'advanced_options',
        'api_errors',
        'api_token',
        'command',
        'comment',
        'dashboard',
        'database_manager',
        'demo_overwrite',
        'dev',
        'editor_markdown',
        'faq',
        'front',
        'front_error',
        'global',
        'global_search',
        'grid',
        'info',
        'installation',
        'log',
        'mail',
        'media',
        'menu',
        'messages',
        'notification',
        'option_system',
        'page',
        'sidebar',
        'sql_manager',
        'tag',
        'translate',
        'user',
    ];

    /**
     * Préfixes d'origine des clés du domaine messages, traduites sans domaine
     */
    private const MESSAGES_PREFIXES = [
        'date',
        'global',
        'mail',
        'options_system',
        'options_user',
        'pagedemo',
        'response',
        'sidebar',
    ];

    private const LOCALES = ['fr', 'en', 'es'];

    /**
     * @return array<string, array{string}>
     */
    public static function domainProvider(): array
    {
        $domains = [];
        foreach (self::DOMAINS as $domain) {
            $domains[$domain] = [$domain];
        }
        return $domains;
    }

    /**
     * Chaque clé suit le format <domaine>.<écran>.<élément> : minuscules, points entre les niveaux,
     * tirets bas à l'intérieur d'un nom ; le domaine messages remplace <domaine> par un préfixe d'origine
     * @param string $domain
     * @return void
     */
    #[DataProvider('domainProvider')]
    public function testKeyFormat(string $domain): void
    {
        $prefixes = $domain === 'messages' ? self::MESSAGES_PREFIXES : [$domain];
        $prefixes = implode('|', array_map(fn(string $prefix) => preg_quote($prefix, '/'), $prefixes));
        $pattern = '/^(' . $prefixes . ')(\.[a-z0-9]+(_[a-z0-9]+)*){2,}$/';
        $invalid = array_filter($this->getKeys($domain, 'fr'), fn(string $key) => !preg_match($pattern, $key));

        $this->assertSame([], array_values($invalid), 'Clés hors convention dans le domaine ' . $domain);
    }

    /**
     * Une clé n'est jamais le préfixe d'une autre (ex. `grid.search` et `grid.search.title`)
     * @param string $domain
     * @return void
     */
    #[DataProvider('domainProvider')]
    public function testNoKeyIsPrefixOfAnother(string $domain): void
    {
        $keys = array_flip($this->getKeys($domain, 'fr'));

        $conflicts = [];
        foreach (array_keys($keys) as $key) {
            $parts = explode('.', $key);
            for ($i = 1; $i < count($parts); $i++) {
                $prefix = implode('.', array_slice($parts, 0, $i));
                if (isset($keys[$prefix])) {
                    $conflicts[] = $prefix . ' / ' . $key;
                }
            }
        }

        $this->assertSame([], $conflicts, 'Clés à la fois feuille et préfixe dans le domaine ' . $domain);
    }

    /**
     * Les fichiers de toutes les langues contiennent exactement les mêmes clés
     * @param string $domain
     * @return void
     */
    #[DataProvider('domainProvider')]
    public function testSameKeysInAllLocales(string $domain): void
    {
        $reference = $this->getKeys($domain, 'fr');
        sort($reference);

        foreach (array_diff(self::LOCALES, ['fr']) as $locale) {
            $keys = $this->getKeys($domain, $locale);
            sort($keys);
            $this->assertSame($reference, $keys, 'Clés différentes entre fr et ' . $locale . ' pour ' . $domain);
        }
    }

    /**
     * Retourne les clés d'un fichier de traduction
     * @param string $domain
     * @param string $locale
     * @return string[]
     */
    private function getKeys(string $domain, string $locale): array
    {
        $file = dirname(__DIR__, 2) . '/translations/' . $domain . '+intl-icu.' . $locale . '.yaml';
        $this->assertFileExists($file);

        return array_map('strval', array_keys(Yaml::parseFile($file) ?? []));
    }
}
