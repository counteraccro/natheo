<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Fidélité de Natheo-Editor : le Markdown réécrit par l'éditeur donne le même HTML que le texte d'origine
 */

namespace App\Tests\Service\Global;

use App\Service\Global\MarkdownService;
use League\CommonMark\Exception\CommonMarkException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class NatheoEditorFidelityTest extends TestCase
{
    /**
     * Paires écrites par le test Vitest fidelity.spec.ts
     */
    private const string PAIRS = __DIR__ . '/../../js/fixtures/natheo-editor/pairs.json';

    /**
     * Paires avant / après, indexées par contenu
     * @return array<string, array{string, string}>
     */
    public static function pairs(): array
    {
        $pairs = json_decode((string) file_get_contents(self::PAIRS), true, flags: JSON_THROW_ON_ERROR);
        $cases = [];
        foreach ($pairs as $pair) {
            $cases[$pair['name']] = [$pair['before'], $pair['after']];
        }
        return $cases;
    }

    /**
     * Le rendu du site et celui des commentaires (mode strict) ne changent pas
     * @param string $before
     * @param string $after
     * @return void
     * @throws CommonMarkException
     */
    #[DataProvider('pairs')]
    public function testSameHtml(string $before, string $after): void
    {
        $markdown = new MarkdownService();
        foreach ([false, true] as $strict) {
            $this->assertSame(
                $this->normalize($markdown->convertMarkdownToHtml($before, $strict)),
                $this->normalize($markdown->convertMarkdownToHtml($after, $strict))
            );
        }
    }

    /**
     * HTML sans les blancs que le navigateur ne montre pas, hors des blocs de code
     * @param string $html
     * @return string
     */
    private function normalize(string $html): string
    {
        $parts = preg_split('/(<pre\b.*?<\/pre>)/s', $html, flags: PREG_SPLIT_DELIM_CAPTURE);
        foreach ($parts as $index => $part) {
            if ($index % 2 === 0) {
                $parts[$index] = preg_replace(['/\s+/u', '/\s*(<\/?[a-z][^>]*>)\s*/'], [' ', '$1'], $part);
            }
        }
        return trim(implode('', $parts));
    }
}
